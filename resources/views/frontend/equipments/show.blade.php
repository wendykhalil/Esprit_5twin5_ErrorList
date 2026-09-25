@extends('layouts.frontend')

@section('title', $equipment['name'] . ' - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <a href="{{ route('equipments.index') }}" class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 mb-12">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Retour aux équipements
            </a>

            <div class="grid lg:grid-cols-3 gap-10">
                {{-- Main content --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Image --}}
                    <div class="relative bg-white rounded-2xl overflow-hidden shadow-sm border border-green-100">
                        <img
                            src="{{ $equipment['image'] }}"
                            alt="{{ $equipment['name'] }}"
                            class="w-full h-96 object-cover bg-green-50"
                        />
                        <div class="absolute top-4 left-4 flex gap-2">
                            <span class="bg-white text-green-700 text-sm font-medium px-3 py-1 rounded-full border border-green-100 shadow-sm">
                                {{ $equipment['category'] }}
                            </span>
                            @if($equipment['available'])
                                <span class="bg-green-500 text-white text-sm font-medium px-3 py-1 rounded-full">
                                    Disponible
                                </span>
                            @else
                                <span class="bg-gray-400 text-white text-sm font-medium px-3 py-1 rounded-full">
                                    Indisponible
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="bg-white rounded-2xl border border-green-100 p-8 shadow-sm">
                        <h1 class="text-4xl font-bold text-green-900 mb-6" style="font-family: Fraunces, Georgia, serif">
                            {{ $equipment['name'] }}
                        </h1>

                        {{-- Owner info --}}
                        <div class="flex items-center gap-4 pb-8 mb-8 border-b border-green-100">
                            <div class="w-12 h-12 rounded-full bg-green-100 text-green-700 font-bold flex items-center justify-center text-lg shrink-0">
                                {{ $equipment['ownerInitial'] }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-green-900">{{ $equipment['owner'] }}</p>
                                <div class="flex items-center gap-2 text-sm">
                                    <p class="text-gray-600">{{ $equipment['location'] }} · Membre depuis 2024</p>
                                </div>
                                <div class="flex items-center gap-1 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3.5 h-3.5 {{ $i <= round($equipment['rating']) ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                    <span class="text-xs text-gray-500 ml-1">Propriétaire vérifié</span>
                                </div>
                            </div>
                            <button class="px-4 py-2 border border-green-200 text-green-700 text-sm font-medium rounded-xl hover:bg-green-50 transition-colors whitespace-nowrap">
                                Contacter
                            </button>
                        </div>

                        {{-- Location --}}
                        <div class="flex items-center gap-2 pb-8 mb-8 border-b border-green-100">
                            <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-lg text-gray-700">{{ $equipment['location'] }}</span>
                        </div>

                        {{-- Description --}}
                        <div class="pb-8 mb-8 border-b border-green-100">
                            <h2 class="text-xl font-bold text-green-900 mb-4">Description</h2>
                            <p class="text-gray-600 leading-relaxed text-base">{{ $equipment['description'] }}</p>
                        </div>

                        {{-- Specifications --}}
                        <div>
                            <h2 class="text-xl font-bold text-green-900 mb-6">Caractéristiques</h2>
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                                    <p class="text-xs text-green-600 font-semibold uppercase mb-2 tracking-wide">Catégorie</p>
                                    <p class="text-lg font-semibold text-green-900">{{ $equipment['category'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                                    <p class="text-xs text-green-600 font-semibold uppercase mb-2 tracking-wide">Localisation</p>
                                    <p class="text-lg font-semibold text-green-900">{{ $equipment['location'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                                    <p class="text-xs text-green-600 font-semibold uppercase mb-2 tracking-wide">Prix</p>
                                    <p class="text-lg font-semibold text-green-900">{{ $equipment['price'] }} TND / {{ $equipment['period'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                                    <p class="text-xs text-green-600 font-semibold uppercase mb-2 tracking-wide">État</p>
                                    <p class="text-lg font-semibold text-green-900">{{ $equipment['available'] ? 'Disponible' : 'Indisponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Booking Section --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-green-100 p-6 shadow-sm sticky top-24">
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-green-700" id="bookingPrice">{{ $equipment['price'] }} TND</span>
                            <span class="text-gray-500"> / jour</span>
                        </div>

                        <div id="bookingForm" class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Date de début</label>
                                <input
                                    type="date"
                                    id="startDate"
                                    min="{{ date('Y-m-d') }}"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Durée de location</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" class="durationBtn py-2 px-3 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50 transition-colors" data-days="1">
                                        1 jour
                                    </button>
                                    <button type="button" class="durationBtn py-2 px-3 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50 transition-colors" data-days="3">
                                        3 jours
                                    </button>
                                    <button type="button" class="durationBtn py-2 px-3 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50 transition-colors" data-days="7">
                                        1 semaine
                                    </button>
                                    <button type="button" class="durationBtn py-2 px-3 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50 transition-colors" data-days="14">
                                        2 semaines
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="priceBreakdown" class="border-t border-gray-100 pt-4 mb-6 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span id="priceCalc">{{ $equipment['price'] }} TND × 1 jour</span>
                                <span id="priceTotal">{{ $equipment['price'] }} TND</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Frais de service</span>
                                <span id="serviceFee">1 TND</span>
                            </div>
                            <div class="flex justify-between font-bold text-green-900 pt-2 border-t border-gray-100">
                                <span>Total</span>
                                <span id="grandTotal">{{ $equipment['price'] + 1 }} TND</span>
                            </div>
                        </div>

                        <button
                            id="bookingBtn"
                            type="button"
                            @if(!$equipment['available']) disabled @endif
                            class="w-full py-4 font-bold rounded-xl transition-colors {{ $equipment['available'] ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}"
                        >
                            {{ $equipment['available'] ? 'Réserver maintenant' : 'Non disponible' }}
                        </button>
                        <p id="trustBadge" class="text-xs text-center text-gray-400 mt-3">Paiement sécurisé · Annulation gratuite 24h avant</p>

                        <div id="confirmationMsg" class="hidden mt-4 bg-green-50 rounded-xl p-4 text-center border border-green-200">
                            <div class="text-3xl mb-2">✅</div>
                            <p class="font-semibold text-green-900">Réservation confirmée !</p>
                            <p class="text-sm text-gray-600 mt-2" id="confirmationText">Votre demande de réservation est prête. La réservation réelle sera disponible prochainement.</p>
                            <button type="button" id="resetBtn" class="mt-4 text-sm text-green-600 hover:text-green-700 underline">
                                Faire une autre réservation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related equipment --}}
    @if(count($related) > 0)
        <div class="bg-white py-16 mt-8 border-t border-green-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-green-900 mb-10">Équipements similaires</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($related as $eq)
                        <x-frontend.equipment-card :equipment="$eq" />
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Booking CTA --}}
    <section class="py-20 bg-gradient-to-br from-green-700 to-green-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Vous êtes intéressé ?</h2>
            <p class="text-green-200 mb-8 max-w-2xl mx-auto text-lg leading-relaxed">
                Connectez-vous pour réserver cet équipement ou nous contacter pour plus d'informations.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a
                    href="{{ route('login') }}"
                    class="px-8 py-3 bg-yellow-400 hover:bg-yellow-300 text-green-900 font-bold rounded-xl transition-colors"
                >
                    Réserver maintenant
                </a>
                <a
                    href="{{ route('contact') }}"
                    class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl border border-white/20 transition-colors"
                >
                    Nous contacter
                </a>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const equipmentPrice = {{ $equipment['price'] }};
            const durationBtns = document.querySelectorAll('.durationBtn');
            const startDateInput = document.getElementById('startDate');
            const bookingBtn = document.getElementById('bookingBtn');
            const bookingForm = document.getElementById('bookingForm');
            const priceBreakdown = document.getElementById('priceBreakdown');
            const confirmationMsg = document.getElementById('confirmationMsg');
            const trustBadge = document.getElementById('trustBadge');
            const resetBtn = document.getElementById('resetBtn');
            
            let selectedDays = 1;
            let selectedDate = '';

            // Set first button as active on load
            if (durationBtns.length > 0) {
                durationBtns[0].classList.add('bg-green-600', 'text-white', 'border-green-600');
                durationBtns[0].classList.remove('border-gray-200', 'text-gray-600');
            }

            // Duration button click handlers
            durationBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectedDays = parseInt(this.dataset.days);
                    
                    // Remove active state from all buttons
                    durationBtns.forEach(b => {
                        b.classList.remove('bg-green-600', 'text-white', 'border-green-600');
                        b.classList.add('border-gray-200', 'text-gray-600');
                    });
                    
                    // Add active state to clicked button
                    this.classList.remove('border-gray-200', 'text-gray-600');
                    this.classList.add('bg-green-600', 'text-white', 'border-green-600');
                    
                    updatePriceCalculation();
                });
            });

            // Start date input handler
            startDateInput.addEventListener('change', function() {
                selectedDate = this.value;
            });

            function updatePriceCalculation() {
                const itemTotal = equipmentPrice * selectedDays;
                const serviceFee = 1; // Mock service fee
                const grandTotal = itemTotal + serviceFee;
                
                document.getElementById('priceCalc').textContent = `${equipmentPrice} TND × ${selectedDays} jour${selectedDays > 1 ? 's' : ''}`;
                document.getElementById('priceTotal').textContent = `${itemTotal} TND`;
                document.getElementById('serviceFee').textContent = `${serviceFee} TND`;
                document.getElementById('grandTotal').textContent = `${grandTotal} TND`;
            }

            // Booking button handler
            bookingBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!startDateInput.value) {
                    alert('Veuillez sélectionner une date de début');
                    return;
                }

                // Show confirmation
                bookingForm.classList.add('hidden');
                priceBreakdown.classList.add('hidden');
                bookingBtn.classList.add('hidden');
                trustBadge.classList.add('hidden');
                confirmationMsg.classList.remove('hidden');
            });

            // Reset button handler
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Reset form
                startDateInput.value = '';
                selectedDays = 1;
                selectedDate = '';
                
                // Reset button styles
                durationBtns.forEach((btn, index) => {
                    btn.classList.remove('bg-green-600', 'text-white', 'border-green-600');
                    btn.classList.add('border-gray-200', 'text-gray-600');
                    
                    if (index === 0) {
                        btn.classList.add('bg-green-600', 'text-white', 'border-green-600');
                        btn.classList.remove('border-gray-200', 'text-gray-600');
                    }
                });
                
                updatePriceCalculation();
                
                // Show form again
                bookingForm.classList.remove('hidden');
                priceBreakdown.classList.remove('hidden');
                bookingBtn.classList.remove('hidden');
                trustBadge.classList.remove('hidden');
                confirmationMsg.classList.add('hidden');
            });
        });
    </script>

@endsection
