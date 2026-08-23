<?php

namespace App\Http\Controllers;

use App\Models\BCategory;
use App\Models\BMaterial;
use App\Models\Category;
use App\Models\Labour;
use App\Models\LabourInstalment;
use App\Models\LabourCategory;
use App\Models\Material;
use App\Models\Misc;
use App\Models\PaymentReceived;
use App\Models\ReturnPayment;
use App\Models\Setting;
use App\Models\Site;
use App\Services\AvatarService;
use App\Services\RollupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Port of application/controllers/Construction.php — the worker-facing module.
 *
 * Status semantics on material / b_material / labour_instalment:
 *   0 = pending admin approval, 1 = approved/live, 2 = rejected.
 *
 * The legacy status filtering is INCONSISTENT between the row listing and the
 * heading total on the same screen, and that inconsistency is preserved here
 * so the numbers match the old app exactly:
 *   - show_bricks  — rows filtered to status 1, heading total NOT filtered
 *     (Construction.php:82 has no status clause, Sites::get_bricks does)
 *   - show_labour  — same asymmetry (Construction.php:400)
 *   - show_b_category, show_civil_total, show_finish_total, show_labour_total
 *     — filtered on both sides
 * Each is flagged inline below. Changing them would silently move totals the
 * client already sees, so any correction belongs in a separate, deliberate step.
 */
class ConstructionController extends Controller
{
    public function __construct(protected RollupService $rollups) {}

    // ---------------------------------------------------------------- pages

    public function addSite()
    {
        return view('construction.add_site');
    }

    public function showSite()
    {
        return view('construction.show_site');
    }

    /** Construction.php:436-538 — the per-project ledger screen. */
    public function showDetails(string $id)
    {
        $site = Site::findOrFail($id);

        $civil = $this->sum(Material::where('project_id', $id)->where('status', 1)->pluck('price'));
        $finish = $this->sum(BMaterial::where('project_id', $id)->where('status', 1)->pluck('price'));
        $misc = $this->sum(Misc::where('proj_id', $id)->pluck('price'));
        $labour = $this->sum(LabourInstalment::where('project_id', $id)->where('status', 1)->pluck('instalmet'));
        $returned = $this->sum(ReturnPayment::where('proj_id', $id)->pluck('price'));
        $received = $this->sum(PaymentReceived::where('proj_id', $id)->pluck('payment'));

        $grandTotal = ($labour + $misc + $finish + $civil) - $returned;

        return view('construction.site_settings', [
            'const_id' => $id,
            'name' => $site->display_name,
            'civil_price' => $civil,
            'finish_price' => $finish,
            'misc_price' => $misc,
            'labour_price' => $labour,
            'return_total' => $returned,
            'total_payments' => $received,
            'payment_recieved' => $received,
            'Grand_total' => $grandTotal,
            'Remainung_Balace' => $received - $grandTotal,
            // Dropdowns the legacy view queried for itself (site_settings.php
            // lines 134, 194, 267, 333, 406, 466).
            'civilCategories' => Category::orderBy('material_name')->get(),
            'finishCategories' => BCategory::orderBy('material_name')->get(),
            'labourTypes' => Labour::where('project_id', $id)->get(),
        ]);
    }

    // ------------------------------------------------------------ payments

    /*
     * Client payments, recorded by a worker against a site.
     *
     * Previously admin-only (admin_setting/show_con_details). A worker can now
     * add, list and correct a payment, but NOT delete one — a payment that was
     * banked and then vanishes from the ledger is the one edit nobody should
     * be able to make without an admin. There is deliberately no delete route
     * here, not merely a hidden button.
     *
     * Whether a new payment counts immediately or waits for an admin is the
     * `setting.payment_status` switch, the same mechanism civil and finishing
     * materials use.
     */

    /** The site picker: running and closed, with each site's payment total. */
    public function payments()
    {
        $totals = PaymentReceived::query()
            ->selectRaw('proj_id, sum(cast(payment as decimal(18,2))) total')
            ->where('status', PaymentReceived::LIVE)
            ->groupBy('proj_id')
            ->pluck('total', 'proj_id');

        return view('construction.payments', [
            'sites' => Site::all()->map(fn ($site) => [
                'id' => $site->id,
                'name' => $site->display_name,
                'closed' => $site->site_status === Site::STATUS_CLOSED,
                'total' => (float) ($totals[$site->id] ?? 0),
            ])->values(),
        ]);
    }

