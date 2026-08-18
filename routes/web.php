<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConstructionController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy URL map
|--------------------------------------------------------------------------
|
| CodeIgniter had no route file — every URL was the bare
| controller/method/arg convention, and its router is case-INSENSITIVE. The
| views exploit that inconsistently: admin/show_expense.php calls
| `Admin_setting/get_expense_data` on line 411 and `admin_setting/update_expense`
| on line 449; admin_sidebar.php:43 uses `admin_setting/add_user` while other
| views use `Add_user`.
|
| Everything is therefore registered once, in lowercase. The global
| NormalizeLegacyUrl middleware lowercases the controller and method segments
| of every incoming request, which restores CI's case-insensitivity without
| duplicating 139 route definitions. Segments beyond the method are left alone
| because they carry data (show_bricks/63/Cement).
|
| Legacy typos in the URLs are load-bearing and deliberately preserved:
| update_rerturn, get_site_comapny, get_funish_payment, get_payment_arcchi,
| and get_Agategory/get_Bgategory (which are swapped — get_Agategory returns
| b_category rows).
|
*/

// ---------------------------------------------------------------- worker login

Route::prefix('login')->group(function () {
    Route::get('/', [LoginController::class, 'index']);
    Route::post('login_user', [LoginController::class, 'authenticate']);
    Route::match(['get', 'post'], 'logout', [LoginController::class, 'logout']);
});

Route::get('/', [LoginController::class, 'index']);

// ----------------------------------------------------------------- admin login

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::post('login_admin', [AdminController::class, 'authenticate']);
    Route::match(['get', 'post'], 'logout', [AdminController::class, 'logout']);
});

// ----------------------------------------------------------------- construction

