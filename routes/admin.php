<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\SettingsController;

Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');


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
});