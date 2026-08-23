<?php

namespace App\Http\Controllers;

use App\Models\ArchitectDetail;
use App\Models\ArchitectDetailCompany;
use App\Models\ArchitectSite;
use App\Models\BCategory;
use App\Models\BMaterial;
use App\Models\Category;
use App\Models\CompanyArchitectSite;
use App\Models\ConstructionDetail;
use App\Models\Expense;
use App\Models\Labour;
use App\Models\LabourInstalment;
use App\Models\LabourCategory;
use App\Models\Material;
use App\Models\MiscAdmin;
use App\Models\PaymentReceived;
use App\Models\Setting;
use App\Models\Site;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Port of application/controllers/Admin_setting.php — the back-office.
 *
 * Every route in this controller is behind the `admin` middleware. The legacy
 * constructor (Admin_setting.php:14-16) rendered the login view without an
 * exit(), so all 80 endpoints executed for anonymous callers; /get_users
 * returned md5 hashes and the plaintext copies in `for_admin` to anyone.
 *
 * NAMING WARNING — the legacy A/B category names are swapped, consistently:
 *   get_Agategory / delete_category   operate on `b_category` (finishing)
 *   get_Bgategory / delete_Bcategory  operate on `category`   (civil)
 * The views pair them correctly, so the swap is preserved rather than fixed.
 *
 * Likewise `architect_detail_company` hangs off `sites`, not off the
 * (permanently empty) `Company_architect_site` table its name suggests.
 */
class AdminSettingController extends Controller
{
    // ---------------------------------------------------------------- pages

    public function addUser()
    {
        // The legacy view queried `sites` itself (admin/add_user.php:154).
        return view('admin.add_user', ['sites' => Site::all()]);
    }

    public function showUser()
    {
        return view('admin.show_user');
    }

    /**
     * Admin home.
     *
     * The per-site figures are the same arithmetic showDetails() does for one
     * site, run across all of them at once: cost is civil + finishing + labour
     * + miscellaneous, less returns; the balance is what a client has paid
     * against that. Keeping the definition identical matters — a dashboard that
     * totals differently from the page it links to is worse than no dashboard.
     *
     * The portfolio-wide totals, the cost breakdown and the approval queue were
     * dropped with the cards that showed them, and the whole-table sums and
     * pending counts that fed them went too. What is left is the monthly series
     * and the per-site table.
     *
     * Money columns are VARCHAR in the legacy schema (real rows hold '20% of
     * profit' alongside plain amounts), so every sum casts. Non-numeric text
     * casts to 0 rather than breaking the query, which is the behaviour the
     * per-site screens already rely on.
     *
     * Only status 1 counts for the three approval-gated tables, matching
     * showDetails(); 0 is awaiting an admin and 2 was rejected.
     */
    public function dashboard()
    {
        // table => [amount column, project fk, status-gated?]
        $costs = [
            'material' => ['price', 'project_id', true],
            'b_material' => ['price', 'project_id', true],
            'labour_instalment' => ['instalmet', 'project_id', true],
            'misc' => ['price', 'proj_id', false],
        ];

        // Per-site cost, so the table can rank sites and show each balance.
        $perSite = [];
        foreach ($costs as $table => [$col, $fk, $gated]) {
            foreach ($this->sumByProject($table, $col, $fk, $gated) as $id => $amount) {
                $perSite[$id] = ($perSite[$id] ?? 0) + $amount;
            }
        }
        foreach ($this->sumByProject('return_payment', 'price', 'proj_id', false) as $id => $amount) {
            $perSite[$id] = ($perSite[$id] ?? 0) - $amount;
        }

        $receivedBySite = $this->sumByProject('payments_recieved', 'payment', 'proj_id', false);

        $sites = Site::all()->map(function ($site) use ($perSite, $receivedBySite) {
            $cost = (float) ($perSite[$site->id] ?? 0);
            $paid = (float) ($receivedBySite[$site->id] ?? 0);

            return [
                'id' => $site->id,
                'name' => $site->display_name,
                'closed' => $site->site_status === Site::STATUS_CLOSED,
                'cost' => $cost,
                'received' => $paid,
                'balance' => $paid - $cost,
            ];
        })->sortByDesc('cost')->values();

        // Closed sites are settled and only pad the table, so the dashboard
        // shows the work in progress. Their balances are excluded from the
        // shortfall below for the same reason: money owed on a site nobody is
        // building any more is a different question from what is owed now.
        $running = $sites->reject(fn ($site) => $site['closed'])->values();

        // Only the sites in deficit. Netting the positives off would hide the
        // shortfall behind sites that happen to be paid ahead.
        $behind = $running->filter(fn ($site) => $site['balance'] < 0);

        return view('admin.dashboard', [
            'sites' => $running,
            'shortfall' => (float) $behind->sum('balance'),
            'behindCount' => $behind->count(),
            'months' => $this->monthlySeries(),
        ]);
    }

