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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Equipments
    Route::get('/equipements', [EquipmentController::class, 'index'])->name('equipments');
    
    // Users
    Route::get('/utilisateurs', [UserController::class, 'index'])->name('users');
    
    // Rentals
    Route::get('/locations', [RentalController::class, 'index'])->name('rentals');
    
    // Settings
    Route::get('/parametres', [SettingsController::class, 'index'])->name('settings');

    // Payments
    Route::resource('payments', PaymentController::class);

    // Transactions
    Route::resource('transactions', TransactionController::class);
});
