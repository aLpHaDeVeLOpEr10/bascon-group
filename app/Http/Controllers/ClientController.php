<?php

namespace App\Http\Controllers;

use App\Models\ArchitectDetailCompany;
use App\Models\BCategory;
use App\Models\BMaterial;
use App\Models\Category;
use App\Models\ConstructionDetail;
use App\Models\FinishTotal;
use App\Models\Labour;
use App\Models\LabourInstalment;
use App\Models\LabourTotal;
use App\Models\Material;
use App\Models\Misc;
use App\Models\PaymentReceived;
use App\Models\ReturnPayment;
use App\Models\Site;
use App\Models\TotalPayement;
use App\Services\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Port of application/controllers/Client.php — read-only dashboards for the
 * Client role.
 *
 * SECURITY FIX. The legacy JSON endpoints took the project id from the URL
 * (Client.php:33, :71, :81, :95, :345, :360, :376 all read
 * $this->uri->segment(3)), so any signed-in client could read any other
 * client's project by editing the address bar. The page-rendering methods on
 * the same controller correctly used the session (Client.php:122, :217, :266,
 * :310). Every method here resolves the project from the session instead, and
 * a URL id is accepted only when it matches. The routes still carry the {id}
 * segment so existing bookmarks and the AJAX URLs in the views keep working.
 */
class ClientController extends Controller
{
    /**
     * The only source of truth for which project this client may see.
     * A mismatched URL id is refused rather than silently ignored, so a
     * tampered link fails loudly instead of quietly showing the wrong project.
     */
    protected function projectId(?string $urlId = null): int|string|null
    {
        $sessionId = Auth::guard('web')->user()?->project_id;

        if ($urlId !== null && $sessionId !== null && (string) $urlId !== (string) $sessionId) {
            abort(403, 'This project does not belong to your account.');
        }

        return $sessionId;
    }

    // ---------------------------------------------------------------- pages

    // The views build their AJAX URLs from $id. The legacy views read it out
    // of the session inline; it is passed in explicitly now.

    public function showPayments()
    {
        return view('client.payments', ['id' => $this->projectId()]);
    }

    public function finishPayments()
    {
        return view('client.finish_payments', ['id' => $this->projectId()]);
    }

    public function labourPayment()
    {
        return view('client.labour_payment', ['id' => $this->projectId()]);
    }

    public function miscPayment()
    {
        return view('client.misc_payment', ['id' => $this->projectId()]);
    }

    /**
     * The four cost ledgers, entry by entry.
     *
     * The pages above are roll-ups: one line per material, and no way to see
     * what makes up a figure. This is the same view of the same four tables
     * that the site team works in — pick a category, see every entry recorded
     * against it — read-only and fixed to this client's own project.
     *
     * The category lists are catalogues shared by every site, except labour
     * types, which are created per project.
     */
    public function constructionPayments()
    {
        $project = $this->projectId();

        return view('client.construction_payments', [
            'id' => $project,
            'civilCategories' => Category::orderBy('material_name')->pluck('material_name')->values(),
            'finishCategories' => BCategory::orderBy('material_name')->pluck('material_name')->values(),
            'labourTypes' => Labour::where('project_id', $project)->pluck('type')->values(),
        ]);
    }

    /**
     * Ledger  => [model, project column, category column, amount column,
     *             does it have an approval status?]
     *
     * Miscellaneous has no categories and no approval step, which is why both
     * of its entries here are null/false rather than a special case in the
     * query below.
     */
    private const LEDGERS = [
        'civil' => [Material::class, 'project_id', 'type', 'price', true],
        'finishing' => [BMaterial::class, 'project_id', 'type', 'price', true],
        'labour' => [LabourInstalment::class, 'project_id', 'type', 'instalmet', true],
        'misc' => [Misc::class, 'proj_id', null, 'price', false],
    ];

