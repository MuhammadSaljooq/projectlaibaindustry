<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InternationalPayableController;
use App\Http\Controllers\InternationalPurchaseController;
use App\Http\Controllers\PayableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
    ? redirect()->away(route('dashboard', absolute: false))
    : redirect()->away(route('login', absolute: false));
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => ['required', 'email']]);

        return redirect()->away(route('password.reset', ['token' => 'demo-token'], absolute: false))
            ->with('status', 'If the email exists, reset instructions have been sent.');
    })->name('password.email');
    Route::view('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        return redirect()->away(route('login', absolute: false))
            ->with('status', 'Password has been reset.');
    })->name('password.update');
});

Route::middleware(['auth', 'blockViewerWrite'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inventory-dashboard', [ProductController::class, 'index'])->name('inventory.dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::resource('products', ProductController::class);
    Route::resource('sales', SaleController::class);
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::get('quotations/{quotation}/preview', [QuotationController::class, 'preview'])->name('quotations.preview');
    Route::resource('quotations', QuotationController::class);
    Route::get('receivables/group/{groupKey}', [ReceivableController::class, 'showGroup'])->name('receivables.group');
    Route::post('receivables/group/{groupKey}/payments', [ReceivableController::class, 'storeGroupPayment'])->name('receivables.group.payments.store');
    Route::get('receivables/group/{groupKey}/payments/{receivableGroupPayment}/edit', [ReceivableController::class, 'editGroupPayment'])->name('receivables.group.payments.edit');
    Route::patch('receivables/group/{groupKey}/payments/{receivableGroupPayment}', [ReceivableController::class, 'updateGroupPayment'])->name('receivables.group.payments.update');
    Route::delete('receivables/group/{groupKey}/payments/{receivableGroupPayment}', [ReceivableController::class, 'destroyGroupPayment'])->name('receivables.group.payments.destroy');
    Route::patch('receivables/{receivable}/payments/{customerLedgerEntry}', [ReceivableController::class, 'updatePayment'])->name('receivables.payments.update');
    Route::delete('receivables/{receivable}/payments/{customerLedgerEntry}', [ReceivableController::class, 'destroyPayment'])->name('receivables.payments.destroy');
    Route::put('receivables/{receivable}/adjust-received', [ReceivableController::class, 'adjustReceivedWithoutLedger'])->name('receivables.adjust-received');
    Route::resource('receivables', ReceivableController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::get('suppliers/{supplier}/ledger', [SupplierController::class, 'ledger'])->name('suppliers.ledger');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('international-purchases', InternationalPurchaseController::class);
    Route::get('/international-payables', [InternationalPayableController::class, 'index'])->name('international-payables.index');
    Route::get('/international-payables/{international_purchase}/pay', [InternationalPayableController::class, 'pay'])->name('international-payables.pay');
    Route::post('/international-payables/{international_purchase}/pay', [InternationalPayableController::class, 'storePayment'])->name('international-payables.pay.store');
    Route::patch('/international-payables/{international_purchase}/payments/{internationalPayablePayment}', [InternationalPayableController::class, 'updatePayment'])->name('international-payables.payments.update');
    Route::delete('/international-payables/{international_purchase}/payments/{internationalPayablePayment}', [InternationalPayableController::class, 'destroyPayment'])->name('international-payables.payments.destroy');
    Route::delete('customers/{customer}/ledger-entries/{customerLedgerEntry}', [CustomerController::class, 'destroyLedgerEntry'])->name('customers.ledger-entries.destroy');
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('customers/{customer}/statement/pdf', [CustomerController::class, 'statementPdf'])->name('customers.statement.pdf');
    Route::post('customers/{customer}/statement/email', [CustomerController::class, 'emailStatement'])->name('customers.statement.email');
    Route::resource('payables', PayableController::class);
    Route::resource('expenses', ExpenseController::class);

    Route::get('bank-statement', [BankStatementController::class, 'index'])->name('bank-statement.index');
    Route::post('bank-statement', [BankStatementController::class, 'store'])->name('bank-statement.store');
    Route::get('bank-statement/{bank_statement_entry}/edit', [BankStatementController::class, 'edit'])->name('bank-statement.edit');
    Route::put('bank-statement/{bank_statement_entry}', [BankStatementController::class, 'update'])->name('bank-statement.update');
    Route::delete('bank-statement/{bank_statement_entry}', [BankStatementController::class, 'destroy'])->name('bank-statement.destroy');

    Route::get('/vat', [VatController::class, 'index'])->name('vat.index');
    Route::get('/vat/export', [VatController::class, 'export'])->name('vat.export');
    Route::delete('/vat/{vat_entry}', [VatController::class, 'destroy'])->name('vat.destroy');

    Route::get('/sales/export/csv', [\App\Http\Controllers\SaleController::class, 'export'])->name('sales.export');
    Route::get('/purchases/export/csv', [\App\Http\Controllers\PurchaseController::class, 'export'])->name('purchases.export');
    Route::get('/expenses/export/csv', [\App\Http\Controllers\ExpenseController::class, 'export'])->name('expenses.export');
    Route::get('/international-purchases/export/csv', [InternationalPurchaseController::class, 'export'])->name('international-purchases.export');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::middleware('adminOrManager')->group(function () {
        Route::resource('users', UserController::class);
    });
});
