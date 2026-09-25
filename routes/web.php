<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;

// Home
Route::get('/', function () {
    $categories = \App\Data\EquipmentData::getCategories();
    return view('frontend.home', compact('categories'));
})->name('home');

// Equipment
Route::get('/equipements', [EquipmentController::class, 'index'])->name('equipments.index');
Route::get('/equipements/create', function () {
    return view('frontend.equipments.create');
})->name('equipments.create');
Route::get('/equipements/{id}', [EquipmentController::class, 'show'])->name('equipments.show');

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