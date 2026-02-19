<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\PayableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleItemController;
use App\Http\Controllers\TaxSettingController;
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
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return redirect()->away(route('password.reset', ['token' => 'demo-token'], absolute: false))->with('status', 'If the email exists, reset instructions have been sent.');
    })->name('password.email');

    Route::view('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        return redirect()->away(route('login', absolute: false))->with('status', 'Password has been reset (frontend demo flow).');
    })->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/inventory-dashboard', [ProductController::class, 'index'])->name('inventory.dashboard');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::resource('categories', CategoryController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('exchange-rates', ExchangeRateController::class);
    Route::resource('products', ProductController::class);
    Route::resource('tax-settings', TaxSettingController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('sale-items', SaleItemController::class);
    Route::resource('receivables', ReceivableController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::resource('purchase-items', PurchaseItemController::class);
    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::resource('payables', PayableController::class);

    Route::middleware('adminOrManager')->group(function () {
        Route::resource('users', UserController::class);
    });
});
