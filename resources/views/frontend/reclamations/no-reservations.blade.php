@extends('layouts.frontend')

@section('title', 'Aucune Réclamation Possible')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Error Card -->
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Heading -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Aucune Réservation Éligible</h1>

            <!-- Message -->
            <p class="text-gray-600 mb-6">
                Vous n'avez actuellement aucune réservation qui peut faire l'objet d'une réclamation.
                <br>
                <span class="text-sm text-gray-500">Les réclamations peuvent être créées pour les réservations confirmées, en cours ou terminées.</span>
            </p>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-left">
                <h3 class="font-semibold text-blue-900 mb-2">Comment Créer une Réclamation :</h3>
                <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                    <li>Effectuez une réservation sur la plateforme</li>
                    <li>Votre réservation doit être confirmée ou en cours</li>
                    <li>Signalez tout problème avec l'équipement via le formulaire de réclamation</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 justify-center">
                <a href="{{ route('rentals.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                    Voir mes Réservations
                </a>
                <a href="{{ route('equipments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-6 rounded-lg">
                    Découvrir les Équipements
                </a>
            </div>

            <!-- Back Link -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Retour au Tableau de Bord
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
