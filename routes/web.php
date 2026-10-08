<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\ServiceRequestController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;


// --------------------------------------------------
// HOME
// --------------------------------------------------
Route::get('/', function () {
    $categories = Category::orderBy('name')->get();

    return view('frontend.home', compact('categories'));
})->name('home');


// --------------------------------------------------
// EQUIPMENT - PUBLIC ROUTES
// --------------------------------------------------

Route::get('/equipements', [EquipmentController::class, 'index'])
    ->name('equipments.index');


// --------------------------------------------------
// EQUIPMENT - AUTHENTICATED ROUTES
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    // Edit (la création se fait uniquement dans le back-office /admin/equipements/create)
    Route::get('/equipements/{equipment}/edit', [EquipmentController::class, 'edit'])
        ->whereNumber('equipment')
        ->name('equipments.edit');

    Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
        ->whereNumber('equipment')
        ->name('equipments.update');

    // Delete
    Route::delete('/equipements/{equipment}', [EquipmentController::class, 'destroy'])
        ->whereNumber('equipment')
        ->name('equipments.destroy');
});


// --------------------------------------------------
// EQUIPMENT DETAILS
// IMPORTANT: keep this after /create and /edit
// --------------------------------------------------

Route::get('/equipements/{equipment}', [EquipmentController::class, 'show'])
    ->whereNumber('equipment')
    ->name('equipments.show');


// --------------------------------------------------
// SOLARSHARE STATIC PAGES
// --------------------------------------------------

Route::get('/a-propos', function () {
    return view('frontend.about');
})->name('about');


Route::get('/comment-ca-marche', function () {
    return view('frontend.how-it-works');
})->name('how-it-works');


Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');


// --------------------------------------------------
// RENTALS
// --------------------------------------------------

Route::get('/mes-reservations', function () {
    return view('frontend.rentals.index');
})
    ->middleware('auth')
    ->name('rentals.index');


// --------------------------------------------------
// RESERVATIONS & INSPECTIONS
// --------------------------------------------------

Route::get('/reservations', [ReservationController::class, 'index'])
    ->middleware('auth')
    ->name('reservations.index');

Route::middleware('auth')->group(function () {
    Route::resource('reservations', ReservationController::class)
        ->except(['index']);
    Route::resource('inspections', InspectionController::class);
});


// --------------------------------------------------
// PAYMENTS
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    Route::get('/paiement/creer', [PaymentController::class, 'create'])
        ->name('payments.create');

    Route::post('/paiement', [PaymentController::class, 'store'])
        ->name('payments.store');

    Route::get('/paiement/{payment}', [PaymentController::class, 'show'])
        ->name('payments.show');

    Route::get('/mes-paiements', [PaymentController::class, 'history'])
        ->name('payments.history');

    Route::get('/mes-factures', [PaymentController::class, 'invoices'])
        ->name('payments.invoices');

    Route::get('/mes-paiements/{payment}/facture', [PaymentController::class, 'invoice'])
        ->name('payments.invoice');
});


// --------------------------------------------------
// BREEZE DASHBOARD
// --------------------------------------------------

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// --------------------------------------------------
// SUPPORT TICKETS (CLIENT)
// --------------------------------------------------

Route::middleware('auth')->prefix('support')->name('support.tickets.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
    Route::post('/', [SupportTicketController::class, 'store'])->name('store');
    Route::get('/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
    Route::post('/{supportTicket}/replies', [SupportTicketController::class, 'storeReply'])
        ->name('replies.store');
});

// --------------------------------------------------
// PROFILE
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::delete('/profile/photo', [ProfileController::class, 'deleteProfilePhoto'])
        ->name('profile.photo.delete');

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile');
});


// --------------------------------------------------
// TECHNICAL SERVICES: SERVICE PROVIDERS
// --------------------------------------------------

Route::get('/services', [ServiceProviderController::class, 'index'])
    ->name('service-providers.index');

Route::middleware('auth')->group(function () {
    Route::middleware('client')->group(function () {
        Route::get('/services/create', [ServiceProviderController::class, 'create'])
            ->name('service-providers.create');

        Route::post('/services', [ServiceProviderController::class, 'store'])
            ->name('service-providers.store');
    });

    Route::get('/services/{serviceProvider}/edit', [ServiceProviderController::class, 'edit'])
        ->name('service-providers.edit');

    Route::put('/services/{serviceProvider}', [ServiceProviderController::class, 'update'])
        ->name('service-providers.update');

    Route::delete('/services/{serviceProvider}', [ServiceProviderController::class, 'destroy'])
        ->name('service-providers.destroy');

    // Technical Services: Service Requests
    Route::get('/services/{serviceProvider}/request', [ServiceRequestController::class, 'create'])
        ->name('service-requests.create');
    Route::post('/services/{serviceProvider}/request', [ServiceRequestController::class, 'store'])
        ->name('service-requests.store');
    Route::get('/my-service-requests', [ServiceRequestController::class, 'index'])
        ->name('service-requests.index');
    Route::get('/my-service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
        ->name('service-requests.show');
    Route::delete('/my-service-requests/{serviceRequest}', [ServiceRequestController::class, 'destroy'])
        ->name('service-requests.destroy');

    // Technical Services: Provider Side Requests (rôle prestataire approuvé)
    Route::middleware('provider')->prefix('provider')->group(function () {
        Route::get('/service-requests', [ServiceRequestController::class, 'providerIndex'])
            ->name('provider.service-requests.index');
        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'providerShow'])
            ->name('provider.service-requests.show');
        Route::patch('/service-requests/{serviceRequest}/accept', [ServiceRequestController::class, 'accept'])
            ->name('provider.service-requests.accept');
        Route::patch('/service-requests/{serviceRequest}/reject', [ServiceRequestController::class, 'reject'])
            ->name('provider.service-requests.reject');
        Route::patch('/service-requests/{serviceRequest}/start', [ServiceRequestController::class, 'start'])
            ->name('provider.service-requests.start');
        Route::patch('/service-requests/{serviceRequest}/complete', [ServiceRequestController::class, 'complete'])
            ->name('provider.service-requests.complete');
    });
});

Route::get('/services/{serviceProvider}', [ServiceProviderController::class, 'show'])
    ->name('service-providers.show');


// --------------------------------------------------
// ADMIN ROUTES
// --------------------------------------------------

require __DIR__.'/admin.php';


// --------------------------------------------------
// BREEZE AUTHENTICATION ROUTES
// --------------------------------------------------

require __DIR__.'/auth.php';