@extends('layouts.frontend')

@section('title', 'Devenir prestataire - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-8 border-b border-gray-100 bg-green-50/50">
                <h1 class="text-2xl font-bold text-gray-900" style="font-family: Fraunces, Georgia, serif">
                    Devenir prestataire
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Complétez votre profil pour proposer vos services. Votre profil sera soumis à validation par un administrateur avant d'être publié.
                </p>
            </div>

            <form action="{{ route('service-providers.store') }}" method="POST" class="p-6 sm:p-8">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="specialty" class="block text-sm font-medium text-gray-700">Spécialité <span class="text-red-500">*</span></label>
                        <input type="text" name="specialty" id="specialty" value="{{ old('specialty') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        @error('specialty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Localisation <span class="text-red-500">*</span></label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="experience_years" class="block text-sm font-medium text-gray-700">Années d'expérience <span class="text-red-500">*</span></label>
                            <input type="number" name="experience_years" id="experience_years" value="{{ old('experience_years') }}" min="0" max="100" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            @error('experience_years')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Tarif horaire (TND) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate') }}" min="0" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            @error('hourly_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone professionnel</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description détaillée <span class="text-red-500">*</span></label>
                        <textarea name="description" id="description" rows="5" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input type="checkbox" name="availability" id="availability" value="1" {{ old('availability', true) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="availability" class="font-medium text-gray-700">Je suis actuellement disponible pour de nouvelles interventions</label>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-end gap-4">
                    <a href="{{ route('service-providers.index') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                        Annuler
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                        Soumettre mon profil
                    </button>
                </div>
            </form>
        </div>
        
    </div>
</div>
@endsection
