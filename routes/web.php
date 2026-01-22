<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\LandPlotController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerQuickSearchController;
   use App\Http\Controllers\TransferController;


    Route::middleware(['auth'])->group(function () {



   /*
   |--------------------------------------------------------------------------
   | التنازلات
   |--------------------------------------------------------------------------
   */

    Route::get(
    '/transfers/{transfer}/print',
    [TransferController::class, 'print']
    )->name('transfers.print');

    Route::post(
    '/transfers/{transfer}/restore-ownership',
    [TransferController::class, 'restoreOwnership']
    )->name('transfers.restoreOwnership');


    Route::get(
    '/transfers/create/{context}/{unit}',
    [TransferController::class, 'create']
    )->name('transfers.create');
    Route::post(
    '/transfers',
    [TransferController::class, 'store']
    )->name('transfers.store');
    Route::get(
        '/transfers',
        [TransferController::class, 'index']
    )->name('transfers.index');
    
    Route::get('/transfers/{transfer}/edit', [TransferController::class, 'edit'])
        ->name('transfers.edit');
    
    Route::put('/transfers/{transfer}', [TransferController::class, 'update'])
        ->name('transfers.update');
    
    Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])
    ->name('transfers.destroy');

   /*
   |--------------------------------------------------------------------------
   | STATEMENTS (PDF)
   |--------------------------------------------------------------------------
   */

    // بيان دفوعات المشروع كامل
    Route::get('/projects/{project}/statement', [ProjectController::class, 'statement'])
    ->name('projects.statement');

    // بيان دفوعات عمارة (مع الشطر إن وجد)
    Route::get('/buildings/{building}/statement', [BuildingController::class, 'statement'])
    ->name('buildings.statement');

    // بيان دفوعات القطع الأرضية
    Route::get('/lands/statement', [LandPlotController::class, 'statement'])
    ->name('lands.statement');

     /* -------------------------------------------------------
        البحث عن زبون  
    ------------------------------------------------------- */

    //Dashbord search
    Route::get('customers/quick-search', CustomerQuickSearchController::class)
    ->name('customers.quick-search');
    
    Route::get('/customers/{customer}/print', [CustomerController::class, 'print'])
    ->name('customers.print');
    Route::get('/customers', [CustomerController::class, 'index'])
    ->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])
    ->name('customers.show');
    Route::get('/customers/search', [CustomerController::class, 'search']);
     /* -------------------------------------------------------
        الدفوعات  
    ------------------------------------------------------- */
    Route::get('/payments/print', [PaymentController::class, 'print'])
    ->name('payments.print');

    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])
     ->name('payments.receipt');

    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])
    ->name('payments.destroy');

    Route::get('/apartments/{apartment}/payments/create',[PaymentController::class, 'createFromApartment']
    )->name('apartments.payments.create');

    Route::get('/shops/{shop}/payments/create',[PaymentController::class, 'createFromShop']
    )->name('shops.payments.create');

    Route::get('/lands/{land}/payments/create', [PaymentController::class, 'createFromLand'])
    ->name('lands.payments.create');

    Route::get('/payments/summary/{context}', [PaymentController::class, 'summary']);

    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])
    ->name('payments.edit');

    Route::put('/payments/{payment}', [PaymentController::class, 'update'])
    ->name('payments.update');

    Route::resource('payments', PaymentController::class)
    ->only(['index', 'store']);

    /* -------------------------------------------------------
        القطع الارضية  
    ------------------------------------------------------- */

    Route::resource('lands', \App\Http\Controllers\LandPlotController::class);
    /* -------------------------------------------------------
     المحلات التجارية  
    ------------------------------------------------------- */

    Route::resource('shops', ShopController::class);

    /* -------------------------------------------------------
        
    ------------------------------------------------------- */

    Route::get('/buildings/{building}/plan/pdf', [\App\Http\Controllers\BuildingController::class, 'planPdf'])
    ->name('buildings.plan.pdf');


     /* -------------------------------------------------------
        طباعة بيان الشقة
    ------------------------------------------------------- */
    Route::get('/apartments/{apartment}/invoice/pdf', [ApartmentController::class, 'invoicePdf'])
    ->name('apartments.invoice.pdf');
    
     /* -------------------------------------------------------
        طباعة بيان القطعة
    ------------------------------------------------------- */
    Route::get('/lands/{land}/invoice/pdf',[LandPlotController::class, 'invoicePdf']
     )->name('lands.invoice.pdf');

    /* -------------------------------------------------------
        طباعة بيان المحل
    ------------------------------------------------------- */
    Route::get('/shops/{shop}/invoice/pdf', [ShopController::class, 'invoicePdf'])
    ->name('shops.invoice.pdf');


    /* -------------------------------------------------------
        طباعة دفوعات المشاريع و المخطط
    ------------------------------------------------------- */

    Route::get('/buildings/{building}/plan', [BuildingController::class, 'plan'])
    ->name('buildings.plan');

    Route::get(
    '/buildings/{building}/tranches/{tranche}/payments/pdf',
    [\App\Http\Controllers\BuildingController::class, 'paymentsPdf']
   )->name('buildings.tranche.payments.pdf');


    Route::get('/projects/{project}/statement', [ProjectController::class, 'statement'])
    ->name('projects.statement');


    // 🔹 عرض شقق مشروع معيّن — هذا هو الطريق الصحيح الوحيد
    Route::get('/projects/{project}/apartments', [ApartmentController::class, 'byProject'])
        ->name('apartments.byProject');

    Route::get('/projects/{project}/lands', [LandPlotController::class, 'byProject'])
    ->name('projects.lands');
    
    Route::get('/projects/{project}/lands/plan/pdf',[LandPlotController::class, 'printPlan'])
    ->name('lands.plan.pdf');

    // الشقق
    Route::resource('apartments', ApartmentController::class);
    Route::get('/projects/{project}/buildings', [ApartmentController::class, 'getBuildings']);

    // الشركات + المشاريع + التجزئات
    Route::resource('projects', ProjectController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('users', UserController::class);

    // صفحة welcome 
    Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
    })->name('home');

    // الرئيسية
    Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
});



require __DIR__.'/settings.php';
