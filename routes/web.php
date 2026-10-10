<?php

use App\Http\Controllers\ActivityLogController;
// Auth & Google Controllers
use App\Http\Controllers\Admin\WithdrawalApprovalController;
// Core & Tenant Management Controllers
use App\Http\Controllers\AiReportController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\CashOperationController;
use App\Http\Controllers\CategoryController;
// Operational POS & Catalog Controllers
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GettingStartedController;
use App\Http\Controllers\InventoryController;
// Inventory, Materials & Suppliers Controllers
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MenuConfigurationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentCallbackController;
// CRM, Marketing & Employee Controllers
use App\Http\Controllers\PaymentReviewController;
use App\Http\Controllers\PosApiController;
use App\Http\Controllers\PosController;
// Finance, Reports, Settings & AI Controllers
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\StocktakeController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceJobController;
// Admin Controllers
use App\Http\Controllers\ServiceOrderController;
// Middlewares
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TenantController;
use App\Http\Middleware\EnsureSubscriptionIsActive;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC & WEBHOOK ROUTES (Bebas dari Auth & CSRF)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $plans = Plan::where('is_active', true)->where('is_public', true)->get();

    return view('landing', compact('plans'));
})->name('landing');

// Webhook Callback dari Midtrans
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handleNotification'])
    ->name('midtrans.callback');

