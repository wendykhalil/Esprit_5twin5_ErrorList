<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\InspectionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

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

    Route::get('/equipements/create', [EquipmentController::class, 'create'])
        ->name('equipments.create');

    Route::post('/equipements/create/validate-step', [EquipmentController::class, 'validateWizardStep'])
        ->name('equipments.create.validate-step');

    Route::post('/equipements', [EquipmentController::class, 'store'])
        ->name('equipments.store');

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

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])
        ->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])
        ->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])
        ->name('reservations.store');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])
        ->name('reservations.show');
    Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])
        ->name('reservations.edit');
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])
        ->name('reservations.update');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])
        ->name('reservations.destroy');

    // Inspections
    Route::resource('inspections', InspectionController::class);


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

    // Support tickets
    Route::get('/tickets', [AdminSupportTicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/{supportTicket}', [AdminSupportTicketController::class, 'show'])
        ->name('tickets.show');

    Route::post('/tickets/{supportTicket}/replies', [AdminSupportTicketController::class, 'storeReply'])
        ->name('tickets.replies.store');

    Route::patch('/tickets/{supportTicket}', [AdminSupportTicketController::class, 'update'])
        ->name('tickets.update');

    // Service Providers
    Route::get('/service-providers', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'index'])->name('service-providers.index');
    Route::get('/service-providers/{serviceProvider}', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'show'])->name('service-providers.show');
    Route::patch('/service-providers/{serviceProvider}/approve', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'approve'])->name('service-providers.approve');
    Route::patch('/service-providers/{serviceProvider}/reject', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'reject'])->name('service-providers.reject');
});