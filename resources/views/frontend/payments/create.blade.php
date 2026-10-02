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

                <form action="{{ route('payments.store') }}" method="POST" class="space-y-6" id="paymentForm">
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
                        
                        <div class="space-y-3" id="methodsContainer">
                            {{-- Card --}}
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-green-50 transition-colors {{ old('method') === 'card' || !old('method') ? 'bg-green-50 border-green-300' : '' }}">
                                <input
                                    type="radio"
                                    name="method"
                                    value="card"
                                    class="w-4 h-4 text-green-600 focus:ring-green-500 paymentMethod"
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
                                    class="w-4 h-4 text-green-600 focus:ring-green-500 paymentMethod"
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
                                    class="w-4 h-4 text-green-600 focus:ring-green-500 paymentMethod"
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

                    {{-- CARD PAYMENT FORM (shown only when card is selected) --}}
                    <div id="cardPaymentForm" class="space-y-6 p-6 bg-gradient-to-br from-blue-50 to-purple-50 border-2 border-blue-200 rounded-xl {{ old('method') === 'card' || !old('method') ? 'block' : 'hidden' }}">
                        {{-- Warning Banner --}}
                        <div class="bg-gradient-to-r from-amber-100 to-orange-100 border border-amber-300 rounded-lg p-4 mb-2">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="text-sm text-amber-800">
                                    <p class="font-bold">⚠️ SIMULATION DE PAIEMENT</p>
                                    <p class="text-xs mt-1">Ceci est une simulation pour un projet universitaire. Aucun paiement réel ne sera effectué. Utilisez uniquement les données de test fournies.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Virtual Credit Card --}}
                        <div class="relative h-56 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500 rounded-2xl p-6 shadow-2xl overflow-hidden">
                            {{-- Card Background Decorations --}}
                            <div class="absolute top-0 right-0 opacity-10">
                                <svg class="w-40 h-40" fill="white" viewBox="0 0 100 100">
                                    <circle cx="30" cy="30" r="20"/><circle cx="80" cy="70" r="30"/>
                                </svg>
                            </div>

                            {{-- Card Content --}}
                            <div class="relative z-10 flex flex-col justify-between h-full">
                                {{-- Card Type and Chip --}}
                                <div class="flex justify-between items-start">
                                    <div class="flex flex-col">
                                        <span id="cardType" class="text-white font-bold text-lg tracking-widest">VISA</span>
                                    </div>
                                    <div class="w-14 h-11 bg-gradient-to-br from-yellow-300 to-yellow-600 rounded-lg shadow-md"></div>
                                </div>

                                {{-- Card Number Display --}}
                                <div>
                                    <p id="cardNumber" class="text-white text-2xl font-mono tracking-widest">•••• •••• •••• 4242</p>
                                </div>

                                {{-- Card Holder and Expiry --}}
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <p class="text-white text-xs opacity-75 mb-1">CARDHOLDER</p>
                                        <p id="cardHolder" class="text-white font-semibold truncate max-w-[180px]">TEST USER</p>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <p class="text-white text-xs opacity-75 mb-1">EXPIRES</p>
                                        <p id="cardExpiry" class="text-white font-mono text-lg">12/30</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Holder Name --}}
                        <div>
                            <label for="card_holder" class="block text-sm font-semibold text-gray-800 mb-2">Nom du titulaire</label>
                            <input
                                type="text"
                                id="card_holder"
                                name="card_holder"
                                placeholder="Votre nom"
                                value="{{ old('card_holder', 'TEST USER') }}"
                                class="w-full px-4 py-3 border {{ $errors->has('card_holder') ? 'border-red-300' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase tracking-wide"
                            >
                            @error('card_holder')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Card Number --}}
                        <div>
                            <label for="card_number" class="block text-sm font-semibold text-gray-800 mb-2">Numéro de carte</label>
                            <input
                                type="text"
                                id="card_number"
                                name="card_number"
                                placeholder="0000 0000 0000 0000"
                                value="{{ old('card_number', '4242424242424242') }}"
                                maxlength="19"
                                class="w-full px-4 py-3 border {{ $errors->has('card_number') ? 'border-red-300' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono tracking-widest text-center text-lg"
                                inputmode="numeric"
                            >
                            <p class="text-xs text-gray-600 mt-2">Commencez par <strong>4</strong> (Visa) ou <strong>5</strong> (Mastercard)</p>
                            @error('card_number')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Expiry & CVV Row --}}
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Expiry --}}
                            <div>
                                <label for="card_expiry" class="block text-sm font-semibold text-gray-800 mb-2">Expiration</label>
                                <input
                                    type="text"
                                    id="card_expiry"
                                    name="card_expiry"
                                    placeholder="MM/YY"
                                    value="{{ old('card_expiry', '12/30') }}"
                                    maxlength="5"
                                    class="w-full px-4 py-3 border {{ $errors->has('card_expiry') ? 'border-red-300' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-center tracking-wider text-lg"
                                    inputmode="numeric"
                                >
                                @error('card_expiry')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- CVV --}}
                            <div>
                                <label for="card_cvv" class="block text-sm font-semibold text-gray-800 mb-2">CVV</label>
                                <input
                                    type="password"
                                    id="card_cvv"
                                    name="card_cvv"
                                    placeholder="•••"
                                    value="{{ old('card_cvv', '123') }}"
                                    maxlength="3"
                                    class="w-full px-4 py-3 border {{ $errors->has('card_cvv') ? 'border-red-300' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-center tracking-widest text-lg"
                                    inputmode="numeric"
                                >
                                @error('card_cvv')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Test Data Button --}}
                        <button
                            type="button"
                            id="useTestDataBtn"
                            class="w-full px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg"
                        >
                            ✓ Utiliser les données de test
                        </button>

                        {{-- Example Data Box --}}
                        <div class="p-5 bg-white border-l-4 border-blue-500 rounded-lg shadow-sm">
                            <p class="text-xs font-bold text-gray-800 mb-3 uppercase tracking-wide">📋 Données de test disponibles</p>
                            <div class="space-y-2 text-xs text-gray-700 font-mono">
                                <div class="flex justify-between"><span class="text-gray-600">Titulaire :</span><span class="font-semibold">TEST USER</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">Numéro :</span><span class="font-semibold">4242 4242 4242 4242</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">Expiration :</span><span class="font-semibold">12/30</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">CVV :</span><span class="font-semibold">123</span></div>
                            </div>
                        </div>
                    </div>

                    {{-- BANK TRANSFER FORM (shown only when bank_transfer is selected) --}}
                    <div id="bankTransferForm" class="space-y-5 p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl {{ old('method') === 'bank_transfer' ? 'block' : 'hidden' }}">
                        {{-- Info Banner --}}
                        <div class="bg-gradient-to-r from-blue-100 to-indigo-100 border border-blue-300 rounded-lg p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-blue-700 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-bold">🏦 Instructions de virement</p>
                                    <p class="text-xs mt-1">Veuillez effectuer un virement bancaire vers le compte SolarShare en utilisant les coordonnées ci-dessous.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Bank Details Grid --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-2 tracking-wide">💰 Montant à payer</p>
                                <p class="text-3xl font-bold text-blue-700">{{ number_format($amount, 2, ',', ' ') }}<span class="text-lg text-blue-600 ml-1">TND</span></p>
                            </div>

                            <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-2 tracking-wide">📋 Référence de paiement</p>
                                <p class="text-sm font-mono font-bold text-gray-900 break-all">REF-{{ strtoupper(Str::random(8)) }}-{{ date('Ymd') }}</p>
                                <p class="text-xs text-gray-500 mt-2">Utilisez cette référence dans votre virement</p>
                            </div>
                        </div>

                        {{-- Detailed Bank Information --}}
                        <div class="bg-white rounded-lg p-5 border-l-4 border-blue-500 shadow-sm">
                            <p class="text-xs text-gray-600 font-bold uppercase mb-4 tracking-wide">📌 Coordonnées bancaires (simulation)</p>
                            <div class="space-y-3">
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-gray-700">Bénéficiaire</span>
                                    <span class="text-sm font-mono font-bold text-gray-900">SOLARSHARE SARL</span>
                                </div>
                                <div class="h-px bg-gray-200"></div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-gray-700">IBAN</span>
                                    <span class="text-sm font-mono font-bold text-gray-900">TN59 1234 5678 9012 3456</span>
                                </div>
                                <div class="h-px bg-gray-200"></div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-gray-700">BIC</span>
                                    <span class="text-sm font-mono font-bold text-gray-900">SOLATNTT</span>
                                </div>
                                <div class="h-px bg-gray-200"></div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-gray-700">Banque</span>
                                    <span class="text-sm font-bold text-gray-900">SolarBank Tunisia</span>
                                </div>
                            </div>
                        </div>

                        {{-- Important Notice --}}
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex gap-2">
                                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="text-sm text-amber-800">
                                    <p class="font-semibold">⚠️ Simulation universitaire</p>
                                    <p class="text-xs mt-1">Aucun virement bancaire réel ne sera effectué. Les coordonnées ci-dessus sont fictives.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Status Info --}}
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-xs text-green-600 font-bold uppercase mb-2">✓ Après confirmation</p>
                            <p class="text-sm text-green-700">Votre paiement sera marqué comme <strong>EN ATTENTE</strong> jusqu'à confirmation.</p>
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Paiement sécurisé (simulation)</p>
                                <p>Les données de carte ne sont jamais stockées. Votre paiement est confirmé et vous pouvez consulter les détails de votre transaction ci-dessous.</p>
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
                            id="submitBtn"
                        >
                            Confirmer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const methodInputs = document.querySelectorAll('.paymentMethod');
            const cardForm = document.getElementById('cardPaymentForm');
            const bankTransferForm = document.getElementById('bankTransferForm');
            const submitBtn = document.getElementById('submitBtn');

            // Card visualization elements
            const cardHolder = document.getElementById('cardHolder');
            const cardNumber = document.getElementById('cardNumber');
            const cardExpiry = document.getElementById('cardExpiry');
            const cardType = document.getElementById('cardType');

            // Input elements
            const cardHolderInput = document.getElementById('card_holder');
            const cardNumberInput = document.getElementById('card_number');
            const cardExpiryInput = document.getElementById('card_expiry');
            const useTestDataBtn = document.getElementById('useTestDataBtn');

            /**
             * Format card number with spaces every 4 digits
             */
            function formatCardNumber(value) {
                const onlyNumbers = value.replace(/\D/g, '');
                return onlyNumbers.replace(/(\d{4})/g, '$1 ').trim();
            }

            /**
             * Mask card number for display (show only last 4 digits)
             */
            function maskCardNumber(value) {
                const onlyNumbers = value.replace(/\D/g, '');
                if (onlyNumbers.length === 0) return '•••• •••• •••• ••••';
                const lastFour = onlyNumbers.slice(-4);
                return `•••• •••• •••• ${lastFour}`;
            }

            /**
             * Detect card type from first digit
             */
            function detectCardType(value) {
                const firstDigit = value.replace(/\D/g, '').charAt(0);
                if (firstDigit === '4') return 'VISA';
                if (firstDigit === '5') return 'MASTERCARD';
                return 'CARD';
            }

            /**
             * Format expiry date (MM/YY)
             */
            function formatExpiry(value) {
                const onlyNumbers = value.replace(/\D/g, '');
                if (onlyNumbers.length >= 2) {
                    return `${onlyNumbers.slice(0, 2)}/${onlyNumbers.slice(2, 4)}`;
                }
                return onlyNumbers;
            }

            /**
             * Sync card holder name to visual card
             */
            cardHolderInput.addEventListener('input', function () {
                cardHolder.textContent = this.value || 'TEST USER';
            });

            /**
             * Sync card number to visual card
             */
            cardNumberInput.addEventListener('input', function () {
                const formatted = formatCardNumber(this.value);
                this.value = formatted;
                
                const masked = maskCardNumber(this.value);
                cardNumber.textContent = masked;
                
                const type = detectCardType(this.value);
                cardType.textContent = type;
            });

            /**
             * Sync expiry to visual card
             */
            cardExpiryInput.addEventListener('input', function () {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = `${value.slice(0, 2)}/${value.slice(2, 4)}`;
                }
                this.value = value;
                cardExpiry.textContent = this.value || '••/••';
            });

            /**
             * Fill test data
             */
            useTestDataBtn.addEventListener('click', function (e) {
                e.preventDefault();
                cardHolderInput.value = 'TEST USER';
                cardNumberInput.value = '4242424242424242';
                cardExpiryInput.value = '12/30';
                document.getElementById('card_cvv').value = '123';

                // Trigger input events to sync card
                cardHolderInput.dispatchEvent(new Event('input'));
                cardNumberInput.dispatchEvent(new Event('input'));
                cardExpiryInput.dispatchEvent(new Event('input'));

                // Focus on card holder
                cardHolderInput.focus();
            });

            /**
             * Update visible form based on selected method
             */
            function updateVisibleForm() {
                const selectedMethod = document.querySelector('.paymentMethod:checked').value;

                // Hide all forms
                cardForm.classList.add('hidden');
                bankTransferForm.classList.add('hidden');

                // Show selected form
                if (selectedMethod === 'card') {
                    cardForm.classList.remove('hidden');
                    submitBtn.textContent = 'Simuler le paiement';
                } else if (selectedMethod === 'bank_transfer') {
                    bankTransferForm.classList.remove('hidden');
                    submitBtn.textContent = 'Confirmer le virement';
                } else if (selectedMethod === 'cash') {
                    submitBtn.textContent = 'Confirmer le paiement en espèces';
                }
            }

            /**
             * Add event listeners to method radio buttons
             */
            methodInputs.forEach(input => {
                input.addEventListener('change', updateVisibleForm);
            });

            // Initialize card display on page load
            updateVisibleForm();
            cardHolder.textContent = cardHolderInput.value || 'TEST USER';
            cardNumber.textContent = maskCardNumber(cardNumberInput.value);
            cardType.textContent = detectCardType(cardNumberInput.value);
            cardExpiry.textContent = cardExpiryInput.value || '••/••';
        });
    </script>

@endsection
