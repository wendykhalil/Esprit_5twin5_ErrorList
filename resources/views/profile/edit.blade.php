@extends('layouts.frontend')

@section('title', 'Mon Profil - SolarShare')

@section('content')

<div class="bg-white border-b border-green-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-green-900" style="font-family: Fraunces, Georgia, serif">
            Mon Profil
        </h1>
        <p class="text-gray-600 mt-2">
            Gérez vos informations personnelles et vos préférences.
        </p>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Update Profile Form -->
        <div class="bg-white rounded-lg shadow p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form', ['user' => $user])
        </div>

        <!-- Update Password Form -->
        <div class="bg-white rounded-lg shadow p-6 sm:p-8 mt-6">
            @include('profile.partials.update-password-form')
        </div>

        <!-- Delete Account Form -->
        <div class="bg-white border border-red-200 rounded-lg shadow p-6 sm:p-8 mt-6">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</div>

@endsection
