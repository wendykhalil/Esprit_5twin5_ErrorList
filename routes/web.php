<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\PaymentController;

use App\Models\Category;


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

    // Create
    Route::get('/equipements/create', [EquipmentController::class, 'create'])
        ->name('equipments.create');

    Route::post('/equipements', [EquipmentController::class, 'store'])
        ->name('equipments.store');

    // Edit
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
// PROFILE
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile');
});


// --------------------------------------------------
// ADMIN ROUTES
// --------------------------------------------------

require __DIR__.'/admin.php';


// --------------------------------------------------
// BREEZE AUTHENTICATION ROUTES
// --------------------------------------------------

require __DIR__.'/auth.php';