    /** The same sum, keyed by the project it belongs to. */
    private function sumByProject(string $table, string $column, string $fk, bool $gated)
    {
        $q = DB::table($table)->select($fk, DB::raw("sum(cast({$column} as decimal(18,2))) total"));

        if ($gated) {
            $q->where('status', 1);
        }

        return $q->groupBy($fk)->pluck('total', $fk)->map(fn ($v) => (float) $v)->all();
    }

    /**
     * Cost and receipts per calendar month, last 12 including this one.
     *
     * Reads date_n, the normalised DATE column added beside every legacy
     * VARCHAR `date` — the original holds free text and cannot be grouped on.
     * Months with no activity are still emitted, so the axis has no gaps.
     */
    private function monthlySeries(): array
    {
        $buckets = [];
        $cursor = now()->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $buckets[$cursor->format('Y-m')] = [
                'label' => $cursor->format('M'),
                'year' => $cursor->format('Y'),
                'cost' => 0.0,
                'received' => 0.0,
            ];
            $cursor = $cursor->addMonth();
        }

        $from = array_key_first($buckets).'-01';

        $add = function (string $table, string $column, bool $gated, string $key) use (&$buckets, $from) {
            $q = DB::table($table)
                ->selectRaw("date_format(date_n, '%Y-%m') ym, sum(cast({$column} as decimal(18,2))) total")
                ->whereNotNull('date_n')
                ->where('date_n', '>=', $from);

            if ($gated) {
                $q->where('status', 1);
            }

            foreach ($q->groupBy('ym')->get() as $row) {
                if (isset($buckets[$row->ym])) {
                    $buckets[$row->ym][$key] += (float) $row->total;
                }
            }
        };

        $add('material', 'price', true, 'cost');
        $add('b_material', 'price', true, 'cost');
        $add('labour_instalment', 'instalmet', true, 'cost');
        $add('misc', 'price', false, 'cost');
        $add('payments_recieved', 'payment', false, 'received');