    /** One site's payment ledger. */
    public function paymentDetails(string $id)
    {
        $site = Site::findOrFail($id);

        return view('construction.payment_details', [
            'const_id' => $id,
            'name' => $site->display_name,
            'total_payments' => $this->sum(
                PaymentReceived::where('proj_id', $id)->where('status', PaymentReceived::LIVE)->pluck('payment')
            ),
            'pending_payments' => $this->sum(
                PaymentReceived::where('proj_id', $id)->where('status', PaymentReceived::PENDING)->pluck('payment')
            ),
            'needsApproval' => Setting::current()->paymentsNeedApproval(),
        ]);
    }

    /**
     * Rows for one site. Rejected payments are dropped; pending ones are kept
     * so the worker can see their own entry is filed and waiting rather than
     * assuming it failed and entering it twice.
     */
    public function getPayments(string $id)
    {
        return response()->json([
            'data' => PaymentReceived::where('proj_id', $id)
                ->whereIn('status', [PaymentReceived::LIVE, PaymentReceived::PENDING])
                // Newest first. On date_n, the sortable DATE copy — the legacy
                // `date` is a VARCHAR and would sort as text. id breaks ties
                // and carries rows whose date never parsed.
                ->orderByDesc('date_n')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function savePayment(Request $request)
    {
        $data = $request->validate([
            'price1' => ['required'],
            'source1' => ['nullable', 'string', 'max:200'],
            'selected_date1' => ['nullable', 'string', 'max:200'],
            'proj_id1' => ['required'],
        ]);

        $date = $data['selected_date1'] ?? '';

        return $this->ok(PaymentReceived::create([
            'payment' => $data['price1'],
            'source' => $data['source1'] ?? '',
            'date' => $date,
            'date_n' => $this->normaliseDate($date),
            'proj_id' => $data['proj_id1'],
            'status' => Setting::current()->paymentEntryStatus(),
        ])->exists);
    }

    /**
     * Acknowledge every decision currently in the worker's bell.
     *
     * Fired when the notification menu is opened, so a decision is announced
     * once and then stops nagging. Idempotent, so a double-open is harmless.
     */
    public function markPaymentsSeen()
    {
        PaymentReceived::whereIn('status', [PaymentReceived::LIVE, PaymentReceived::REJECTED])
            ->where('decision_seen', 0)
            ->update(['decision_seen' => 1]);

        return $this->ok(true);
    }

    public function getPaymentDetails(Request $request)
    {
        return $this->found(PaymentReceived::find($request->query('userId')));
    }

    /**
     * Correcting a payment re-files it for approval when the setting demands
     * it — otherwise an approved row could be edited to any figure afterwards
     * and the approval would have meant nothing.
     */
    public function updatePayment(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'price1' => ['required'],
            'source1' => ['nullable', 'string', 'max:200'],
            'selected_date1' => ['nullable', 'string', 'max:200'],
        ]);

        $payment = PaymentReceived::findOrFail($data['id']);

        $date = $data['selected_date1'] ?? '';

        return $this->ok($payment->update([
            'payment' => $data['price1'],
            'source' => $data['source1'] ?? '',
            'date' => $date,
            'date_n' => $this->normaliseDate($date),
            'status' => Setting::current()->paymentEntryStatus(),
        ]));
    }

    /**
     * Fills date_n alongside the legacy VARCHAR date.
     *
     * date_n is the sortable copy every reporting query reads — the dashboard's
     * monthly series among them — and it was only ever populated by the
     * `dates:normalize` backfill. A payment recorded here would have been
     * invisible to those queries until someone remembered to re-run it.
     *
     * d/m/Y: payments are written dd/mm from id 302 on, by this form and by
     * the admin's. Rows before that are mm/dd and NormalizeDates holds the
     * boundary. Anything that does not parse cleanly stores null rather than
     * a guess.
     */
    /**
     * The sortable form of a date the user typed.
     *
     * Every date field on this side of the app is a jQuery UI picker set to
     * dd/mm/yyyy, and the legacy column it lands in is a VARCHAR that cannot be
     * ordered. `date_n` is the DATE column added alongside it for exactly that,
     * and rows written without one sort to the bottom whatever their date says,
     * so every create that stores `date` must fill this too.
     *
     * Null for anything unparseable, which is what the column already holds for
     * the handful of legacy rows whose date was blank or malformed.
     */
    private function normaliseDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('d/m/Y|', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($date === false || ! empty($errors['warning_count']) || ! empty($errors['error_count'])) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    // ------------------------------------------------------------- profile

    public function profile()
    {
        return view('construction.profile', ['user' => Auth::guard('web')->user()]);
    }

    /**
     * A worker's picture is reviewed before it goes live, the same as their
     * material and payment entries. AvatarService decides that, not this
     * method — clients post to the client controller and get the direct path.
     */
    public function saveProfilePhoto(Request $request, AvatarService $avatars)
    {
        $request->validate(['photo' => AvatarService::RULES]);

        $live = $avatars->storeForUser(Auth::guard('web')->user(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'live' => $live,
            'message' => $live
                ? 'Your profile picture has been updated.'
                : 'Your picture has been sent for approval.',
        ]);
    }

    /** Acknowledge an approval or rejection shown in the bell. */
    public function markAvatarSeen()
    {
        $user = Auth::guard('web')->user();

        if ($user && (int) $user->avatar_seen === 0) {
            $user->forceFill(['avatar_seen' => 1])->save();
        }

        return $this->ok(true);
    }

    // ------------------------------------------------- material catalogue

    /*
     * The Category screens, previously admin-only. Workers now reach the same
     * three pages under /construction, so a site can be set up without an
     * administrator.
     *
     * The pages themselves are one copy — partials/category/* rendered with a
     * different prefix — and these methods mirror AdminSettingController's,
     * writing the same three tables. The legacy name swaps are preserved
     * because the shared views hardcode the endpoint names: save_Amaterial
     * writes a CIVIL category and get_Agategory reads FINISHING ones.
     */

    public function addCivil()
    {
        return view('construction.add_civil');
    }

    public function addFinishing()
    {
        return view('construction.add_finishing');
    }

    public function addLabour()
    {
        return view('construction.add_labour', [
            'sites' => Site::all(),
            'labourTypes' => $this->labourTypeList(),
        ]);
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

    /**
     * Named saveLabourType, not saveLabour: ConstructionController already has
     * a labour route family for instalments, and `Labour` here is the type
     * catalogue a site's instalments are recorded against.
     */
    public function saveLabourType(Request $request)
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

        $matches = fn ($value) => mb_strtolower(trim((string) $value)) === mb_strtolower($name);

        $assignments = Labour::get()->filter(fn ($row) => $matches($row->type));

        /*
         * An assignment that carries a contract value, or that has instalments
         * recorded against it, is real work — removing the name would leave
         * those rows referring to a type no longer on offer, and the union in
         * labourTypeList() would put it straight back in the list anyway.
         *
         * An assignment with neither is a leftover: someone picked a site while
         * creating the type, or the type turned out to be wrong. Those go with
         * the name, which is what makes this button usable for the tidying-up
         * it exists to do.
         */
        $inUse = $assignments->filter(function ($row) use ($name) {
            $hasInstalments = LabourInstalment::where('project_id', $row->project_id)
                ->get()
                ->contains(fn ($i) => mb_strtolower(trim((string) $i->type)) === mb_strtolower($name));

            return (float) ($row->total ?: 0) !== 0.0 || $hasInstalments;
        });

        if ($inUse->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '"'.$name.'" is in use on '.$inUse->count().' '
                    .\Illuminate\Support\Str::plural('site', $inUse->count())
                    .' and cannot be removed.',
            ]);
        }

        $assignments->each(fn ($row) => $row->delete());

        LabourCategory::get()
            ->filter(fn ($row) => $matches($row->type))
            ->each(fn ($row) => $row->delete());

        return response()->json([
            'success' => true,
            'removed' => $assignments->count(),
            'types' => $this->labourTypeList(),
        ]);
    }

