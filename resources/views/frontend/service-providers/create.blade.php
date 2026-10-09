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

            @if ($errors->any())
                <div class="mx-6 mt-6 sm:mx-8 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-medium">Le formulaire contient des erreurs.</p>
                    <p class="mt-1">Vérifiez les champs indiqués ci-dessous. Les informations n'ont pas été enregistrées.</p>
                </div>
            @endif

            <form
                action="{{ route('service-providers.store') }}"
                method="POST"
                class="provider-form p-6 sm:p-8"
                novalidate
                x-data="{ submitting: false }"
                x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
            >
                @csrf

                @if ($errors->has('form') || $errors->has('status') || $errors->has('user_id') || $errors->has('role'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        @foreach (array_merge($errors->get('form'), $errors->get('status'), $errors->get('user_id'), $errors->get('role')) as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-6">
                    <div x-data="{ showError: {{ $errors->has('specialty') ? 'true' : 'false' }} }">
                        <label for="specialty" class="block text-sm font-medium text-gray-700">Spécialité <span class="text-red-700" aria-hidden="true">*</span></label>
                        <input
                            type="text"
                            name="specialty"
                            id="specialty"
                            value="{{ old('specialty') }}"
                            autocomplete="organization-title"
                            @class([
                                'mt-1 block w-full rounded-md shadow-sm sm:text-sm',
                                'border-red-600' => $errors->has('specialty'),
                                'border-gray-300' => ! $errors->has('specialty'),
                            ])
                            x-bind:aria-invalid="showError ? 'true' : 'false'"
                            @if ($errors->has('specialty')) aria-describedby="specialty-error" @endif
                            x-on:input="showError = false"
                        >
                        @error('specialty')
                            <p id="specialty-error" x-show="showError" class="mt-1 text-sm text-red-700">
                                <span class="font-semibold">Erreur.</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div x-data="{ showError: {{ $errors->has('location') ? 'true' : 'false' }} }">
                        <label for="location" class="block text-sm font-medium text-gray-700">Localisation <span class="text-red-700" aria-hidden="true">*</span></label>
                        <input
                            type="text"
                            name="location"
                            id="location"
                            value="{{ old('location') }}"
                            autocomplete="address-level2"
                            @class([
                                'mt-1 block w-full rounded-md shadow-sm sm:text-sm',
                                'border-red-600' => $errors->has('location'),
                                'border-gray-300' => ! $errors->has('location'),
                            ])
                            x-bind:aria-invalid="showError ? 'true' : 'false'"
                            @if ($errors->has('location')) aria-describedby="location-error" @endif
                            x-on:input="showError = false"
                        >
                        @error('location')
                            <p id="location-error" x-show="showError" class="mt-1 text-sm text-red-700">
                                <span class="font-semibold">Erreur.</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div x-data="{ showError: {{ $errors->has('experience_years') ? 'true' : 'false' }} }">
                            <label for="experience_years" class="block text-sm font-medium text-gray-700">Années d'expérience <span class="text-red-700" aria-hidden="true">*</span></label>
                            <input
                                type="text"
                                inputmode="numeric"
                                name="experience_years"
                                id="experience_years"
                                value="{{ old('experience_years') }}"
                                @class([
                                    'mt-1 block w-full rounded-md shadow-sm sm:text-sm',
                                    'border-red-600' => $errors->has('experience_years'),
                                    'border-gray-300' => ! $errors->has('experience_years'),
                                ])
                                x-bind:aria-invalid="showError ? 'true' : 'false'"
                                @if ($errors->has('experience_years')) aria-describedby="experience_years-error" @endif
                                x-on:input="showError = false"
                            >
                            <p class="mt-1 text-xs text-gray-500">Nombre entier de 0 à 60. Indiquez 0 si vous débutez.</p>
                            @error('experience_years')
                                <p id="experience_years-error" x-show="showError" class="mt-1 text-sm text-red-700">
                                    <span class="font-semibold">Erreur.</span> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div x-data="{ showError: {{ $errors->has('hourly_rate') ? 'true' : 'false' }} }">
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Tarif horaire (TND) <span class="text-red-700" aria-hidden="true">*</span></label>
                            <input
                                type="text"
                                inputmode="decimal"
                                name="hourly_rate"
                                id="hourly_rate"
                                value="{{ old('hourly_rate') }}"
                                @class([
                                    'mt-1 block w-full rounded-md shadow-sm sm:text-sm',
                                    'border-red-600' => $errors->has('hourly_rate'),
                                    'border-gray-300' => ! $errors->has('hourly_rate'),
                                ])
                                x-bind:aria-invalid="showError ? 'true' : 'false'"
                                @if ($errors->has('hourly_rate')) aria-describedby="hourly_rate-error" @endif
                                x-on:input="showError = false"
                            >
                            <p class="mt-1 text-xs text-gray-500">Montant positif, deux décimales au maximum.</p>
                            @error('hourly_rate')
                                <p id="hourly_rate-error" x-show="showError" class="mt-1 text-sm text-red-700">
                                    <span class="font-semibold">Erreur.</span> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div x-data="{ showError: {{ $errors->has('phone') ? 'true' : 'false' }} }">
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            Téléphone professionnel <span class="font-normal text-gray-500">(facultatif)</span>
                        </label>
                        <input
                            type="text"
                            inputmode="tel"
                            name="phone"
                            id="phone"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            placeholder="+216 98 123 456"
                            @class([
                                'mt-1 block w-full rounded-md shadow-sm sm:text-sm',
                                'border-red-600' => $errors->has('phone'),
                                'border-gray-300' => ! $errors->has('phone'),
                            ])
                            x-bind:aria-invalid="showError ? 'true' : 'false'"
                            @if ($errors->has('phone')) aria-describedby="phone-error" @endif
                            x-on:input="showError = false"
                        >
                        <p class="mt-1 text-xs text-gray-500">Numéro tunisien, par exemple 98 123 456 ou +216 98 123 456.</p>
                        @error('phone')
                            <p id="phone-error" x-show="showError" class="mt-1 text-sm text-red-700">
                                <span class="font-semibold">Erreur.</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div x-data="{ showError: {{ $errors->has('description') ? 'true' : 'false' }} }">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description détaillée <span class="text-red-700" aria-hidden="true">*</span></label>
                        <textarea
                            name="description"
                            id="description"
                            rows="5"
                            @class([
                                'mt-1 block w-full rounded-md shadow-sm sm:text-sm',
                                'border-red-600' => $errors->has('description'),
                                'border-gray-300' => ! $errors->has('description'),
                            ])
                            x-bind:aria-invalid="showError ? 'true' : 'false'"
                            @if ($errors->has('description')) aria-describedby="description-error" @endif
                            x-on:input="showError = false"
                        >{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">30 caractères minimum, 2&nbsp;000 maximum.</p>
                        @error('description')
                            <p id="description-error" x-show="showError" class="mt-1 text-sm text-red-700">
                                <span class="font-semibold">Erreur.</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input type="checkbox" name="availability" id="availability" value="1" {{ old('availability', true) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="availability" class="font-medium text-gray-700">Je suis actuellement disponible pour de nouvelles interventions</label>
                            </div>
                        </div>
                        @error('availability')
                            <p class="mt-1 text-sm text-red-700"><span class="font-semibold">Erreur.</span> {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-end gap-4">
                    <a href="{{ route('service-providers.index') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium transition"
                        x-bind:class="submitting ? 'cursor-wait opacity-70' : ''"
                        x-bind:aria-busy="submitting"
                    >
                        <span x-show="!submitting">Soumettre mon profil</span>
                        <span x-cloak x-show="submitting">Envoi en cours...</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<style>
    [x-cloak] { display: none !important; }

    .provider-form input:focus,
    .provider-form textarea:focus {
        border-color: #189C48 !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(24, 156, 72, 0.22) !important;
    }

    .provider-form input[aria-invalid="true"],
    .provider-form textarea[aria-invalid="true"] {
        border-color: #b91c1c !important;
    }

    .provider-form input[aria-invalid="true"]:focus,
    .provider-form textarea[aria-invalid="true"]:focus {
        border-color: #b91c1c !important;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.18) !important;
    }
</style>
@endsection
