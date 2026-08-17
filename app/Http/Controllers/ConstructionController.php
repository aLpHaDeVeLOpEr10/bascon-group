<?php

namespace App\Http\Controllers;

use App\Models\BCategory;
use App\Models\BMaterial;
use App\Models\Category;
use App\Models\Labour;
use App\Models\LabourInstalment;
use App\Models\Material;
use App\Models\Misc;
use App\Models\PaymentReceived;
use App\Models\ReturnPayment;
use App\Models\Setting;
use App\Models\Site;
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

        $rows = Material::where('project_id', $id)
            ->where('type', $category)
            ->where('status', 1)
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

        $rows = BMaterial::where('project_id', $id)
            ->where('type', $category)
            ->where('status', 1)
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

        $rows = LabourInstalment::where('project_id', $id)
            ->where('type', $category)
            ->where('status', 1)
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
        $rows = Misc::where('proj_id', $id)->get();

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
                'quantity' => '', 'price' => $r->price, 'source' => 'return_payment']);
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