        return array_values($buckets);
    }

    public function addCivil()
    {
        return view('admin.add_civil');
    }

    public function addFinishing()
    {
        return view('admin.add_finishing');
    }

    public function addLabour()
    {
        // admin/add_labour.php:151 queried `sites` itself.
        return view('admin.add_labour', [
            'sites' => Site::all(),
            'labourTypes' => $this->labourTypeList(),
        ]);
    }

    public function addSite()
    {
        return view('admin.add_architect_site');
    }

    public function addConSite()
    {
        return view('admin.add_con_site');
    }

    public function showSite()
    {
        return view('admin.show_architect_site');
    }

    public function showConSite()
    {
        return view('admin.show_con_site');
    }

    public function setRole()
    {
        // admin/setting.php:161-168 ran these two queries inside the view.
        return view('admin.setting', ['setting' => Setting::current()]);
    }

    public function addExpense()
    {
        return view('admin.expense');
    }

    public function civilRequests()
    {
        return view('admin.civil_requets');
    }

    public function finishRequests()
    {
        return view('admin.finish_requets');
    }

    // ------------------------------------------------------------- profile

    public function profile()
    {
        return view('admin.profile', ['admin' => Auth::guard('admin')->user()]);
    }

    /** Admins publish directly. */
    public function saveProfilePhoto(Request $request, AvatarService $avatars)
    {
        $request->validate(['photo' => AvatarService::RULES]);

        $admin = Auth::guard('admin')->user();
        $avatars->forget($admin->avatar);

        $admin->forceFill(['avatar' => $avatars->put($request->file('photo'))])->save();

        return response()->json([
            'success' => true,
            'message' => 'Your profile picture has been updated.',
        ]);
    }

    /** The review queue for workers' profile pictures. */
    public function photoRequests()
    {
        return view('admin.photo_requets', [
            'pending' => User::awaitingAvatar()->orderBy('name')->get(),
        ]);
    }

    public function acceptPhoto(Request $request, AvatarService $avatars)
    {
        $user = User::find($request->input('userId'));

        return $this->ok($user ? $avatars->approve($user) : false);
    }

    public function rejectPhoto(Request $request, AvatarService $avatars)
    {
        $user = User::find($request->input('userId'));

        return $this->ok($user ? $avatars->reject($user) : false);
    }

    /** The approval queue for payments a worker has recorded. */
    public function paymentRequests()
    {
        return view('admin.payment_requets');
    }

    /**
     * Pending payments, newest first, with the site each belongs to resolved
     * so the table does not have to make a second call per row.
     */
    public function getPaymentRequests()
    {
        $sites = Site::all()->keyBy('id');

        $rows = PaymentReceived::where('status', PaymentReceived::PENDING)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'site' => $sites[$p->proj_id]->display_name ?? ('Site #'.$p->proj_id),
                'payment' => $p->payment,
                'source' => $p->source,
                'date' => $p->date,
            ]);

        return response()->json(['data' => $rows]);
    }

    public function acceptPayment(Request $request)
    {
        return $this->decidePayment($request->input('userId'), PaymentReceived::LIVE);
    }

    public function rejectPayment(Request $request)
    {
        return $this->decidePayment($request->input('userId'), PaymentReceived::REJECTED);
    }

    /**
     * Not setStatus(): deciding a payment also clears decision_seen, which is
     * what puts the outcome in the worker's notification bell. The two have to
     * move together or a decision is made silently.
     */
    private function decidePayment($id, int $status)
    {
        $payment = PaymentReceived::find($id);

        return $this->ok($payment ? $payment->update([
            'status' => $status,
            'decision_seen' => 0,
        ]) : false);
    }

    /** Admin_setting.php:864-922 — the company money dashboard. */
    public function showExpense()
    {
        $construction = $this->sum(ConstructionDetail::pluck('payment'));
        $architect = $this->sum(ArchitectDetailCompany::pluck('payment'));
        $miscTotal = $this->sum(MiscAdmin::pluck('amount'));

        $expenseTotal = $this->sum(Expense::where('type', 'expense')->pluck('ammount'));
        $received = $construction + $architect + $miscTotal;

        return view('admin.show_expense', [
            'expense_total' => $expenseTotal,
            'hussnain_total' => $this->sum(Expense::where('type', 'hussnain')->pluck('ammount')),
            'basharat_total' => $this->sum(Expense::where('type', 'basharat')->pluck('ammount')),
            'misc_total' => $miscTotal,
            'total_price' => $received,
            'total_remaining' => $received - $expenseTotal,
        ]);
    }

    /** Admin_setting.php:585-628 — architect fee ledger for an architect_site. */
    public function showDetails(string $id)
    {
        $site = ArchitectSite::findOrFail($id);
        $paid = $this->sum(ArchitectDetail::where('proj_id', $id)->pluck('payment'));
        $fee = (float) $site->total_price;

        return view('admin.architect_site', [
            'const_id' => $id,
            'name' => $site->display_name,
            'total_fee' => $fee,
            'total_instalments' => $paid,
            'remaing_instalment' => $fee - $paid,
        ]);
    }

    /** Admin_setting.php:631-674 — architect fee ledger for a `sites` project. */
    public function showDetailsCompany(string $id)
    {
        $site = Site::findOrFail($id);
        $paid = $this->sum(ArchitectDetailCompany::where('proj_id', $id)->pluck('payment'));
        $fee = (float) $site->architect_fees;

        return view('admin.architect_detail_company', [
            'const_id' => $id,
            'name' => $site->display_name,
            'total_fee' => $fee,
            'total_instalments' => $paid,
            'remaing_instalment' => $fee - $paid,
        ]);
    }

    /** Admin_setting.php:677-736 — construction instalments + payments received. */
    public function showConDetails(string $id)
    {
        $site = Site::findOrFail($id);

        return view('admin.construction_site', [
            'const_id' => $id,
            'name' => $site->display_name,
            'total_fee' => $site->total_price,
            'total_instalments' => $this->sum(ConstructionDetail::where('proj_id', $id)->pluck('payment')),
            'total_payments' => $this->sum(PaymentReceived::where('proj_id', $id)->pluck('payment')),
        ]);
    }

    // ----------------------------------------------------------- list reads

    public function getUsers()
    {
        // `password` and `for_admin` are hidden by the User model, so the
        // hashes and plaintext copies the old endpoint leaked are not sent.
        return response()->json(['data' => User::all()]);
    }

    /** Pending civil requests — status 0. */
    public function getCivil()
    {
        return response()->json(['data' => Material::where('status', 0)->get()]);
    }

    /** Pending finishing requests — status 0. */
    public function getFinish()
    {
        return response()->json(['data' => BMaterial::where('status', 0)->get()]);
    }

    /** Swapped by legacy naming: returns FINISHING categories. */
    public function getAGategory()
    {
        return response()->json(['data' => BCategory::all()]);
    }

    /** Swapped by legacy naming: returns CIVIL categories. */
    public function getBGategory()
    {
        return response()->json(['data' => Category::all()]);
    }

    public function getLabourCat()
    {
        return response()->json(['data' => Labour::all()]);
    }

    public function getSite()
    {
        return response()->json(['data' => ArchitectSite::all()]);
    }

    /** Legacy typo "comapny" preserved in the URL. */
    public function getSiteCompany()
    {
        return response()->json(['data' => Site::all()]);
    }

    public function getConSite()
    {
        return response()->json(['data' => Site::all()]);
    }

    public function getExpense()
    {
        return response()->json(['data' => Expense::all()]);
    }

    public function getMiscAdmin()
    {
        return response()->json(['data' => MiscAdmin::all()]);
    }

    public function showBCategory(string $id)
    {
        return response()->json(['data' => ArchitectDetail::where('proj_id', $id)->get()]);
    }

    public function showCompanyArchitect(string $id)
    {
        return response()->json(['data' => ArchitectDetailCompany::where('proj_id', $id)->get()]);
    }

    public function showConDetail(string $id)
    {
        return response()->json(['data' => ConstructionDetail::where('proj_id', $id)->get()]);
    }

    public function showConDetail1(string $id)
    {
        // Newest first, on the sortable date_n rather than the legacy VARCHAR.
        return response()->json([
            'data' => PaymentReceived::where('proj_id', $id)
                ->orderByDesc('date_n')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    // ------------------------------------------------------- modal prefills

    public function getUserDetails(Request $request)
    {
        return $this->found(User::find($request->query('userId')));
    }

    public function getSiteDetails(Request $request)
    {
        return $this->found(ArchitectSite::find($request->query('userId')));
    }

    public function getSiteDetailsCompany(Request $request)
    {
        return $this->found(Site::find($request->query('userId')));
    }

    public function getSiteDetails12(Request $request)
    {
        return $this->found(Site::find($request->query('userId')));
    }

    public function getPaymentEdit(Request $request)
    {
        return $this->found(PaymentReceived::find($request->query('userId')));
    }

    public function getPaymentArchi(Request $request)
    {
        return $this->found(ArchitectDetail::find($request->query('userId')));
    }

    public function getPaymentCompany(Request $request)
    {
        return $this->found(ArchitectDetailCompany::find($request->query('userId')));
    }

    public function getExpenseData(Request $request)
    {
        return $this->found(Expense::find($request->query('userId')));
    }

    public function getMiscData(Request $request)
    {
        return $this->found(MiscAdmin::find($request->query('userId')));
    }

    // -------------------------------------------------------------- creates

    /**
     * Admin_setting.php:26-66.
     *
     * The hash is upgraded from md5 to bcrypt, but the plaintext copy in
     * `for_admin` is still written: the Show Users page displays it in its
     * Password column, and leaving it unwritten would make that column show
     * stale or blank values for every account created from here on.
     */
    public function saveUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200'],
            'password' => ['required', 'string', 'min:4'],
            'address' => ['nullable', 'string', 'max:200'],
            'contact' => ['nullable', 'string', 'max:200'],
            'user_role' => ['nullable', 'string', 'max:200'],
            'proj_id' => ['nullable'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'address' => $data['address'] ?? '',
            'password' => Hash::make($data['password']),
            // plaintext copy shown in the Show Users "Password" column
            'for_admin' => $data['password'],
            'contact' => $data['contact'] ?? '',
        ];

        // Legacy behaviour: role/project are only stored when a project was
        // picked (Admin_setting.php:30).
        if (($data['proj_id'] ?? '') !== '') {
            $attributes['role'] = $data['user_role'] ?? '';
            $attributes['project_id'] = $data['proj_id'];
        }

        return $this->ok(User::create($attributes)->exists);
    }

    /** Swapped by legacy naming: save_Amaterial writes a CIVIL category. */
    public function saveAMaterial(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:200']]);

        return $this->ok(Category::create(['material_name' => $data['name']])->exists);
    }

    /** Swapped by legacy naming: save_Bmaterial writes a FINISHING category. */
    public function saveBMaterial(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:200']]);

        return $this->ok(BCategory::create(['material_name' => $data['name']])->exists);
    }

    public function saveLabour(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'price' => ['nullable'],
            'project_id' => ['required'],
        ]);

        return $this->ok(Labour::create([
            'type' => $data['name'],
            'total' => $data['price'] ?? 0,
            'project_id' => $data['project_id'],
        ])->exists);
    }

    /** Admin_setting.php:312-333 — the single settings row. */
    public function saveSetting(Request $request)
    {
        $data = $request->validate([
            'civil' => ['required', 'in:0,1'],
            'finish' => ['required', 'in:0,1'],
            // Optional so an older form post, which knows nothing about
            // payments, leaves the payment setting as it found it.
            'payment' => ['nullable', 'in:0,1'],
        ]);

        $setting = Setting::current();

        $fields = [
            'civil_status' => $data['civil'],
            'finish_status' => $data['finish'],
        ];

        if (isset($data['payment'])) {
            $fields['payment_status'] = $data['payment'];
        }

        return $this->ok($setting->update($fields));
    }

    public function saveSite(Request $request)
    {
        $data = $request->validate([
            'phase' => ['required', 'string', 'max:200'],
            'project_name' => ['required', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            'price' => ['nullable'],
        ]);

        return $this->ok(ArchitectSite::create([
            'phase' => $data['phase'],
            'name' => $data['project_name'],
            'sector' => $data['sector'] ?? '',
            'total_price' => $data['price'] ?? 0,
        ])->exists);
    }

    /**
     * Admin_setting.php:423-445. Writes to `Company_architect_site`, which is
     * empty in production and read by nothing — the "company architect" screens
     * all read `sites` instead. Kept so behaviour is identical.
     */
    public function saveCompanySite(Request $request)
    {
        $data = $request->validate([
            'phase1' => ['required', 'string', 'max:200'],
            'project_name1' => ['required', 'string', 'max:200'],
            'sector1' => ['nullable', 'string', 'max:200'],
            'price1' => ['nullable'],
        ]);

        return $this->ok(CompanyArchitectSite::create([
            'phase1' => $data['phase1'],
            'name1' => $data['project_name1'],
            'sector1' => $data['sector1'] ?? '',
            'total_price1' => $data['price1'] ?? 0,
        ])->exists);
    }

    public function saveConSite(Request $request)
    {
        $data = $request->validate([
            'phase' => ['required', 'string', 'max:200'],
            'project_name' => ['required', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            // Free text: real rows hold '20% of profit', '12%', '8%'.
            'price' => ['nullable', 'string', 'max:200'],
            'architect_fee' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(Site::create([
            'phase' => $data['phase'],
            'project_name' => $data['project_name'],
            'sector' => $data['sector'] ?? '',
            'total_price' => $data['price'] ?? '',
            'architect_fees' => $data['architect_fee'] ?? '',
        ])->exists);
    }

    public function saveExpense(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:200'],
            'amount' => ['nullable'],
            'detail' => ['nullable', 'string', 'max:200'],
            'selected_date' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(Expense::create([
            'type' => $data['type'],
            'ammount' => $data['amount'] ?? 0,
            'detail' => $data['detail'] ?? '',
            'date' => $data['selected_date'] ?? '',
        ])->exists);
    }

    public function saveMiscAdmin(Request $request)
    {
        $data = $request->validate([
            'detail' => ['nullable', 'string', 'max:200'],
            'Amount' => ['nullable'],
            'Selected_date' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(MiscAdmin::create([
            'detail' => $data['detail'] ?? '',
            'amount' => $data['Amount'] ?? 0,
            'date' => $data['Selected_date'] ?? '',
        ])->exists);
    }

    /** Architect fee instalment against an `architect_site`. */
    public function addBrick(Request $request)
    {
        return $this->ok(ArchitectDetail::create($this->paymentPayload($request))->exists);
    }

    /** Architect fee instalment against a `sites` project. */
    public function addCompanyArchitect(Request $request)
    {
        return $this->ok(ArchitectDetailCompany::create($this->paymentPayload($request))->exists);
    }

    public function addConsInstal(Request $request)
    {
        return $this->ok(ConstructionDetail::create($this->paymentPayload($request))->exists);
    }

    /** The second form on the same page uses suffixed field names. */
    public function addConsPayment(Request $request)
    {
        $data = $request->validate([
            'price1' => ['nullable'],
            'source1' => ['nullable', 'string', 'max:200'],
            'selected_date1' => ['nullable', 'string', 'max:200'],
            'proj_id1' => ['required'],
        ]);

        return $this->ok(PaymentReceived::create([
            'payment' => $data['price1'] ?? 0,
            'source' => $data['source1'] ?? '',
            'date' => $data['selected_date1'] ?? '',
            'proj_id' => $data['proj_id1'],
        ])->exists);
    }

    protected function paymentPayload(Request $request): array
    {
        $data = $request->validate([
            'price' => ['nullable'],
            'source' => ['nullable', 'string', 'max:200'],
            'selected_date' => ['nullable', 'string', 'max:200'],
            'proj_id' => ['required'],
        ]);

        return [
            'payment' => $data['price'] ?? 0,
            'source' => $data['source'] ?? '',
            'date' => $data['selected_date'] ?? '',
            'proj_id' => $data['proj_id'],
        ];
    }

    // ------------------------------------------------------------ approvals

    public function acceptCivil(Request $request)
    {
        return $this->setStatus(Material::class, $request->input('userId'), 1);
    }

    public function rejectCivil(Request $request)
    {
        return $this->setStatus(Material::class, $request->input('userId'), 2);
    }

    public function acceptFinish(Request $request)
    {
        return $this->setStatus(BMaterial::class, $request->input('userId'), 1);
    }

    public function rejectFinish(Request $request)
    {
        return $this->setStatus(BMaterial::class, $request->input('userId'), 2);
    }

    protected function setStatus(string $model, $id, int $status)
    {
        $row = $model::find($id);

        return $this->ok($row ? $row->update(['status' => $status]) : false);
    }

    // -------------------------------------------------------------- updates

    public function updateUser(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'name' => ['nullable', 'string', 'max:200'],
            'username' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:200'],
            'address' => ['nullable', 'string', 'max:200'],
            'contact' => ['nullable', 'string', 'max:200'],
            'role' => ['nullable', 'string', 'max:200'],
            'password' => ['nullable', 'string', 'min:4'],
        ]);

        $user = User::findOrFail($data['id']);

        $attributes = collect($data)->only('name', 'username', 'email', 'address', 'contact', 'role')->all();

        // Legacy only touched the password when a new one was typed
        // (Admin_setting.php:205). Same here, but stored as bcrypt — and the
        // plaintext copy is refreshed alongside it so the Show Users Password
        // column keeps matching the real password.
        if (! empty($data['password'])) {
            $attributes['password'] = Hash::make($data['password']);
            $attributes['for_admin'] = $data['password'];
        }

        return $this->ok($user->update($attributes));
    }

    public function updateSite(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'phase' => ['nullable', 'string', 'max:200'],
            'name' => ['nullable', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            'total_price' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(ArchitectSite::findOrFail($data['id'])
            ->update($request->only('phase', 'name', 'sector', 'total_price')));
    }

    /** Updates the architect fee on a `sites` row. */
    public function updateSiteCompany(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'phase' => ['nullable', 'string', 'max:200'],
            'project_name' => ['nullable', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            'architect_fees' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(Site::findOrFail($data['id'])
            ->update($request->only('phase', 'project_name', 'sector', 'architect_fees')));
    }

    public function updateSite12(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'phase' => ['nullable', 'string', 'max:200'],
            'project_name' => ['nullable', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            'total_price' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(Site::findOrFail($data['id'])
            ->update($request->only('phase', 'project_name', 'sector', 'total_price')));
    }

    public function updatePayment(Request $request)
    {
        return $this->updatePaymentRow(PaymentReceived::class, $request);
    }

    public function updatePaymentArchi(Request $request)
    {
        return $this->updatePaymentRow(ArchitectDetail::class, $request);
    }

    public function updatePaymentCompany(Request $request)
    {
        return $this->updatePaymentRow(ArchitectDetailCompany::class, $request);
    }

    protected function updatePaymentRow(string $model, Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'payment' => ['nullable'],
            'source' => ['nullable', 'string', 'max:200'],
            'date' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok($model::findOrFail($data['id'])
            ->update($request->only('payment', 'source', 'date')));
    }

    public function updateExpense(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'type' => ['nullable', 'string', 'max:200'],
            'ammount' => ['nullable'],
            'date' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(Expense::findOrFail($data['id'])
            ->update($request->only('type', 'ammount', 'date')));
    }

    /** The misc modal uses differently-cased field names: id1 / Amount / Selected_date. */
    public function updateMisc(Request $request)
    {
        $data = $request->validate([
            'id1' => ['required'],
            'detail' => ['nullable', 'string', 'max:200'],
            'Amount' => ['nullable'],
            'Selected_date' => ['nullable', 'string', 'max:200'],
        ]);

        return $this->ok(MiscAdmin::findOrFail($data['id1'])->update([
            'detail' => $data['detail'] ?? '',
            'amount' => $data['Amount'] ?? 0,
            'date' => $data['Selected_date'] ?? '',
        ]));
    }

    // -------------------------------------------------------------- deletes

    public function deleteUser(Request $request)
    {
        return $this->ok((bool) User::destroy($request->input('userId')));
    }

    /** Swapped by legacy naming: delete_category removes a FINISHING category. */
    /**
     * The labour type catalogue behind the picker on the Add Category page.
     *
     * Union of the catalogue and the names actually in use: the legacy
     * CodeIgniter app writes to `labour` directly and knows nothing about
     * `labour_category`, so a type it introduces would otherwise be missing
     * from the list while sites were recording instalments against it.
     */
    private function labourTypeList()
    {
        return LabourCategory::pluck('type')
            ->merge(Labour::whereNotNull('type')->where('type', '!=', '')->distinct()->pluck('type'))
            ->map(fn ($type) => trim((string) $type))
            ->filter()
            ->unique(fn ($type) => mb_strtolower($type))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /** Adds a name to the catalogue. Idempotent, and case-insensitively so. */
    public function saveLabourCategory(Request $request)
    {
        $name = trim((string) $request->validate([
            'name' => ['required', 'string', 'max:200'],
        ])['name']);

        $existing = LabourCategory::get()->first(
            fn ($row) => mb_strtolower(trim((string) $row->type)) === mb_strtolower($name)
        );

        if (! $existing) {
            LabourCategory::create(['type' => $name]);
        }

        return response()->json([
            'success' => true,
            'name' => $existing ? trim((string) $existing->type) : $name,
            'existed' => (bool) $existing,
            'types' => $this->labourTypeList(),
        ]);
    }

    /**
     * Removes a name from the catalogue.
     *
     * Refused while any site still has that type assigned. Deleting the name
     * would not touch those rows — nothing is keyed to this table — so the
     * type would simply reappear in the list, sourced from the sites using it,
     * and the delete would look broken rather than blocked.
     */
    public function deleteLabourCategory(Request $request)
    {
        $name = trim((string) $request->validate([
            'name' => ['required', 'string', 'max:200'],
        ])['name']);

        $sites = Labour::get()->filter(
            fn ($row) => mb_strtolower(trim((string) $row->type)) === mb_strtolower($name)
        );

        if ($sites->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '"'.$name.'" is assigned to '.$sites->count().' '
                    .\Illuminate\Support\Str::plural('site', $sites->count())
                    .' and cannot be removed.',
            ]);
        }

        LabourCategory::get()
            ->filter(fn ($row) => mb_strtolower(trim((string) $row->type)) === mb_strtolower($name))
            ->each(fn ($row) => $row->delete());

        return response()->json(['success' => true, 'types' => $this->labourTypeList()]);
    }

    public function deleteCategory(Request $request)
    {
        return $this->ok((bool) BCategory::destroy($request->input('userId')));
    }

    /** Swapped by legacy naming: delete_Bcategory removes a CIVIL category. */
    public function deleteBCategory(Request $request)
    {
        return $this->ok((bool) Category::destroy($request->input('userId')));
    }

    public function deleteArchitectSite(Request $request)
    {
        return $this->ok((bool) ArchitectSite::destroy($request->input('userId')));
    }

    public function deleteCompanySite(Request $request)
    {
        return $this->ok((bool) CompanyArchitectSite::destroy($request->input('userId')));
    }

    public function deleteConSite(Request $request)
    {
        return $this->ok((bool) Site::destroy($request->input('userId')));
    }

    public function deletePayment(Request $request)
    {
        return $this->ok((bool) PaymentReceived::destroy($request->input('userId')));
    }

    public function deletePaymentArchi(Request $request)
    {
        return $this->ok((bool) ArchitectDetail::destroy($request->input('userId')));
    }

    public function deletePaymentCompany(Request $request)
    {
        return $this->ok((bool) ArchitectDetailCompany::destroy($request->input('userId')));
    }

    public function deleteExpense(Request $request)
    {
        return $this->ok((bool) Expense::destroy($request->input('userId')));
    }

    public function deleteMisc(Request $request)
    {
        return $this->ok((bool) MiscAdmin::destroy($request->input('userId')));
    }

    // -------------------------------------------------------------- helpers

    protected function found($model)
    {
        return $model
            ? response()->json(['success' => true, 'data' => $model])
            : response()->json(['success' => false]);
    }

    protected function ok(bool $result)
    {
        return response()->json(['success' => $result]);
    }

    protected function sum(iterable $values): float|int
    {
        $total = 0;

        foreach ($values as $value) {
            $total += (float) ($value ?: 0);
        }

        return $total == (int) $total ? (int) $total : $total;
    }
}