    /** Swapped by legacy naming: delete_category removes a FINISHING one. */
    public function deleteCategory(Request $request)
    {
        return $this->ok((bool) BCategory::destroy($request->input('userId')));
    }

    /** Swapped by legacy naming: delete_Bcategory removes a CIVIL one. */
    public function deleteBCategory(Request $request)
    {
        return $this->ok((bool) Category::destroy($request->input('userId')));
    }

    // ---------------------------------------------------------------- reads

    /**
     * Every site, running and closed. The Sites screen fetches this once and
     * splits it between its two tabs client-side, so switching tabs costs no
     * request — `site_status` is in the payload for that.
     */
    public function getSite()
    {
        return response()->json(['data' => Site::all()]);
    }

    /** Construction.php:427-435 — calls Sites::get_bricks() with no arguments,
     *  which is a PHP error in the legacy app. Returns an empty set instead. */
    public function getBricks()
    {
        return response()->json(['data' => []]);
    }

    /** Construction.php:71-100 */
    public function showBricks(string $id, ?string $category = null)
    {
        $category = $this->decodeCategory($category);

        // Newest first. `date` is the legacy VARCHAR and sorts alphabetically,
        // so the ordering is done on the normalised DATE column beside it; id
        // breaks ties, which keeps several entries on one day in the order they
        // were recorded. Rows with no parseable date sort to the bottom.
        $rows = Material::where('project_id', $id)
            ->where('type', $category)
            ->where('status', 1)
            ->orderByDesc('date_n')
            ->orderByDesc('id')
            ->get();

        // Legacy: heading totals deliberately NOT status-filtered (line 82).
        $all = Material::where('project_id', $id)->where('type', $category)->get();

        return response()->json([
            'total_price' => $this->sum($all->pluck('price')),
            'quantity' => $this->sum($all->pluck('quantity')),
            'data' => $rows,
        ]);
    }