Route::prefix('construction')->group(function () {
    Route::middleware('worker')->group(function () {
        // pages
        Route::get('add_site', [ConstructionController::class, 'addSite']);
        Route::get('show_site', [ConstructionController::class, 'showSite']);
        Route::get('show_details/{id}', [ConstructionController::class, 'showDetails']);

        // Category catalogue. Mirrors the admin_setting routes of the same
        // names — same screens, same tables, reachable without an admin
        // account. The endpoint names carry the legacy swaps (save_amaterial
        // writes a CIVIL row, get_agategory reads FINISHING ones) because the
        // shared views in partials/category hardcode them for both sides.
        // Client payments. No delete route by design — a worker may correct a
        // payment but never remove one; see ConstructionController::payments.
        Route::get('payments', [ConstructionController::class, 'payments']);
        Route::get('payment_details/{id}', [ConstructionController::class, 'paymentDetails']);
        Route::get('get_payments/{id}', [ConstructionController::class, 'getPayments']);
        Route::get('get_payment_details', [ConstructionController::class, 'getPaymentDetails']);
        Route::post('save_payment', [ConstructionController::class, 'savePayment']);
        Route::post('update_payment', [ConstructionController::class, 'updatePayment']);
        Route::post('payments_seen', [ConstructionController::class, 'markPaymentsSeen']);

        Route::get('add_civil', [ConstructionController::class, 'addCivil']);
        Route::get('add_finishing', [ConstructionController::class, 'addFinishing']);
        Route::get('add_labour', [ConstructionController::class, 'addLabour']);
        Route::get('get_agategory', [ConstructionController::class, 'getAGategory']);
        Route::get('get_bgategory', [ConstructionController::class, 'getBGategory']);
        Route::get('get_labourcat', [ConstructionController::class, 'getLabourCat']);
        Route::post('save_amaterial', [ConstructionController::class, 'saveAMaterial']);
        Route::post('save_bmaterial', [ConstructionController::class, 'saveBMaterial']);
        Route::post('save_labour', [ConstructionController::class, 'saveLabourType']);
        Route::post('delete_category', [ConstructionController::class, 'deleteCategory']);
        Route::post('delete_bcategory', [ConstructionController::class, 'deleteBCategory']);

        // reads
        Route::get('get_site', [ConstructionController::class, 'getSite']);
        Route::get('get_bricks', [ConstructionController::class, 'getBricks']);
        Route::get('show_bricks/{id}/{category?}', [ConstructionController::class, 'showBricks']);
        Route::get('show_b_category/{id}/{category?}', [ConstructionController::class, 'showBCategory']);
        Route::get('show_labour/{id}/{category?}', [ConstructionController::class, 'showLabour']);
        Route::get('show_civil_total/{id}', [ConstructionController::class, 'showCivilTotal']);
        Route::get('show_finish_total/{id}', [ConstructionController::class, 'showFinishTotal']);
        Route::get('show_misc_total/{id}', [ConstructionController::class, 'showMiscTotal']);
        Route::get('show_labour_total/{id}', [ConstructionController::class, 'showLabourTotal']);
        Route::get('misc_total/{id}', [ConstructionController::class, 'miscTotal']);
        Route::get('return_total/{id}', [ConstructionController::class, 'returnTotal']);
        Route::get('total_entries/{id}', [ConstructionController::class, 'totalEntries']);

        // edit-modal prefills (the views send these as GET with ?userId=)
        Route::get('get_site_details', [ConstructionController::class, 'getSiteDetails']);
        Route::get('get_civil_details', [ConstructionController::class, 'getCivilDetails']);
        Route::get('get_finish_details', [ConstructionController::class, 'getFinishDetails']);
        Route::get('get_labour_details', [ConstructionController::class, 'getLabourDetails']);
        Route::get('get_misc_details', [ConstructionController::class, 'getMiscDetails']);
        Route::get('get_return_details', [ConstructionController::class, 'getReturnDetails']);

        // writes
        Route::post('save_site', [ConstructionController::class, 'saveSite']);
        Route::post('add_brick', [ConstructionController::class, 'addBrick']);
        Route::post('add_b_category', [ConstructionController::class, 'addBCategory']);
        Route::post('labour_instalment', [ConstructionController::class, 'labourInstalment']);
        Route::post('misc_add', [ConstructionController::class, 'miscAdd']);
        Route::post('return_payment', [ConstructionController::class, 'returnPayment']);

        Route::post('update_site', [ConstructionController::class, 'updateSite']);
        Route::post('update_civil', [ConstructionController::class, 'updateCivil']);
        Route::post('update_finish', [ConstructionController::class, 'updateFinish']);
        Route::post('update_labour', [ConstructionController::class, 'updateLabour']);
        Route::post('update_rerturn', [ConstructionController::class, 'updateReturn']);
        // NOT present in the CodeIgniter controller — site_settings.php:3172 has
        // always POSTed here and always got a 404, so editing a Miscellaneous
        // row silently failed. Implemented so the modal works.
        Route::post('update_misc', [ConstructionController::class, 'updateMisc']);

        Route::post('delete_user', [ConstructionController::class, 'deleteSite']);
        Route::post('delete_civil', [ConstructionController::class, 'deleteCivil']);
        Route::post('delete_finish', [ConstructionController::class, 'deleteFinish']);
        Route::post('delete_labour', [ConstructionController::class, 'deleteLabour']);
        Route::post('delete_misc', [ConstructionController::class, 'deleteMisc']);
        Route::post('return_delete', [ConstructionController::class, 'deleteReturn']);
    });
});

// ---------------------------------------------------------------------- client

Route::prefix('client')->group(function () {
    Route::middleware('client')->group(function () {
        // pages
        Route::get('show_payments', [ClientController::class, 'showPayments']);
        Route::get('finish_payments', [ClientController::class, 'finishPayments']);
        Route::get('labour_payment', [ClientController::class, 'labourPayment']);
        Route::get('misc_payment', [ClientController::class, 'miscPayment']);
        Route::get('total_payment', [ClientController::class, 'totalPayment']);
        Route::get('project_mangement', [ClientController::class, 'projectManagement']);
        Route::get('architect_management', [ClientController::class, 'architectManagement']);
        Route::get('payment_recieved', [ClientController::class, 'paymentReceived']);

        // reads — the {id} is accepted for URL compatibility only; the project
        // is resolved from the session and a mismatched id is rejected.
        Route::get('get_payment/{id?}', [ClientController::class, 'getPayment']);
        Route::get('get_funish_payment/{id?}', [ClientController::class, 'getFinishPayment']);
        Route::get('get_labour_payment/{id?}', [ClientController::class, 'getLabourPayment']);
        Route::get('misc_payments/{id?}', [ClientController::class, 'miscPayments']);
        Route::get('show_con_detail/{id?}', [ClientController::class, 'showConDetail']);
        Route::get('show_arch_detail/{id?}', [ClientController::class, 'showArchDetail']);
        Route::get('show_con_detail12/{id?}', [ClientController::class, 'showConDetail12']);
    });
});

