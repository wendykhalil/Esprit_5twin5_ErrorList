@extends('layouts.frontend')

@section('title', 'Gérer mon profil - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-5 py-4 rounded-xl">
                {{ session('info') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-6 border-b border-gray-100 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900" style="font-family: Fraunces, Georgia, serif">
                    Gérer mon profil
                </h1>
                
                {{-- Status Badge --}}
                @if($serviceProvider->status === 'approved')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Approuvé
                    </span>
                @elseif($serviceProvider->status === 'pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        En attente
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        Rejeté
                    </span>
                @endif
            </div>

            <form action="{{ route('service-providers.update', $serviceProvider) }}" method="POST" class="p-6 sm:p-8" novalidate>
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="specialty" class="block text-sm font-medium text-gray-700">Spécialité <span class="text-red-500">*</span></label>
                        <input type="text" name="specialty" id="specialty" value="{{ old('specialty', $serviceProvider->specialty) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        @error('specialty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Localisation <span class="text-red-500">*</span></label>
                        <input type="text" name="location" id="location" value="{{ old('location', $serviceProvider->location) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="experience_years" class="block text-sm font-medium text-gray-700">Années d'expérience <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="numeric" name="experience_years" id="experience_years" value="{{ old('experience_years', $serviceProvider->experience_years) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            @error('experience_years')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Tarif horaire (TND) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $serviceProvider->hourly_rate) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            @error('hourly_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone professionnel <span class="font-normal text-gray-500">(facultatif)</span></label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $serviceProvider->phone) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description détaillée <span class="text-red-500">*</span></label>
                        <textarea name="description" id="description" rows="5"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">{{ old('description', $serviceProvider->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input type="checkbox" name="availability" id="availability" value="1" {{ old('availability', $serviceProvider->availability) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="availability" class="font-medium text-gray-700">Je suis actuellement disponible pour de nouvelles interventions</label>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-end gap-4">
                    <a href="{{ route('service-providers.index') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                        Retour
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-red-50 rounded-2xl border border-red-100 p-6 sm:p-8">
            <h2 class="text-lg font-bold text-red-900 mb-2">Zone de danger</h2>
            <p class="text-red-700 text-sm mb-6">La suppression de votre profil professionnel est irréversible. Vous n'apparaîtrez plus dans l'annuaire des professionnels.</p>
            
            <form action="{{ route('service-providers.destroy', $serviceProvider) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre profil ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-medium text-sm transition">
                    Supprimer mon profil
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
