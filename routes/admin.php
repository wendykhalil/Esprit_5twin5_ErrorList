<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentGuideController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\InspectionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\ReclamationController;
use App\Http\Controllers\Admin\CompensationCodeController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\EquipmentGuideAIController;

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

    Route::get('/equipements/{equipment}', [EquipmentController::class, 'show'])
        ->whereNumber('equipment')
        ->name('equipments.show');

    Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
        ->whereNumber('equipment')
        ->name('equipments.update');

    Route::delete('/equipements/{equipment}', [EquipmentController::class, 'destroy'])
        ->whereNumber('equipment')
        ->name('equipments.destroy');


    // --------------------------------------------------
    // EQUIPMENT USAGE GUIDES - ADMIN ONLY
    // --------------------------------------------------

Route::post(
    '/equipment-guides/ai/generate',
    [EquipmentGuideAIController::class, 'generate']
)->name('equipment-guides.ai.generate');

    Route::resource(
        'equipment-guides',
        EquipmentGuideController::class
    )->whereNumber('equipment_guide');


    // --------------------------------------------------
    // USERS
    // --------------------------------------------------

    Route::get('/utilisateurs', [UserController::class, 'index'])
        ->name('users');

    Route::get('/utilisateurs/{user}', [UserController::class, 'show'])
        ->whereNumber('user')
        ->name('users.show');

    Route::get('/utilisateurs/{user}/edit', [UserController::class, 'edit'])
        ->whereNumber('user')
        ->name('users.edit');

    Route::put('/utilisateurs/{user}', [UserController::class, 'update'])
        ->whereNumber('user')
        ->name('users.update');

    Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])
        ->whereNumber('user')
        ->name('users.destroy');


    // Rentals
    Route::get('/locations', [RentalController::class, 'index'])
        ->name('rentals');


    // --------------------------------------------------
    // RESERVATIONS
    // --------------------------------------------------

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

    Route::patch('/reservations/{reservation}/approve', [ReservationController::class, 'approve'])
        ->name('reservations.approve');

    Route::patch('/reservations/{reservation}/reject', [ReservationController::class, 'reject'])
        ->name('reservations.reject');

    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])
        ->name('reservations.destroy');


    // Inspections
    Route::resource('inspections', InspectionController::class);


    // Settings
    Route::get('/parametres', [SettingsController::class, 'index'])
        ->name('settings');


    // --------------------------------------------------
    // PAYMENTS
    // --------------------------------------------------

    Route::resource('payments', PaymentController::class);

    Route::get('/payments/{payment}/refund', [PaymentController::class, 'refundCreate'])
        ->name('payments.refund.create');

    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refundStore'])
        ->name('payments.refund.store');


    // --------------------------------------------------
    // CONTRACTS
    // --------------------------------------------------

    Route::get('/contracts', [ContractController::class, 'index'])
        ->name('contracts.index');

    Route::get('/contracts/{contract}', [ContractController::class, 'show'])
        ->name('contracts.show');

    Route::patch('/contracts/{contract}/status', [ContractController::class, 'updateStatus'])
        ->name('contracts.updateStatus');

    Route::get('/contracts/{contract}/print', [ContractController::class, 'print'])
        ->name('contracts.print');


    // --------------------------------------------------
    // DELIVERIES
    // --------------------------------------------------

    Route::get('/livraisons', [DeliveryController::class, 'index'])
        ->name('deliveries.index');

    Route::get('/livraisons/{delivery}', [DeliveryController::class, 'show'])
        ->name('deliveries.show');

    Route::patch('/livraisons/{delivery}/status', [DeliveryController::class, 'updateStatus'])
        ->name('deliveries.updateStatus');


    // Transactions
    Route::resource('transactions', TransactionController::class);


    // --------------------------------------------------
    // SUPPORT TICKETS
    // --------------------------------------------------

    Route::get('/tickets', [AdminSupportTicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/{supportTicket}', [AdminSupportTicketController::class, 'show'])
        ->name('tickets.show');

    Route::post('/tickets/{supportTicket}/replies', [AdminSupportTicketController::class, 'storeReply'])
        ->name('tickets.replies.store');

    Route::patch('/tickets/{supportTicket}', [AdminSupportTicketController::class, 'update'])
        ->name('tickets.update');


    // --------------------------------------------------
    // SERVICE PROVIDERS
    // --------------------------------------------------

    Route::get('/service-providers', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'index'])
        ->name('service-providers.index');

    Route::get('/service-providers/{serviceProvider}', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'show'])
        ->name('service-providers.show');

    Route::patch('/service-providers/{serviceProvider}/approve', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'approve'])
        ->name('service-providers.approve');

    Route::patch('/service-providers/{serviceProvider}/reject', [\App\Http\Controllers\Admin\ServiceProviderController::class, 'reject'])
        ->name('service-providers.reject');


    // --------------------------------------------------
    // RECLAMATIONS - ADMIN
    // --------------------------------------------------

    Route::get('/reclamations', [ReclamationController::class, 'index'])
        ->name('reclamations.index');

    Route::get('/reclamations/{reclamation}', [ReclamationController::class, 'show'])
        ->name('reclamations.show');

    Route::get('/reclamations/{reclamation}/approve', [ReclamationController::class, 'approveForm'])
        ->name('reclamations.approve.form');

    Route::post('/reclamations/{reclamation}/approve', [ReclamationController::class, 'approve'])
        ->name('reclamations.approve');

    Route::get('/reclamations/{reclamation}/reject', [ReclamationController::class, 'rejectForm'])
        ->name('reclamations.reject.form');

    Route::post('/reclamations/{reclamation}/reject', [ReclamationController::class, 'reject'])
        ->name('reclamations.reject');


    // --------------------------------------------------
    // COMPENSATION CODES - ADMIN
    // --------------------------------------------------

    Route::get('/codes-compensation', [CompensationCodeController::class, 'index'])
        ->name('compensation-codes.index');

    Route::get('/codes-compensation/create', [CompensationCodeController::class, 'create'])
        ->name('compensation-codes.create');

    Route::post('/codes-compensation', [CompensationCodeController::class, 'store'])
        ->name('compensation-codes.store');

    Route::get('/codes-compensation/{compensationCode}', [CompensationCodeController::class, 'show'])
        ->name('compensation-codes.show');

    Route::get('/codes-compensation/{compensationCode}/edit', [CompensationCodeController::class, 'edit'])
        ->name('compensation-codes.edit');

    Route::put('/codes-compensation/{compensationCode}', [CompensationCodeController::class, 'update'])
        ->name('compensation-codes.update');

    Route::delete('/codes-compensation/{compensationCode}', [CompensationCodeController::class, 'destroy'])
        ->name('compensation-codes.destroy');

    Route::patch('/codes-compensation/{compensationCode}/toggle-active', [CompensationCodeController::class, 'toggleActive'])
        ->name('compensation-codes.toggle-active');

});
