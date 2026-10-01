@extends('layouts.frontend')

@section('title', 'Paiement - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <a href="{{ route('equipments.index') }}" class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 mb-8">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Retour aux équipements
            </a>

            <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-8">
                <h1 class="text-3xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">Paiement de location</h1>
                <p class="text-gray-600 mb-8">Veuillez remplir le formulaire ci-dessous pour confirmer votre paiement.</p>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-red-700 mb-2">Erreurs détectées :</p>
                        <ul class="text-sm text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('payments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Amount Display --}}
                    <div class="bg-green-50 border border-green-100 rounded-xl p-6">
                        <p class="text-sm text-green-600 font-semibold uppercase mb-2 tracking-wide">Montant à payer</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-bold text-green-700">{{ number_format($amount, 2, ',', ' ') }}</span>
                            <span class="text-xl text-green-600">TND</span>
                        </div>
                    </div>

                    {{-- Hidden amount field --}}
                    <input type="hidden" name="amount" value="{{ $amount }}">

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-3">Description de la location</label>
                        
                        @php
                            $descriptionBorderClass = $errors->has('description') ? 'border-red-300' : 'border-gray-200';
                        @endphp
                        
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent {{ $descriptionBorderClass }}"
                            placeholder="Ex: Location de panneau solaire 200W du 25 au 30 septembre"
                        >{{ old('description', $description) }}</textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-4">Méthode de paiement</label>
                        
                        <div class="space-y-3">
                            {{-- Card --}}
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-green-50 transition-colors {{ old('method') === 'card' ? 'bg-green-50 border-green-300' : '' }}">
                                <input
                                    type="radio"
                                    name="method"
                                    value="card"
                                    class="w-4 h-4 text-green-600 focus:ring-green-500"
                                    {{ old('method') === 'card' || !old('method') ? 'checked' : '' }}
                                >
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-semibold text-gray-900">Carte bancaire</p>
                                    <p class="text-xs text-gray-500">Visa, Mastercard, ou autre carte bancaire</p>
                                </div>
                                <span class="text-2xl">💳</span>
                            </label>

                            {{-- Bank Transfer --}}
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-green-50 transition-colors {{ old('method') === 'bank_transfer' ? 'bg-green-50 border-green-300' : '' }}">
                                <input
                                    type="radio"
                                    name="method"
                                    value="bank_transfer"
                                    class="w-4 h-4 text-green-600 focus:ring-green-500"
                                    {{ old('method') === 'bank_transfer' ? 'checked' : '' }}
                                >
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-semibold text-gray-900">Virement bancaire</p>
                                    <p class="text-xs text-gray-500">Transfert direct vers notre compte</p>
                                </div>
                                <span class="text-2xl">🏦</span>
                            </label>

                            {{-- Cash --}}
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-green-50 transition-colors {{ old('method') === 'cash' ? 'bg-green-50 border-green-300' : '' }}">
                                <input
                                    type="radio"
                                    name="method"
                                    value="cash"
                                    class="w-4 h-4 text-green-600 focus:ring-green-500"
                                    {{ old('method') === 'cash' ? 'checked' : '' }}
                                >
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-semibold text-gray-900">Paiement en espèces</p>
                                    <p class="text-xs text-gray-500">Paiement direct lors de la location</p>
                                </div>
                                <span class="text-2xl">💵</span>
                            </label>
                        </div>

                        @error('method')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Paiement sécurisé</p>
                                <p>Vos données sont protégées. Votre paiement est confirmé et vous pouvez consulter les détails de votre transaction ci-dessous.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-4 pt-4 border-t border-gray-100">
                        <a
                            href="{{ route('equipments.index') }}"
                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors text-center"
                        >
                            Annuler
                        </a>
                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
                        >
                            Confirmer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