    /** Construction.php:102-133 */
    public function showBCategory(string $id, ?string $category = null)
    {
        $category = $this->decodeCategory($category);

        // Newest first. `date` is the legacy VARCHAR and sorts alphabetically,
        // so the ordering is done on the normalised DATE column beside it; id
        // breaks ties, which keeps several entries on one day in the order they
        // were recorded. Rows with no parseable date sort to the bottom.
        $rows = BMaterial::where('project_id', $id)
            ->where('type', $category)
            ->where('status', 1)
            ->orderByDesc('date_n')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('price')),
            'quantity' => $this->sum($rows->pluck('quantity')),
            'data' => $rows,
        ]);
    }

    /** Construction.php:388-426 */
    public function showLabour(string $id, ?string $category = null)
    {
        $category = $this->decodeCategory($category);

        // Newest first. `date` is the legacy VARCHAR and sorts alphabetically,
        // so the ordering is done on the normalised DATE column beside it; id
        // breaks ties, which keeps several entries on one day in the order they
        // were recorded. Rows with no parseable date sort to the bottom.
        $rows = LabourInstalment::where('project_id', $id)
            ->where('type', $category)
            ->where('status', 1)
            ->orderByDesc('date_n')
            ->orderByDesc('id')
            ->get();

        // Legacy: heading total NOT status-filtered (line 400).
        $all = LabourInstalment::where('project_id', $id)->where('type', $category)->get();

        return response()->json([
            'total_price' => $this->sum($all->pluck('instalmet')),
            'project_price' => Labour::where('project_id', $id)->where('type', $category)->value('total'),
            'data' => $rows,
        ]);
    }

    /** Construction.php:135-163 */
    public function showCivilTotal(string $id)
    {
        $rows = Material::where('project_id', $id)->where('status', 1)->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('price')),
            'data' => $rows,
        ]);
    }

    /** Construction.php:307-335 */
    public function showFinishTotal(string $id)
    {
        $rows = BMaterial::where('project_id', $id)->where('status', 1)->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('price')),
            'data' => $rows,
        ]);
    }

    /** Construction.php:365-385 */
    public function showLabourTotal(string $id)
    {
        $rows = LabourInstalment::where('project_id', $id)->where('status', 1)->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('instalmet')),
            'data' => $rows,
        ]);
    }

    /** Construction.php:249-277 and :336-364 — same query, two endpoints. */
    public function miscTotal(string $id)
    {
        return $this->showMiscTotal($id);
    }

    public function showMiscTotal(string $id)
    {
        // Newest first. `date` is the legacy VARCHAR and sorts alphabetically,
        // so the ordering is done on the normalised DATE column beside it; id
        // breaks ties, which keeps several entries on one day in the order they
        // were recorded. Rows with no parseable date sort to the bottom.
        $rows = Misc::where('proj_id', $id)
            ->orderByDesc('date_n')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('price')),
            'data' => $rows,
        ]);
    }

    /** Construction.php:278-306 */
    public function returnTotal(string $id)
    {
        $rows = ReturnPayment::where('proj_id', $id)->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('price')),
            'data' => $rows,
        ]);
    }

    /**
     * Construction.php:164-245 — every ledger line for a project, merged from
     * five tables and sorted oldest-first.
     *
     * The legacy sort parses every date as 'd/m/Y' (line 228). That is correct
     * for four of the five sources, but `material` rows with id <= 226 were
     * written as m/d/Y, so those fail to parse, fall back to timestamp 0 and
     * sort to the very top. Once the date_n normalisation lands this can sort
     * on a real DATE column; until then the behaviour is reproduced exactly.
     */
    public function totalEntries(string $id)
    {
        $rows = collect();

        foreach (BMaterial::where('project_id', $id)->get() as $r) {
            $rows->push(['date' => $r->date, 'type' => $r->type, 'detail' => $r->detail,
                'quantity' => $r->quantity, 'price' => $r->price, 'source' => 'Finishing']);
        }

        foreach (Misc::where('proj_id', $id)->get() as $r) {
            $rows->push(['date' => $r->date, 'type' => '', 'detail' => $r->detail,
                'quantity' => '', 'price' => $r->price, 'source' => 'Miscellaneous']);
        }

        foreach (LabourInstalment::where('project_id', $id)->get() as $r) {
            $rows->push(['date' => $r->date, 'type' => '', 'detail' => $r->description,
                'quantity' => '', 'price' => $r->instalmet, 'source' => 'Labour Instalment']);
        }

        foreach (Material::where('project_id', $id)->get() as $r) {
            $rows->push(['date' => $r->date, 'type' => $r->type, 'detail' => '',
                'quantity' => $r->quantity, 'price' => $r->price, 'source' => 'Civil']);
        }

        foreach (ReturnPayment::where('proj_id', $id)->get() as $r) {
            $rows->push(['date' => $r->date, 'type' => '', 'detail' => $r->detail,
                // A label like the other four, not the table's name. It is also
                // what the row highlight keys off in the view.
                'quantity' => '', 'price' => $r->price, 'source' => 'Return Payment']);
        }

        $sorted = $rows->sortBy(function (array $row) {
            $parsed = \DateTime::createFromFormat('d/m/Y', trim((string) ($row['date'] ?? '')));

            return $parsed ? $parsed->getTimestamp() : 0;
        })->values();

        return response()->json(['data' => $sorted]);
    }

    // ------------------------------------------------------- modal prefills

    public function getSiteDetails(Request $request)
    {
        return $this->found(Site::find($request->query('userId')));
    }

    public function getCivilDetails(Request $request)
    {
        return $this->found(Material::find($request->query('userId')));
    }

    public function getFinishDetails(Request $request)
    {
        return $this->found(BMaterial::find($request->query('userId')));
    }

    public function getLabourDetails(Request $request)
    {
        return $this->found(LabourInstalment::find($request->query('userId')));
    }

    public function getMiscDetails(Request $request)
    {
        return $this->found(Misc::find($request->query('userId')));
    }

    public function getReturnDetails(Request $request)
    {
        return $this->found(ReturnPayment::find($request->query('userId')));
    }

    // --------------------------------------------------------------- writes

    public function saveSite(Request $request)
    {
        $data = $request->validate([
            'phase' => ['required', 'string', 'max:200'],
            'project_name' => ['required', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            'site_status' => ['nullable', 'in:'.implode(',', Site::statuses())],
        ]);

        // A new site is always being built unless the form says otherwise.
        $data['site_status'] = $data['site_status'] ?? Site::STATUS_RUNNING;

        return $this->ok(Site::create($data)->exists);
    }

    /** Construction.php:540-616 — add a civil material entry + rollup. */
    public function addBrick(Request $request)
    {
        $data = $request->validate([
            'proj_id' => ['required'],
            'catgory' => ['required', 'string', 'max:200'],
            'brick_quantity' => ['nullable'],
            'brick_price' => ['nullable'],
            'selected_date1' => ['nullable', 'string', 'max:200'],
        ]);

        $site = Site::findOrFail($data['proj_id']);

        Material::create([
            'quantity' => $data['brick_quantity'] ?? 0,
            'price' => $data['brick_price'] ?? 0,
            'proj_name' => $site->stamp_name,
            'login_user' => Auth::guard('web')->user()?->name,
            'project_id' => $data['proj_id'],
            'date' => $data['selected_date1'] ?? '',
            'date_n' => $this->normaliseDate($data['selected_date1'] ?? null),
            'type' => $data['catgory'],
            'status' => Setting::current()->civilEntryStatus(),
        ]);

        $this->rollups->civil($data['proj_id'], $data['catgory']);

        return $this->ok(true);
    }

    /** Construction.php:617-694 — add a finishing material entry + rollup. */
    public function addBCategory(Request $request)
    {
        $data = $request->validate([
            'proj_id' => ['required'],
            'catgory' => ['required', 'string', 'max:200'],
            'Detail' => ['nullable', 'string', 'max:200'],
            'brick_quantity' => ['nullable'],
            'brick_price' => ['nullable'],
            'selected_date2' => ['nullable', 'string', 'max:200'],
        ]);

        $site = Site::findOrFail($data['proj_id']);

        BMaterial::create([
            'detail' => $data['Detail'] ?? '',
            'quantity' => $data['brick_quantity'] ?? 0,
            'price' => $data['brick_price'] ?? 0,
            'project_id' => $data['proj_id'],
            'date' => $data['selected_date2'] ?? '',
            'date_n' => $this->normaliseDate($data['selected_date2'] ?? null),
            'type' => $data['catgory'],
            'login_user' => Auth::guard('web')->user()?->name,
            'proj_name' => $site->stamp_name,
            'status' => Setting::current()->finishEntryStatus(),
        ]);

        $this->rollups->finishing($data['proj_id'], $data['catgory']);

        return $this->ok(true);
    }

    /** Construction.php:697-762 — labour instalments are never held for approval. */
    public function labourInstalment(Request $request)
    {
        $data = $request->validate([
            'proj_id' => ['required'],
            'labour_type' => ['required', 'string', 'max:200'],
            'Detail' => ['nullable', 'string', 'max:200'],
            'bill_labour' => ['nullable'],
            'selected_date' => ['nullable', 'string', 'max:200'],
        ]);

        LabourInstalment::create([
            'description' => $data['Detail'] ?? '',
            'date' => $data['selected_date'] ?? '',
            'date_n' => $this->normaliseDate($data['selected_date'] ?? null),
            'instalmet' => $data['bill_labour'] ?? 0,
            'project_id' => $data['proj_id'],
            'type' => $data['labour_type'],
            'status' => 1,
        ]);

        $this->rollups->labour($data['proj_id'], $data['labour_type']);

        return $this->ok(true);
    }

    /** Construction.php:764-786 */
    public function miscAdd(Request $request)
    {
        $data = $this->validateMisc($request);

        return $this->ok(Misc::create($data)->exists);
    }

    /** Construction.php:787-810 — same field names as miscAdd. */
    public function returnPayment(Request $request)
    {
        $data = $this->validateMisc($request);

        return $this->ok(ReturnPayment::create($data)->exists);
    }

    protected function validateMisc(Request $request): array
    {
        $input = $request->validate([
            'proj_id' => ['required'],
            'Detail_misc' => ['nullable', 'string', 'max:200'],
            'ammoun_misc' => ['nullable'],
            'selected_date3' => ['nullable', 'string', 'max:200'],
        ]);

        return [
            'detail' => $input['Detail_misc'] ?? '',
            'date' => $input['selected_date3'] ?? '',
            'date_n' => $this->normaliseDate($input['selected_date3'] ?? null),
            'price' => $input['ammoun_misc'] ?? 0,
            'proj_id' => $input['proj_id'],
        ];
    }

    // -------------------------------------------------------------- updates

    public function updateSite(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'phase' => ['nullable', 'string', 'max:200'],
            'project_name' => ['nullable', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:200'],
            'site_status' => ['nullable', 'in:'.implode(',', Site::statuses())],
        ]);

        $site = Site::findOrFail($data['id']);

        $fields = $request->only('phase', 'project_name', 'sector');

        // Absent from the payload means "leave it alone" — only the Sites
        // screen's modal sends it.
        if (isset($data['site_status'])) {
            $fields['site_status'] = $data['site_status'];
        }

        return $this->ok($site->update($fields));
    }

    /** Rollups are refreshed after an edit — the legacy code did not do this,
     *  so a corrected price left total_payement stale until the next insert. */
    public function updateCivil(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'type' => ['nullable', 'string', 'max:200'],
            'quantity' => ['nullable'],
            'price' => ['nullable'],
        ]);

        $row = Material::findOrFail($data['id']);
        $previousType = $row->type;
        $ok = $row->update($request->only('type', 'quantity', 'price'));

        $this->rollups->civil($row->project_id, $row->type);
        if ($previousType !== $row->type) {
            $this->rollups->civil($row->project_id, $previousType);
        }

        return $this->ok($ok);
    }

    public function updateFinish(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'type' => ['nullable', 'string', 'max:200'],
            'quantity' => ['nullable'],
            'price' => ['nullable'],
            'detail' => ['nullable', 'string', 'max:200'],
        ]);

        $row = BMaterial::findOrFail($data['id']);
        $previousType = $row->type;
        $ok = $row->update($request->only('type', 'quantity', 'price', 'detail'));

        $this->rollups->finishing($row->project_id, $row->type);
        if ($previousType !== $row->type) {
            $this->rollups->finishing($row->project_id, $previousType);
        }

        return $this->ok($ok);
    }

    public function updateLabour(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'type' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:200'],
            'instalmet' => ['nullable'],
        ]);

        $row = LabourInstalment::findOrFail($data['id']);
        $previousType = $row->type;
        $ok = $row->update($request->only('type', 'description', 'instalmet'));

        $this->rollups->labour($row->project_id, $row->type);
        if ($previousType !== $row->type) {
            $this->rollups->labour($row->project_id, $previousType);
        }

        return $this->ok($ok);
    }

    public function updateReturn(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'detail' => ['nullable', 'string', 'max:200'],
            'price' => ['nullable'],
        ]);

        return $this->ok(ReturnPayment::findOrFail($data['id'])->update($request->only('detail', 'price')));
    }

    /**
     * New. site_settings.php:3172 has always POSTed to construction/update_misc,
     * but Construction.php never defined that method — the request 404'd and the
     * edit silently did nothing.
     */
    public function updateMisc(Request $request)
    {
        $data = $request->validate([
            'id' => ['required'],
            'detail' => ['nullable', 'string', 'max:200'],
            'price' => ['nullable'],
        ]);

        return $this->ok(Misc::findOrFail($data['id'])->update($request->only('detail', 'price')));
    }

    // -------------------------------------------------------------- deletes

    public function deleteSite(Request $request)
    {
        return $this->ok((bool) Site::destroy($request->input('userId')));
    }

    public function deleteCivil(Request $request)
    {
        $row = Material::find($request->input('userId'));

        if (! $row) {
            return $this->ok(false);
        }

        [$project, $type] = [$row->project_id, $row->type];
        $ok = (bool) $row->delete();
        $this->rollups->civil($project, $type);

        return $this->ok($ok);
    }

    public function deleteFinish(Request $request)
    {
        $row = BMaterial::find($request->input('userId'));

        if (! $row) {
            return $this->ok(false);
        }

        [$project, $type] = [$row->project_id, $row->type];
        $ok = (bool) $row->delete();
        $this->rollups->finishing($project, $type);

        return $this->ok($ok);
    }

    public function deleteLabour(Request $request)
    {
        $row = LabourInstalment::find($request->input('userId'));

        if (! $row) {
            return $this->ok(false);
        }

        [$project, $type] = [$row->project_id, $row->type];
        $ok = (bool) $row->delete();
        $this->rollups->labour($project, $type);

        return $this->ok($ok);
    }

    public function deleteMisc(Request $request)
    {
        return $this->ok((bool) Misc::destroy($request->input('userId')));
    }

    public function deleteReturn(Request $request)
    {
        return $this->ok((bool) ReturnPayment::destroy($request->input('userId')));
    }

    // -------------------------------------------------------------- helpers

    /** The legacy URLs are built by string concatenation, so a category with a
     *  space arrives percent-encoded; Construction.php:394 undid that by hand. */
    protected function decodeCategory(?string $category): string
    {
        return trim(urldecode((string) $category));
    }

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