    /** Entries in one ledger, narrowed to one category where it has them. */
    public function conEntries(Request $request)
    {
        $kind = (string) $request->query('kind');

        abort_unless(isset(self::LEDGERS[$kind]), 404);

        [$model, $projectKey, $categoryKey, $amount, $gated] = self::LEDGERS[$kind];
        $category = trim((string) $request->query('category', ''));

        $rows = $model::where($projectKey, $this->projectId())
            ->when($gated, fn ($q) => $q->where('status', 1))
            // Only civil, finishing and labour have categories; misc is asked
            // for whole, so the caller sends no category and none is applied.
            ->when($kind !== 'misc' && $category !== '', fn ($q) => $q->where($categoryKey, $category))
            // Newest first, matching the site team's tables. `date` is the
            // legacy VARCHAR and sorts alphabetically; date_n is the DATE
            // column beside it.
            ->orderByDesc('date_n')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck($amount)),
            'quantity' => $this->sum($rows->pluck('quantity')),
            'data' => $rows,
        ]);
    }

    /** Client.php:120-214 — the grand-total roll-up page. */
    public function totalPayment()
    {
        $id = $this->projectId();

        $civil = $this->sum(Material::where('project_id', $id)->where('status', 1)->pluck('price'));
        $finish = $this->sum(BMaterial::where('project_id', $id)->where('status', 1)->pluck('price'));
        $misc = $this->sum(Misc::where('proj_id', $id)->pluck('price'));
        $labour = $this->sum(LabourInstalment::where('project_id', $id)->where('status', 1)->pluck('instalmet'));
        $received = $this->sum(PaymentReceived::where('proj_id', $id)->pluck('payment'));
        $returned = $this->sum(ReturnPayment::where('proj_id', $id)->pluck('price'));

        $grandTotal = ($labour + $misc + $finish + $civil) - $returned;

        return view('client.total_payment', [
            'total_payments' => $received,
            'payment_recieved' => $received,
            'Remainung_Balace' => $received - $grandTotal,
            'labour_price' => $labour,
            'misc_price' => $misc,
            'finish_price' => $finish,
            'civil_price' => $civil,
            'Grand_total' => $grandTotal,
        ]);
    }

    /** Client.php:215-261 */
    public function projectManagement()
    {
        $id = $this->projectId();
        $site = Site::find($id);

        return view('client.project_mangement', [
            'id' => $id,
            'name' => $site?->display_name ?? '',
            'total_fee' => $site?->total_price ?? 0,
            'total_instalments' => $this->sum(ConstructionDetail::where('proj_id', $id)->pluck('payment')),
        ]);
    }

    /** Client.php:263-306 */
    public function architectManagement()
    {
        $id = $this->projectId();
        $site = Site::find($id);

        $fee = (float) ($site?->architect_fees ?? 0);
        $paid = $this->sum(ArchitectDetailCompany::where('proj_id', $id)->pluck('payment'));

        return view('client.architect_management', [
            'id' => $id,
            'name' => $site?->display_name ?? '',
            'total_fee' => $fee,
            'total_instalments' => $paid,
            'remaing_instalment' => $fee - $paid,
        ]);
    }

    /** Client.php:308-339 */
    public function paymentReceived()
    {
        $id = $this->projectId();
        $site = Site::find($id);

        return view('client.payment_recieved', [
            'id' => $id,
            'name' => $site?->display_name ?? '',
            'total_payments' => $this->sum(PaymentReceived::where('proj_id', $id)->pluck('payment')),
        ]);
    }

    // ---------------------------------------------------------------- JSON

    /** Client.php:31-54 — civil material totals (Sites::get_payments). */
    public function getPayment(?string $id = null)
    {
        $project = $this->projectId($id);

        return response()->json([
            'total_price' => $this->sum(
                Material::where('project_id', $project)->where('status', 1)->pluck('price')
            ),
            'data' => TotalPayement::where('project_id', $project)->get(),
        ]);
    }

    /** Client.php:69-77 */
    public function getFinishPayment(?string $id = null)
    {
        return response()->json([
            'data' => FinishTotal::where('project_id', $this->projectId($id))->get(),
        ]);
    }

    /** Client.php:79-87 */
    public function getLabourPayment(?string $id = null)
    {
        return response()->json([
            'data' => LabourTotal::where('project_id', $this->projectId($id))->get(),
        ]);
    }

    /** Client.php:90-118 */
    public function miscPayments(?string $id = null)
    {
        $project = $this->projectId($id);
        $rows = Misc::where('proj_id', $project)->get();

        return response()->json([
            'total_price' => $this->sum($rows->pluck('price')),
            'data' => $rows,
        ]);
    }

    /** Client.php:340-352 */
    public function showConDetail(?string $id = null)
    {
        return response()->json([
            'data' => ConstructionDetail::where('proj_id', $this->projectId($id))->get(),
        ]);
    }

    /** Client.php:355-367 */
    public function showArchDetail(?string $id = null)
    {
        return response()->json([
            'data' => ArchitectDetailCompany::where('proj_id', $this->projectId($id))->get(),
        ]);
    }

    /** Client.php:369-381 */
    public function showConDetail12(?string $id = null)
    {
        return response()->json([
            'data' => PaymentReceived::where('proj_id', $this->projectId($id))->get(),
        ]);
    }

    /** Legacy columns are a mix of INT and VARCHAR, and some rows are ''. */
    protected function sum(iterable $values): float|int
    {
        $total = 0;

        foreach ($values as $value) {
            $total += (float) ($value ?: 0);
        }

        return $total == (int) $total ? (int) $total : $total;
    }

    // ------------------------------------------------------------- profile

    public function profile()
    {
        return view('client.profile', ['user' => Auth::guard('web')->user()]);
    }

    /** Clients publish directly — nothing to approve. */
    public function saveProfilePhoto(Request $request, AvatarService $avatars)
    {
        $request->validate(['photo' => AvatarService::RULES]);

        $avatars->storeForUser(Auth::guard('web')->user(), $request->file('photo'));

        return response()->json([
            'success' => true,
            'live' => true,
            'message' => 'Your profile picture has been updated.',
        ]);
    }
}