/*
|--------------------------------------------------------------------------
| 2. GUEST ROUTES (Google OAuth)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

/*
|--------------------------------------------------------------------------
| 3. AUTHENTICATED & EMAIL VERIFIED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::middleware('can:platform-admin')->group(function () {
        Route::get('/withdrawals', [WithdrawalApprovalController::class, 'index'])->name('admin.withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approve'])->name('admin.withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalApprovalController::class, 'reject'])->name('admin.withdrawals.reject');
    });

    // ==========================================
    // A. SETUP TENANT & PROFILE MANAGEMENT
    // (Bisa diakses walau belum punya tenant_id aktif)
    // ==========================================
    Route::get('/setup-business', [TenantController::class, 'create'])->middleware('admin')->name('tenants.create');
    Route::post('/setup-business', [TenantController::class, 'store'])->middleware('admin')->name('tenants.store');

    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index')->middleware('admin');
    Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit')->middleware('admin');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update')->middleware('admin');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy')->middleware('admin');

    Route::middleware(['check.tenant', 'admin'])->group(function () {
        Route::get('/business', [BusinessProfileController::class, 'edit'])->name('business.edit');
        Route::put('/business', [BusinessProfileController::class, 'update'])->name('business.update');
    });

    Route::get('/getting-started', [GettingStartedController::class, 'index'])->middleware(['check.tenant', 'admin'])->name('getting-started');

    // Switch Tenant Action
    Route::post('/tenants/switch/{tenant}', function ($tenantId) {
        $tenant = auth()->user()->tenants()->findOrFail($tenantId);
        auth()->user()->update(['tenant_id' => $tenant->id]);

        return back()->with('status', 'Berhasil pindah ke bisnis: '.$tenant->name);
    })->middleware('admin')->name('tenants.switch');

    // User Profile Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API Check Status Verifikasi (Auto-Polling Client)
    Route::get('/api/check-email-verification', function () {
        return response()->json([
            'verified' => auth()->user()->hasVerifiedEmail(),
            'has_tenant' => ! is_null(auth()->user()->tenant_id),
            'redirect_url' => auth()->user()->tenant_id ? route('dashboard') : route('tenants.create'),
        ]);
    })->name('api.check-verification');

    // ==========================================
    // B. BILLING & SAAS SUBSCRIPTION MODULE
    // (Bisa diakses walau paket langganan mati/habis)
    // ==========================================
    Route::prefix('billing')->name('billing.')->middleware('admin')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/invoices/{invoice}', [SubscriptionController::class, 'showInvoice'])->name('invoice');
        Route::get('/invoices/{invoice}/check-status', [SubscriptionController::class, 'checkStatus'])->name('check-status');
        Route::post('/invoice/{invoice}/cancel', [SubscriptionController::class, 'cancelInvoice'])->name('invoice.cancel');
    });

    // ==========================================
    // C. CORE OPERATIONAL ROUTES (TERPROTEKSI KETAT)
    // Wajib: (1) Memiliki Tenant ID & (2) Langganan Aktif
    // ==========================================
    // ==========================================
    // C. CORE OPERATIONAL ROUTES (TERPROTEKSI KETAT)
    // Wajib: (1) Memiliki Tenant ID & (2) Langganan Aktif
    // ==========================================
    Route::middleware(['check.tenant', EnsureSubscriptionIsActive::class])->group(function () {

        Route::view('/help', 'help.index')->name('help');
        Route::get('/services', [ServiceJobController::class, 'index'])->name('services.index');
        Route::patch('/services/{order}', [ServiceJobController::class, 'update'])->name('services.update');
        Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::patch('/kitchen/{order}', [KitchenController::class, 'update'])->name('kitchen.update');
        Route::middleware('admin')->group(function () {
            Route::post('/getting-started/sample', [GettingStartedController::class, 'sample'])->name('getting-started.sample');
            Route::post('/getting-started/import', [GettingStartedController::class, 'import'])->name('getting-started.import');
            Route::get('/menu/template', [GettingStartedController::class, 'template'])->name('menu.template');
            Route::get('/menu/export', [GettingStartedController::class, 'export'])->name('menu.export');
        });
        Route::middleware('manager')->group(function () {
            Route::get('/menu/configure', [MenuConfigurationController::class, 'index'])->name('menu.configure');
            Route::post('/menu/{product}/configure', [MenuConfigurationController::class, 'store'])->name('menu.configure.store');
            Route::delete('/menu/{product}/configure', [MenuConfigurationController::class, 'destroy'])->name('menu.configure.destroy');
        });

        // --- DASHBOARD UTAMA ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- ADMIN ONLY ROUTES ---
        Route::middleware(['admin'])->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
            Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        });

        // --- MASTER DATA: PRODUK, KATEGORI & VARIAN ---
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy'])->middleware('manager');
        Route::get('/products/{product}/label', [ProductController::class, 'label'])->middleware('manager')->name('products.label');
        Route::resource('products', ProductController::class)->except(['show'])->middleware('manager');
        Route::resource('products.variants', ProductVariantController::class)->only(['store', 'update', 'destroy'])->middleware('manager');

        // --- MASTER DATA: BAHAN BAKU & INVENTORI ---
        Route::resource('materials', MaterialController::class)->only(['index', 'store'])->middleware('manager');
        Route::post('/materials/update-stock', [MaterialController::class, 'updateStock'])->middleware('manager')->name('materials.update-stock');
        Route::get('/materials/{id}/history', [MaterialController::class, 'getHistory'])->middleware('manager')->name('materials.history-json');

        // --- MASTER DATA: SUPPLIER & PEMASOK ---
        Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('manager')->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('manager')->name('suppliers.store');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('manager')->name('suppliers.destroy');

        // --- INVENTORY ADJUSTMENT & MUTASI STOK ---
        Route::get('/inventory', [InventoryController::class, 'index'])->middleware('manager')->name('inventory.index');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->middleware('manager')->name('inventory.adjust');
        Route::get('/inventory/history', [InventoryController::class, 'history'])->middleware('manager')->name('inventory.history');
        Route::resource('stock-movements', StockMovementController::class)->only(['index'])->middleware('manager');
        Route::middleware('manager')->group(function () {
            Route::get('/purchases', [PurchaseOrderController::class, 'index'])->name('purchases.index');
            Route::get('/purchases/create', [PurchaseOrderController::class, 'create'])->name('purchases.create');
            Route::post('/purchases', [PurchaseOrderController::class, 'store'])->name('purchases.store');
            Route::get('/purchases/{purchase}', [PurchaseOrderController::class, 'show'])->name('purchases.show');
            Route::post('/purchases/{purchase}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');
            Route::get('/stocktakes', [StocktakeController::class, 'index'])->name('stocktakes.index');
            Route::post('/stocktakes', [StocktakeController::class, 'store'])->name('stocktakes.store');
            Route::get('/stocktakes/{stocktake}', [StocktakeController::class, 'show'])->name('stocktakes.show');
            Route::post('/stocktakes/{stocktake}/complete', [StocktakeController::class, 'complete'])->name('stocktakes.complete');
        });

        // --- CRM & CUSTOMERS (TERPROTEKSI: Khusus Growth & Scale) ---
        Route::middleware('can:feature-crm')->group(function () {
            Route::resource('customers', CustomerController::class)->only(['index', 'store', 'show', 'destroy']);
            Route::post('/customers/api', [CustomerController::class, 'storeApi'])->name('customers.storeApi');
        });

        // --- DISCOUNTS & PROMOS ---
        Route::resource('discounts', DiscountController::class)->middleware('manager');

        // --- KASIR & POS TERMINAL ---
        Route::resource('pos', PosController::class)->only(['index', 'store']);
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::resource('service-orders', ServiceOrderController::class)->only(['index', 'store', 'show', 'update', 'destroy'])->middleware('admin');
        Route::get('/orders/{id}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::get('/orders/{id}/check-status', [PosController::class, 'checkStatus'])->name('orders.checkStatus');
        Route::get('/pos/check-payment-status/{order}', [PosController::class, 'checkStatus'])->name('pos.check-payment');

        Route::get('/payments/review', [PaymentReviewController::class, 'index'])->middleware('admin')->name('payments.review');
        Route::post('/payments/review/{order}', [PaymentReviewController::class, 'check'])->middleware('admin')->name('payments.review.check');
        Route::post('/orders/{order}/returns', [CashOperationController::class, 'refund'])->middleware('admin')->name('orders.returns');
        Route::post('/orders/{order}/payments', [CashOperationController::class, 'payment'])->name('orders.payments');
        Route::post('/orders/{order}/cancel', [CashOperationController::class, 'cancel'])->middleware('admin')->name('orders.cancel');
        Route::get('/cash', [CashOperationController::class, 'index'])->middleware('admin')->name('cash.index');
        Route::post('/cash/expenses', [CashOperationController::class, 'expense'])->middleware('admin')->name('cash.expenses');

        // --- AUDIT SHIFT KASIR ---
        Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
        Route::get('/shifts/summary', [ShiftController::class, 'summary'])->name('shifts.summary');
        Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');
        Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
        Route::resource('shifts', ShiftController::class)->only(['index']);

        // --- KEUANGAN & DOMPET TOKO ---
        Route::get('/finance', [FinanceController::class, 'index'])->middleware('can:manage-finance')->name('finance.index');
        Route::post('/finance/withdraw', [FinanceController::class, 'withdraw'])->middleware('can:manage-finance')->name('finance.withdraw');
        Route::post('/finance/settings', [FinanceController::class, 'updateSettings'])->middleware('can:manage-finance')->name('finance.settings');

        // --- LAPORAN OPERASIONAL & EXPORT ---
        Route::get('/reports', [ReportController::class, 'index'])->middleware('manager')->name('reports.index');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->middleware('manager')->name('reports.export-excel');
        Route::get('/reports/exports', [ReportController::class, 'exportList'])->middleware('manager')->name('reports.exports-list');
        Route::get('/reports/exports/download/{id}', [ReportController::class, 'downloadFile'])->middleware('manager')->name('reports.download-file');
        Route::get('/reports/exports/status-json', [ReportController::class, 'getExportsStatusJson'])->middleware('manager')->name('reports.exports-status-json');

        // --- AI ADVISOR ENGINE & CHAT REAL-TIME (TERPROTEKSI: Khusus Scale) ---
        Route::middleware(['can:feature-ai-analytics', 'can:access-backoffice'])->group(function () {
            Route::get('/reports/ai-analysis', [AiReportController::class, 'index'])->name('reports.ai');
            Route::post('/reports/ai-chat', [AiReportController::class, 'chat'])->name('reports.ai-chat');
            Route::get('/api/pos/recommendation', [PosApiController::class, 'getRecommendation'])->name('api.pos.recommendation');
        });

        // --- AUDIT TRAIL ACTIVITY LOG ---
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('admin')->name('activity-logs.index');

        // --- SISTEM PENGATURAN ---
        Route::resource('settings', SettingController::class)->only(['index', 'store'])->middleware('admin');
        Route::post('/settings/update-points', [SettingController::class, 'updatePoints'])->middleware('admin')->name('settings.update-points');
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Breeze/Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
