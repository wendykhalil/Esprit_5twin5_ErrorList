<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use App\Models\Category;

// Home
Route::get('/', function () {
    $categories = Category::orderBy('name')->get();

    return view('frontend.home', compact('categories'));
})->name('home');

// Equipment CRUD
Route::get('/equipements', [EquipmentController::class, 'index'])
    ->name('equipments.index');

Route::get('/equipements/create', [EquipmentController::class, 'create'])
    ->name('equipments.create');

Route::post('/equipements', [EquipmentController::class, 'store'])
    ->name('equipments.store');

Route::get('/equipements/{equipment}', [EquipmentController::class, 'show'])
    ->name('equipments.show');

Route::get('/equipements/{equipment}/edit', [EquipmentController::class, 'edit'])
    ->name('equipments.edit');

Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
    ->name('equipments.update');

Route::delete('/equipements/{equipment}', [EquipmentController::class, 'destroy'])
    ->name('equipments.destroy');

// Pages
Route::get('/a-propos', function () {
    return view('frontend.about');
})->name('about');

Route::get('/comment-ca-marche', function () {
    return view('frontend.how-it-works');
})->name('how-it-works');

Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

// Auth
Route::get('/connexion', function () {
    return view('frontend.auth.login');
})->name('login');

Route::get('/inscription', function () {
    return view('frontend.auth.register');
})->name('register');

Route::get('/profil', function () {
    return view('frontend.profile');
})->name('profile');

Route::get('/mes-reservations', function () {
    return view('frontend.rentals.index');
})->name('rentals.index');

// Admin routes
require __DIR__.'/admin.php';