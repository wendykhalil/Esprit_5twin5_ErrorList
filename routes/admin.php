<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TransactionController;

Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
        ->name('dashboard.stats');


    // --------------------------------------------------
    // EQUIPMENTS
    // --------------------------------------------------

    Route::get('/equipements', [EquipmentController::class, 'index'])
        ->name('equipments');

    Route::get('/equipements/{equipment}/edit', [EquipmentController::class, 'edit'])
        ->whereNumber('equipment')
        ->name('equipments.edit');

    Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
        ->whereNumber('equipment')
        ->name('equipments.update');

    Route::delete('/equipements/{equipment}', [EquipmentController::class, 'destroy'])
        ->whereNumber('equipment')
        ->name('equipments.destroy');


    // Users
    Route::get('/utilisateurs', [UserController::class, 'index'])
        ->name('users');


    // Rentals
    Route::get('/locations', [RentalController::class, 'index'])
        ->name('rentals');


    // Settings
    Route::get('/parametres', [SettingsController::class, 'index'])
        ->name('settings');


    // Payments
    Route::resource('payments', PaymentController::class);

    // Refund routes
    Route::get('/payments/{payment}/refund', [PaymentController::class, 'refundCreate'])
        ->name('payments.refund.create');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refundStore'])
        ->name('payments.refund.store');


    // Transactions
    Route::resource('transactions', TransactionController::class);

    // Service Providers
    Route::get('/service-providers', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'index'])->name('service-providers.index');
    Route::get('/service-providers/{serviceProvider}', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'show'])->name('service-providers.show');
    Route::patch('/service-providers/{serviceProvider}/approve', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'approve'])->name('service-providers.approve');
    Route::patch('/service-providers/{serviceProvider}/reject', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'reject'])->name('service-providers.reject');
});