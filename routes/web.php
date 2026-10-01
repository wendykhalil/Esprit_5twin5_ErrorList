<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ProfileController;
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

    // Create form
    Route::get('/equipements/create', [EquipmentController::class, 'create'])
        ->name('equipments.create');

    // Store new equipment
    Route::post('/equipements', [EquipmentController::class, 'store'])
        ->name('equipments.store');

    // Edit form
    Route::get('/equipements/{equipment}/edit', [EquipmentController::class, 'edit'])
        ->whereNumber('equipment')
        ->name('equipments.edit');

    // Update equipment
    Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
        ->whereNumber('equipment')
        ->name('equipments.update');

    // Delete equipment
    Route::delete('/equipements/{equipment}', [EquipmentController::class, 'destroy'])
        ->whereNumber('equipment')
        ->name('equipments.destroy');
});


// --------------------------------------------------
// EQUIPMENT DETAILS
// IMPORTANT: keep this AFTER /create and /edit
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

    // Breeze profile page
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    // Update profile
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    // Delete account
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // SolarShare custom alias
    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile');
});


// --------------------------------------------------
// ADMIN ROUTES
// --------------------------------------------------

require __DIR__.'/admin.php';


// --------------------------------------------------
// BREEZE AUTHENTICATION ROUTES
// login / register / logout / password reset...
// --------------------------------------------------

require __DIR__.'/auth.php';