// --------------------------------------------------------------- admin_setting

Route::prefix('admin_setting')->group(function () {
    Route::middleware('admin')->group(function () {
        // pages
        Route::get('dashboard', [AdminSettingController::class, 'dashboard']);
        Route::get('add_user', [AdminSettingController::class, 'addUser']);
        Route::get('show_user', [AdminSettingController::class, 'showUser']);
        Route::get('add_civil', [AdminSettingController::class, 'addCivil']);
        Route::get('add_finishing', [AdminSettingController::class, 'addFinishing']);
        Route::get('add_labour', [AdminSettingController::class, 'addLabour']);
        Route::get('add_site', [AdminSettingController::class, 'addSite']);
        Route::get('add_con_site', [AdminSettingController::class, 'addConSite']);
        Route::get('show_site', [AdminSettingController::class, 'showSite']);
        Route::get('show_con_site', [AdminSettingController::class, 'showConSite']);
        Route::get('set_role', [AdminSettingController::class, 'setRole']);
        Route::get('add_expense', [AdminSettingController::class, 'addExpense']);
        Route::get('show_expense', [AdminSettingController::class, 'showExpense']);
        Route::get('civil_requets', [AdminSettingController::class, 'civilRequests']);
        Route::get('finish_requets', [AdminSettingController::class, 'finishRequests']);
        Route::get('payment_requets', [AdminSettingController::class, 'paymentRequests']);
        Route::get('show_details/{id}', [AdminSettingController::class, 'showDetails']);
        Route::get('show_details_company/{id}', [AdminSettingController::class, 'showDetailsCompany']);
        Route::get('show_con_details/{id}', [AdminSettingController::class, 'showConDetails']);

        // list reads
        Route::get('get_users', [AdminSettingController::class, 'getUsers']);
        Route::get('get_civil', [AdminSettingController::class, 'getCivil']);
        Route::get('get_finish', [AdminSettingController::class, 'getFinish']);
        Route::get('get_agategory', [AdminSettingController::class, 'getAGategory']);
        Route::get('get_bgategory', [AdminSettingController::class, 'getBGategory']);
        Route::get('get_labourcat', [AdminSettingController::class, 'getLabourCat']);
        Route::get('get_payment_requests', [AdminSettingController::class, 'getPaymentRequests']);
        Route::get('get_site', [AdminSettingController::class, 'getSite']);
        Route::get('get_site_comapny', [AdminSettingController::class, 'getSiteCompany']);
        Route::get('get_con_site', [AdminSettingController::class, 'getConSite']);
        Route::get('get_expense', [AdminSettingController::class, 'getExpense']);
        Route::get('get_misc_admin', [AdminSettingController::class, 'getMiscAdmin']);
        Route::get('show_b_category/{id}', [AdminSettingController::class, 'showBCategory']);
        Route::get('show_company_architect/{id}', [AdminSettingController::class, 'showCompanyArchitect']);
        Route::get('show_con_detail/{id}', [AdminSettingController::class, 'showConDetail']);
        Route::get('show_con_detail1/{id}', [AdminSettingController::class, 'showConDetail1']);

        // edit-modal prefills
        Route::get('get_user_details', [AdminSettingController::class, 'getUserDetails']);
        Route::get('get_site_details', [AdminSettingController::class, 'getSiteDetails']);
        Route::get('get_site_details_company', [AdminSettingController::class, 'getSiteDetailsCompany']);
        Route::get('get_site_details12', [AdminSettingController::class, 'getSiteDetails12']);
        Route::get('get_payment_edit', [AdminSettingController::class, 'getPaymentEdit']);
        Route::get('get_payment_arcchi', [AdminSettingController::class, 'getPaymentArchi']);
        Route::get('get_payment_company', [AdminSettingController::class, 'getPaymentCompany']);
        Route::get('get_expense_data', [AdminSettingController::class, 'getExpenseData']);
        Route::get('get_misc_data', [AdminSettingController::class, 'getMiscData']);

        // creates
        Route::post('save_user', [AdminSettingController::class, 'saveUser']);
        Route::post('save_amaterial', [AdminSettingController::class, 'saveAMaterial']);
        Route::post('save_bmaterial', [AdminSettingController::class, 'saveBMaterial']);
        Route::post('save_labour', [AdminSettingController::class, 'saveLabour']);
        Route::post('save_setting', [AdminSettingController::class, 'saveSetting']);
        Route::post('save_site', [AdminSettingController::class, 'saveSite']);
        Route::post('save_company_site', [AdminSettingController::class, 'saveCompanySite']);
        Route::post('save_con_site', [AdminSettingController::class, 'saveConSite']);
        Route::post('save_expense', [AdminSettingController::class, 'saveExpense']);
        Route::post('save_misc_admin', [AdminSettingController::class, 'saveMiscAdmin']);
        Route::post('add_brick', [AdminSettingController::class, 'addBrick']);
        Route::post('add_company_architect', [AdminSettingController::class, 'addCompanyArchitect']);
        Route::post('add_cons_instal', [AdminSettingController::class, 'addConsInstal']);
        Route::post('add_cons_payment', [AdminSettingController::class, 'addConsPayment']);

        // approvals
        Route::post('accept_civil', [AdminSettingController::class, 'acceptCivil']);
        Route::post('reject_civil', [AdminSettingController::class, 'rejectCivil']);
        Route::post('accept_finish', [AdminSettingController::class, 'acceptFinish']);
        Route::post('reject_finish', [AdminSettingController::class, 'rejectFinish']);
        Route::post('accept_payment', [AdminSettingController::class, 'acceptPayment']);
        Route::post('reject_payment', [AdminSettingController::class, 'rejectPayment']);

        // updates
        Route::post('update_user', [AdminSettingController::class, 'updateUser']);
        Route::post('update_site', [AdminSettingController::class, 'updateSite']);
        Route::post('update_site_company', [AdminSettingController::class, 'updateSiteCompany']);
        Route::post('update_site12', [AdminSettingController::class, 'updateSite12']);
        Route::post('update_payment', [AdminSettingController::class, 'updatePayment']);
        Route::post('update_payment_archi', [AdminSettingController::class, 'updatePaymentArchi']);
        Route::post('update_payment_company', [AdminSettingController::class, 'updatePaymentCompany']);
        Route::post('update_expense', [AdminSettingController::class, 'updateExpense']);
        Route::post('update_misc', [AdminSettingController::class, 'updateMisc']);

        // deletes
        Route::post('delete_user', [AdminSettingController::class, 'deleteUser']);
        Route::post('delete_category', [AdminSettingController::class, 'deleteCategory']);
        Route::post('delete_bcategory', [AdminSettingController::class, 'deleteBCategory']);
        Route::post('delete_user23', [AdminSettingController::class, 'deleteArchitectSite']);
        Route::post('delete_user_company', [AdminSettingController::class, 'deleteCompanySite']);
        Route::post('delete_user12', [AdminSettingController::class, 'deleteConSite']);
        Route::post('delete_payment', [AdminSettingController::class, 'deletePayment']);
        Route::post('delete_payment_archi', [AdminSettingController::class, 'deletePaymentArchi']);
        Route::post('delete_payment_company', [AdminSettingController::class, 'deletePaymentCompany']);
        Route::post('delete_expense', [AdminSettingController::class, 'deleteExpense']);
        Route::post('delete_misc', [AdminSettingController::class, 'deleteMisc']);
    });
});
