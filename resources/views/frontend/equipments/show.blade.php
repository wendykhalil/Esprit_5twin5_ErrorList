@extends('layouts.frontend')

@section('title', $equipment->name . ' - SolarShare')

@section('content')

<div class="min-h-screen bg-slate-50">

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 xl:px-10 py-6 lg:py-8">

        <a href="{{ route('equipments.index') }}"

           class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-900 mb-5">

            <span aria-hidden="true">←</span> Retour aux équipements

        </a>

        @if(session('success'))

            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl">

                {{ session('success') }}

            </div>

        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- MAIN: details and usage guides --}}

            <main class="lg:col-span-8 min-w-0 space-y-6">

                {{-- Compact equipment overview --}}

                <section class="bg-white rounded-2xl border border-green-100 shadow-sm overflow-hidden">

                    <div class="grid grid-cols-1 md:grid-cols-5">

                        <div class="md:col-span-2 relative bg-green-50 min-h-64 md:min-h-80">

                            @if($equipment->image)

                                <img src="{{ asset('storage/' . $equipment->image) }}"

                                     alt="{{ $equipment->name }}"

                                     class="w-full h-64 md:h-full md:absolute md:inset-0 object-cover">

                            @else

                                <div class="h-64 md:h-full min-h-64 flex flex-col items-center justify-center text-green-700 gap-2">

                                    <span class="text-5xl" aria-hidden="true">☀️</span>

                                    <span class="text-sm font-semibold">Aucune photo disponible</span>

                                </div>

                            @endif

                            <div class="absolute top-4 left-4 flex flex-wrap gap-2">

                                <span class="bg-white/95 text-green-800 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">

                                    {{ $equipment->category?->name ?? 'Sans catégorie' }}

                                </span>

                            </div>

                        </div>

                        <div class="md:col-span-3 p-5 sm:p-6 flex flex-col justify-between gap-5">

                            <div>

                                <div class="flex flex-wrap gap-2 mb-3">

                                    <span class="text-xs font-bold px-3 py-1 rounded-full {{ $equipment->availability ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">

                                        {{ $equipment->availability ? 'Disponible' : 'Indisponible' }}

                                    </span>

                                    @if($equipment->status === 'inactive')

                                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-red-100 text-red-700">Inactif</span>

                                    @endif

                                </div>

                                <h1 class="text-2xl xl:text-3xl font-bold text-green-950 leading-tight"

                                    style="font-family: Fraunces, Georgia, serif">

                                    {{ $equipment->name }}

                                </h1>

                                <p class="text-sm text-slate-500 mt-3 flex items-center gap-2">

                                    <span aria-hidden="true">📍</span> {{ $equipment->location ?: 'Localisation non renseignée' }}

                                </p>

                                <p class="mt-4 text-sm text-slate-600 leading-relaxed">

                                    {{ \Illuminate\Support\Str::limit($equipment->description ?: 'Aucune description disponible.', 190) }}

                                </p>

                            </div>

                            <div class="border-t border-slate-100 pt-4 flex flex-wrap items-center justify-between gap-3">

                                <div class="flex items-center gap-3 min-w-0">

                                    <span class="w-10 h-10 rounded-full bg-green-100 text-green-800 flex items-center justify-center font-bold shrink-0">

                                        {{ strtoupper(substr($equipment->user?->name ?? 'U', 0, 1)) }}

                                    </span>

                                    <div class="min-w-0">

                                        <p class="text-sm font-bold text-green-900 truncate">{{ $equipment->user?->name ?? 'Utilisateur SolarShare' }}</p>

                                        <p class="text-xs text-slate-500">Propriétaire · Membre depuis {{ $equipment->user?->created_at?->format('Y') ?? '—' }}</p>

                                    </div>

                                </div>

                                @auth

                                    @if(auth()->id() === $equipment->user_id)

                                        <div class="flex gap-2">

                                            <a href="{{ route('equipments.edit', $equipment) }}"

                                               class="px-3 py-2 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100">Modifier</a>

                                            <form action="{{ route('equipments.destroy', $equipment) }}" method="POST"

                                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet équipement ?')">

                                                @csrf

                                                @method('DELETE')

                                                <button type="submit" class="px-3 py-2 rounded-lg text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100">Supprimer</button>

                                            </form>

                                        </div>

                                    @else

                                        <button type="button" class="px-3 py-2 border border-green-200 rounded-lg text-xs font-bold text-green-700 hover:bg-green-50">Contacter</button>

                                    @endif

                                @endauth

                            </div>

                        </div>

                    </div>

                </section>

                {{-- Specifications: short and scannable --}}

                <section class="bg-white rounded-2xl border border-green-100 shadow-sm p-5 sm:p-6">

                    <h2 class="text-lg font-bold text-green-950 mb-4">Caractéristiques de l'équipement</h2>

                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                        <div class="bg-slate-50 rounded-xl p-4"><dt class="text-xs text-slate-500 mb-1">Catégorie</dt><dd class="text-sm font-bold text-slate-800">{{ $equipment->category?->name ?? 'Non définie' }}</dd></div>

                        <div class="bg-slate-50 rounded-xl p-4"><dt class="text-xs text-slate-500 mb-1">Marque</dt><dd class="text-sm font-bold text-slate-800">{{ $equipment->brand ?: 'Non renseignée' }}</dd></div>

                        <div class="bg-slate-50 rounded-xl p-4"><dt class="text-xs text-slate-500 mb-1">Puissance</dt><dd class="text-sm font-bold text-slate-800">{{ $equipment->power ? number_format((float) $equipment->power, 0) . ' W' : 'Non renseignée' }}</dd></div>

                        <div class="bg-slate-50 rounded-xl p-4"><dt class="text-xs text-slate-500 mb-1">Capacité</dt><dd class="text-sm font-bold text-slate-800">{{ $equipment->capacity ? number_format((float) $equipment->capacity, 0) . ' Wh' : 'Non renseignée' }}</dd></div>

                        <div class="bg-slate-50 rounded-xl p-4"><dt class="text-xs text-slate-500 mb-1">État</dt><dd class="text-sm font-bold text-slate-800">@switch($equipment->condition) @case('excellent') Excellent @break @case('good') Bon @break @case('used') Utilisé @break @default {{ ucfirst($equipment->condition ?? 'Non renseigné') }} @endswitch</dd></div>

                        <div class="bg-slate-50 rounded-xl p-4"><dt class="text-xs text-slate-500 mb-1">Localisation</dt><dd class="text-sm font-bold text-slate-800">{{ $equipment->location ?: 'Non renseignée' }}</dd></div>

                    </dl>

                    @if($equipment->description)

                        <details class="group mt-4 border-t border-slate-100 pt-4">

                            <summary class="cursor-pointer font-semibold text-sm text-green-800 flex justify-between items-center list-none">

                                Description complète <span class="text-lg group-open:rotate-180 transition-transform">⌄</span>

                            </summary>

                            <p class="mt-3 text-sm leading-7 text-slate-600 whitespace-pre-line">{{ $equipment->description }}</p>

                        </details>

                    @endif

                </section>

                {{-- Published guides: collapsed by default to save scrolling --}}

                @if($equipment->equipmentGuides->isNotEmpty())

                    <section class="bg-white rounded-2xl border border-green-100 shadow-sm p-5 sm:p-6">

                        <div class="flex flex-wrap items-start justify-between gap-3 mb-5">

                            <div>

                                <h2 class="text-xl font-bold text-green-950 flex items-center gap-2">

                                    <span aria-hidden="true">📖</span> Guides d'utilisation

                                </h2>

                                <p class="text-sm text-slate-500 mt-1">Instructions et conseils pour utiliser cet équipement en sécurité.</p>

                            </div>

                            <span class="text-xs font-bold text-green-800 bg-green-50 px-3 py-1.5 rounded-full">

                                {{ $equipment->equipmentGuides->count() }} guide(s)

                            </span>

                        </div>

                        <div class="space-y-3">

                            @foreach($equipment->equipmentGuides as $guide)

                                <details class="group rounded-xl border border-slate-200 overflow-hidden" {{ $loop->first ? 'open' : '' }}>

                                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 px-4 sm:px-5 py-4 bg-slate-50 hover:bg-green-50 transition-colors">

                                        <div class="min-w-0">

                                            <h3 class="font-bold text-green-950 text-base">{{ $guide->title }}</h3>

                                            <p class="text-xs text-slate-500 mt-1">Cliquez pour {{ $loop->first ? 'replier' : 'consulter' }} le guide</p>

                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">

                                            <span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $guide->difficulty_level === 'beginner' ? 'bg-green-100 text-green-800' : ($guide->difficulty_level === 'intermediate' ? 'bg-amber-100 text-amber-800' : 'bg-orange-100 text-orange-800') }}">

                                                @switch($guide->difficulty_level)

                                                    @case('beginner') Débutant @break

                                                    @case('intermediate') Intermédiaire @break

                                                    @case('advanced') Avancé @break

                                                    @default {{ $guide->difficulty_level }}

                                                @endswitch

                                            </span>

                                            <span class="text-slate-500 group-open:rotate-180 transition-transform" aria-hidden="true">⌄</span>

                                        </div>

                                    </summary>

                                    <div class="p-4 sm:p-5 space-y-5 bg-white">

                                        @if($guide->usage_context)

                                            <div class="rounded-lg bg-green-50 p-3 border border-green-100">

                                                <p class="text-xs font-bold uppercase tracking-wide text-green-700 mb-1">Contexte d'utilisation</p>

                                                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $guide->usage_context }}</p>

                                            </div>

                                        @endif

                                        @if($guide->instructions)

                                            @php

                                                $steps = array_values(array_filter(

                                                    array_map('trim', preg_split('/\r\n|\r|\n/', $guide->instructions)),

                                                    fn ($step) => $step !== ''

                                                ));

                                            @endphp

                                            <div>

                                                <h4 class="font-bold text-green-950 mb-3">Instructions d'utilisation</h4>

                                                <ol class="space-y-2">

                                                    @foreach($steps as $step)

                                                        <li class="flex items-start gap-3 text-sm text-slate-700 leading-6">

                                                            <span class="w-7 h-7 rounded-full bg-green-100 text-green-800 flex items-center justify-center text-xs font-bold shrink-0">{{ $loop->iteration }}</span>

                                                            <span class="pt-0.5">{{ $step }}</span>

                                                        </li>

                                                    @endforeach

                                                </ol>

                                            </div>

                                        @endif

                                        @if($guide->safety_precautions)

                                            <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">

                                                <h4 class="font-bold text-amber-900 mb-2 flex items-center gap-2"><span aria-hidden="true">⚠️</span> Précautions de sécurité</h4>

                                                <p class="text-sm text-amber-950 leading-6 whitespace-pre-line">{{ $guide->safety_precautions }}</p>

                                            </div>

                                        @endif

                                        @if($guide->video_url)

                                            <a href="{{ $guide->video_url }}" target="\_blank" rel="noopener noreferrer"

                                               class="inline-flex items-center gap-2 text-sm font-bold text-green-700 hover:text-green-900 hover:underline">

                                                <span aria-hidden="true">▶</span> Voir la vidéo explicative ↗

                                            </a>

                                        @endif

                                    </div>

                                </details>

                            @endforeach

                        </div>

                    </section>

                @endif

            </main>

            {{-- RIGHT: sticky reservation card --}}

            <aside class="lg:col-span-4 min-w-0">

                <div class="bg-white rounded-2xl border border-green-100 p-5 sm:p-6 shadow-sm lg:sticky lg:top-24">

                    <div class="mb-5 pb-5 border-b border-slate-100">

                        <span class="text-3xl font-bold text-green-800">{{ number_format((float) $equipment->price_per_day, 2) }} TND</span>

                        <span class="text-sm text-slate-500">/ jour</span>

                        <p class="text-xs text-slate-500 mt-1">Réservez votre équipement en quelques étapes.</p>
                        <div class="mt-4 rounded-xl bg-green-50 p-3 text-sm text-green-900">
                            <strong>Estimation indicative (8 h)</strong><br>
                            {{ number_format($estimation['kwh_estimes'], 2) }} kWh attendus · {{ number_format($estimation['co2_evite_kg'], 2) }} kg CO2 évités
                        </div>

                    </div>

                    @if($equipment->availability)

                        @auth

                            @if(auth()->id() === $equipment->user_id)

                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">

                                    <p class="text-sm text-blue-800 font-medium">Vous êtes le propriétaire de cet équipement.</p>

                                </div>

                            @else

                                {{-- Consolidated Reservation Form --}}

                                <form id="reservationForm" method="POST" action="{{ route('reservations.store') }}" class="space-y-4">

                                    @csrf

                                    <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">

                                    {{-- Validation Error Alert --}}

                                    <div id="formErrors" class="hidden bg-red-50 border border-red-200 rounded-xl p-4"></div>

                                    {{-- Start Date --}}

                                    <div>

                                        <label for="date_debut" class="block text-sm font-semibold text-slate-700 mb-2">

                                            Date de début <span class="text-red-600">*</span>

                                        </label>

                                        <div class="relative">

                                            <input type="text" 

                                                   id="date_debut" 

                                                   name="date_debut"

                                                   placeholder="YYYY-MM-DD"

                                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"

                                                   required

                                                   readonly>

                                            <div id="datePickerCalendar" class="hidden absolute top-full left-0 mt-2 bg-white border border-slate-300 rounded-xl shadow-lg z-50 p-4 w-96">

                                                <div class="mb-4 flex items-center justify-between">

                                                    <button type="button" id="prevMonth" class="px-2 py-1 text-slate-600 hover:bg-slate-100 rounded">←</button>

                                                    <div id="monthYearDisplay" class="font-semibold text-slate-800"></div>

                                                    <button type="button" id="nextMonth" class="px-2 py-1 text-slate-600 hover:bg-slate-100 rounded">→</button>

                                                </div>

                                                <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-xs">

                                                    <!-- Calendar will be generated by JavaScript -->

                                                </div>

                                            </div>

                                        </div>

                                        <p class="text-xs text-slate-500 mt-1">Dates grisées et barrées sont réservées. Cliquez sur une date disponible pour la sélectionner.</p>

                                    </div>

                                    {{-- Duration Selection --}}

                                    <div>

                                        <label class="block text-sm font-semibold text-slate-700 mb-2">

                                            Durée de location <span class="text-red-600">*</span>

                                        </label>

                                        <div class="grid grid-cols-2 gap-2">

                                            @foreach([1 => '1 jour', 3 => '3 jours', 7 => '1 semaine', 14 => '2 semaines'] as $days => $label)

                                                <button type="button" 

                                                        class="durationBtn py-2.5 px-3 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50 transition-colors"

                                                        data-days="{{ $days }}"

                                                        data-end-date="">

                                                    {{ $label }}

                                                </button>

                                            @endforeach

                                        </div>

                                        <input type="hidden" id="date_fin" name="date_fin">

                                    </div>

                                    {{-- Price Breakdown --}}

                                    <div id="priceBreakdown" class="border-t border-slate-100 pt-4 space-y-3">

                                        <div class="flex justify-between gap-3 text-sm text-slate-600">

                                            <span id="priceCalc">{{ number_format((float) $equipment->price_per_day, 2) }} TND × 1 jour</span>

                                            <span id="priceTotal">{{ number_format((float) $equipment->price_per_day, 2) }} TND</span>

                                        </div>

                                        <div class="flex justify-between gap-3 text-sm text-slate-600">

                                            <span>Frais de service</span>

                                            <span id="serviceFee">1.00 TND</span>

                                        </div>

                                        <div class="flex justify-between gap-3 border-t border-slate-100 pt-3 font-bold text-green-950">

                                            <span>Total</span>

                                            <span id="grandTotal">{{ number_format((float) $equipment->price_per_day + 1, 2) }} TND</span>

                                        </div>

                                    </div>

                                    {{-- Compensation Code (Collapsible) --}}

                                    <details class="group border-t border-slate-100 pt-4">

                                        <summary class="cursor-pointer text-sm font-semibold text-slate-700 flex justify-between items-center list-none hover:text-green-700">

                                            Code de compensation (optionnel)

                                            <span class="text-lg group-open:rotate-180 transition-transform">⌄</span>

                                        </summary>

                                        <div class="mt-3 space-y-2">

                                            <input type="text" 

                                                   id="compensation_code" 

                                                   name="compensation_code"

                                                   placeholder="Entrez votre code"

                                                   maxlength="20"

                                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">

                                            <p class="text-xs text-slate-500">Si vous avez un code de réduction, entrez-le ici pour voir la remise appliquée.</p>

                                            <div id="codeDiscount" class="hidden bg-green-50 border border-green-200 rounded-lg p-3">

                                                <p class="text-sm text-green-800 font-semibold">

                                                    <span id="discountText"></span>

                                                </p>

                                            </div>

                                        </div>

                                    </details>

                                    {{-- Submit Button --}}

                                    <button type="submit" 

                                            id="submitBtn"

                                            class="w-full py-4 mt-6 font-bold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors disabled:bg-slate-300 disabled:cursor-not-allowed">

                                        Réserver maintenant

                                    </button>

                                    <p class="text-xs text-center text-gray-400">

                                        Le paiement est sécurisé via SolarShare.

                                    </p>

                                </form>

                            @endif

                        @else

                            <a href="{{ route('login') }}"

                               class="block text-center w-full py-3.5 font-bold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors">

                                Se connecter pour réserver

                            </a>

                        @endauth

                    @else

                        <button type="button" disabled class="w-full py-3.5 font-bold rounded-xl bg-slate-200 text-slate-400 cursor-not-allowed">Non disponible</button>

                    @endif

                </div>

            </aside>

        </div>

        {{-- Related equipment --}}

        @if($related->count() > 0)

            <section class="mt-10 pt-8 border-t border-green-100">

                <h2 class="text-2xl font-bold text-green-950 mb-5">Équipements similaires</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                    @foreach($related as $eq)

                        <a href="{{ route('equipments.show', $eq) }}"

                           class="bg-white border border-green-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">

                            @if($eq->image)

                                <img src="{{ asset('storage/' . $eq->image) }}" alt="{{ $eq->name }}" class="w-full h-40 object-cover">

                            @else

                                <div class="h-40 bg-green-50 flex items-center justify-center text-4xl" aria-label="Aucune photo">☀️</div>

                            @endif

                            <div class="p-4">

                                <p class="text-xs font-semibold text-green-700 mb-1">{{ $eq->category?->name ?? 'Sans catégorie' }}</p>

                                <h3 class="font-bold text-green-950 mb-3">{{ $eq->name }}</h3>

                                <div class="flex justify-between items-center gap-2 text-sm">

                                    <span class="text-slate-500">{{ $eq->location }}</span>

                                    <span class="font-bold text-green-800">{{ number_format((float) $eq->price_per_day, 2) }} TND/j</span>

                                </div>

                            </div>

                        </a>

                    @endforeach

                </div>

            </section>

        @endif

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const price = @json((float) $equipment->price_per_day);
    const equipmentId = @json((int) $equipment->id);
    
    // Form elements
    const form = document.getElementById('reservationForm');
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const durationBtns = document.querySelectorAll('.durationBtn');
    const compensationCode = document.getElementById('compensation_code');
    const submitBtn = document.getElementById('submitBtn');
    const formErrors = document.getElementById('formErrors');
    const priceCalc = document.getElementById('priceCalc');
    const priceTotal = document.getElementById('priceTotal');
    const grandTotal = document.getElementById('grandTotal');
    const codeDiscount = document.getElementById('codeDiscount');
    
    // Calendar elements
    const datePickerCalendar = document.getElementById('datePickerCalendar');
    const monthYearDisplay = document.getElementById('monthYearDisplay');
    const calendarGrid = document.getElementById('calendarGrid');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    
    let selectedDays = 1;
    let durationSelected = false;
    let blockedPeriods = [];
    let currentCalendarDate = new Date();

    // Supported duration values
    const supportedDurations = [1, 3, 7, 14];

    /**
     * Load blocked periods from the availability endpoint
     */
    async function loadBlockedPeriods() {
        try {
            const response = await fetch(`/equipements/${equipmentId}/availability`);
            if (response.ok) {
                const data = await response.json();
                blockedPeriods = data.blocked_periods || [];
                console.log('Loaded blocked periods:', blockedPeriods);
            } else {
                console.warn('Failed to load availability data');
            }
        } catch (err) {
            console.error('Error loading blocked periods:', err);
        }
    }

    /**
     * Check if a single date is blocked
     */
    function isDateBlocked(dateStr) {
        const date = new Date(dateStr);
        
        for (const period of blockedPeriods) {
            const blockStart = new Date(period.start_date);
            const blockEnd = new Date(period.end_date);
            
            // Half-open interval: blockStart <= date < blockEnd
            if (blockStart <= date && date < blockEnd) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if a date range overlaps with blocked periods
     * Uses half-open interval: [start, end)
     */
    function hasOverlapWithBlocked(startStr, endStr) {
        const start = new Date(startStr);
        const end = new Date(endStr);

        for (const period of blockedPeriods) {
            const blockStart = new Date(period.start_date);
            const blockEnd = new Date(period.end_date);

            // Half-open interval overlap: blockStart < end AND blockEnd > start
            if (blockStart < end && blockEnd > start) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format date to YYYY-MM-DD string
     */
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    /**
     * Validate that start date is not in the past
     */
    function isValidStartDate(dateStr) {
        if (!dateStr) return false;
        
        // Validate YYYY-MM-DD format
        if (!/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return false;
        
        const selected = new Date(dateStr);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selected >= today;
    }

    /**
     * Calculate end date from start date and duration
     */
    function calculateEndDate(startDateStr, days) {
        if (!startDateStr || !days) return '';
        const start = new Date(startDateStr);
        const end = new Date(start);
        end.setDate(end.getDate() + parseInt(days));
        return end.toISOString().split('T')[0];
    }

    /**
     * Render calendar for current month
     */
    function renderCalendar() {
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();
        
        monthYearDisplay.textContent = currentCalendarDate.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
        
        // Clear grid
        calendarGrid.innerHTML = '';
        
        // Add day headers (Mon-Sun)
        const dayHeaders = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
        dayHeaders.forEach(day => {
            const header = document.createElement('div');
            header.className = 'text-center font-bold text-slate-600 py-2';
            header.textContent = day;
            calendarGrid.appendChild(header);
        });
        
        // Get first day of month (0 = Sunday, so add 1 and mod 7)
        const firstDay = new Date(year, month, 1).getDay();
        const firstMonday = (firstDay + 6) % 7; // Convert to Monday = 0
        
        // Get number of days in month
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Add empty cells for days before month starts
        for (let i = 0; i < firstMonday; i++) {
            const empty = document.createElement('div');
            calendarGrid.appendChild(empty);
        }
        
        // Add days of month
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dateObj = new Date(year, month, day);
            const dateStr = formatDate(dateObj);
            const isBlocked = isDateBlocked(dateStr);
            const isPast = dateObj < today;
            
            const dayCell = document.createElement('button');
            dayCell.type = 'button';
            dayCell.className = 'py-2 text-center text-xs font-semibold rounded transition-colors';
            dayCell.textContent = day;
            
            if (isPast) {
                dayCell.className += ' bg-slate-50 text-slate-400 cursor-not-allowed';
                dayCell.disabled = true;
            } else if (isBlocked) {
                dayCell.className += ' bg-slate-300 text-slate-500 line-through cursor-not-allowed';
                dayCell.disabled = true;
                dayCell.title = 'Date réservée';
            } else if (dateStr === dateDebut.value) {
                dayCell.className += ' bg-green-600 text-white';
            } else {
                dayCell.className += ' bg-white text-slate-700 hover:bg-green-100 cursor-pointer';
                dayCell.addEventListener('click', (e) => {
                    e.preventDefault();
                    selectDate(dateStr);
                });
            }
            
            calendarGrid.appendChild(dayCell);
        }
    }

    /**
     * Select a date from calendar
     */
    function selectDate(dateStr) {
        dateDebut.value = dateStr;
        datePickerCalendar.classList.add('hidden');
        
        // Trigger change event
        dateDebut.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /**
     * Update price display and end date
     */
    function updateDisplay() {
        if (!dateDebut.value || !durationSelected) {
            priceCalc.textContent = `${price.toFixed(2)} TND × 1 jour`;
            priceTotal.textContent = `${price.toFixed(2)} TND`;
            grandTotal.textContent = `${(price + 1).toFixed(2)} TND`;
            dateFin.value = '';
            clearErrors();
            return;
        }

        // Calculate end date
        const endDate = calculateEndDate(dateDebut.value, selectedDays);
        dateFin.value = endDate;

        // Check for overlaps with blocked periods
        if (hasOverlapWithBlocked(dateDebut.value, endDate)) {
            showError('Cet équipement est déjà réservé sur cette période. Veuillez choisir d\'autres dates.');
            submitBtn.disabled = true;
            return;
        }

        clearErrors();
        submitBtn.disabled = false;

        // Update price display
        const subtotal = price * selectedDays;
        const fee = 1.00;
        const total = subtotal + fee;

        priceCalc.textContent = `${price.toFixed(2)} TND × ${selectedDays} jour${selectedDays > 1 ? 's' : ''}`;
        priceTotal.textContent = `${subtotal.toFixed(2)} TND`;
        grandTotal.textContent = `${total.toFixed(2)} TND`;
    }

    /**
     * Calendar navigation
     */
    prevMonthBtn.addEventListener('click', (e) => {
        e.preventDefault();
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', (e) => {
        e.preventDefault();
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
        renderCalendar();
    });

    /**
     * Show/hide calendar on date input click
     */
    dateDebut.addEventListener('click', function (e) {
        e.stopPropagation();
        datePickerCalendar.classList.toggle('hidden');
        if (!datePickerCalendar.classList.contains('hidden')) {
            renderCalendar();
        }
    });

    /**
     * Hide calendar when clicking outside
     */
    document.addEventListener('click', function (e) {
        if (!datePickerCalendar.contains(e.target) && e.target !== dateDebut) {
            datePickerCalendar.classList.add('hidden');
        }
    });

    /**
     * Validate start date on change
     */
    dateDebut.addEventListener('change', function () {
        clearErrors();
        
        // Validate start date
        if (this.value && !isValidStartDate(this.value)) {
            showError('La date de début ne peut pas être dans le passé.');
            this.value = '';
            dateFin.value = '';
            return;
        }

        // If both date and duration selected, update display
        if (durationSelected) {
            updateDisplay();
        }
    });

    /**
     * Duration button click handlers
     */
    durationBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const days = parseInt(this.dataset.days);

            // Validate supported duration
            if (!supportedDurations.includes(days)) {
                showError('Durée de location non supportée.');
                return;
            }

            clearErrors();
            selectedDays = days;
            durationSelected = true;

            // Update button states
            durationBtns.forEach(b => {
                const isSelected = parseInt(b.dataset.days) === days;
                b.classList.toggle('bg-green-600', isSelected);
                b.classList.toggle('text-white', isSelected);
                b.classList.toggle('border-green-600', isSelected);
                b.classList.toggle('border-gray-200', !isSelected);
                b.classList.toggle('text-gray-600', !isSelected);
            });

            // If start date is set, update display
            if (dateDebut.value) {
                updateDisplay();
            }
        });
    });

    /**
     * Validate compensation code (real-time)
     */
    compensationCode.addEventListener('change', async function () {
        const code = this.value.trim();
        
        if (!code) {
            codeDiscount.classList.add('hidden');
            return;
        }

        // Note: Full validation happens server-side
        // Client-side just shows a placeholder
        // In a real app, you might make an AJAX call to check code validity
        // For now, we'll let the backend handle all validation
    });

    /**
     * Show error message
     */
    function showError(message) {
        formErrors.innerHTML = `<p class="text-sm text-red-700 font-semibold">${message}</p>`;
        formErrors.classList.remove('hidden');
        window.scrollTo({ top: formErrors.offsetTop - 100, behavior: 'smooth' });
    }

    /**
     * Clear error messages
     */
    function clearErrors() {
        formErrors.classList.add('hidden');
        formErrors.innerHTML = '';
    }

    /**
     * Validate form before submission
     */
    function validateFormFrontend() {
        clearErrors();

        // Check start date
        if (!dateDebut.value) {
            showError('Veuillez sélectionner une date de début.');
            return false;
        }

        // Check date format is valid YYYY-MM-DD
        if (!/^\d{4}-\d{2}-\d{2}$/.test(dateDebut.value)) {
            showError('Format de date invalide. Utilisez YYYY-MM-DD.');
            return false;
        }

        // Check start date is valid (not in past)
        if (!isValidStartDate(dateDebut.value)) {
            showError('La date de début ne peut pas être dans le passé.');
            return false;
        }

        // Check duration selected
        if (!durationSelected) {
            showError('Veuillez sélectionner une durée de location.');
            return false;
        }

        // Check for overlap with blocked periods
        const endDate = calculateEndDate(dateDebut.value, selectedDays);
        if (hasOverlapWithBlocked(dateDebut.value, endDate)) {
            showError('Cet équipement est déjà réservé sur cette période. Veuillez choisir d\'autres dates.');
            return false;
        }

        // Check equipment is still available (will be validated server-side too)
        // This is informational only
        if (!@json((bool) $equipment->availability)) {
            showError('Cet équipement n\'est plus disponible.');
            return false;
        }

        // Compensation code (if provided, will be validated server-side)
        const code = compensationCode.value.trim();
        if (code && code.length > 20) {
            showError('Le code de compensation est trop long.');
            return false;
        }

        return true;
    }

    /**
     * Handle form submission
     */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Frontend validation
        if (!validateFormFrontend()) {
            return;
        }

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Réservation en cours...';

        // Submit form via POST
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // If successful (201 or 200), redirect to reservation show page
            if (response.ok) {
                const data = await response.json();
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Fallback: redirect to reservations index
                    window.location.href = '/reservations';
                }
            } else {
                // Parse error response
                const errorData = await response.json();
                const errorMessages = [];

                if (errorData.errors) {
                    Object.entries(errorData.errors).forEach(([field, messages]) => {
                        if (Array.isArray(messages)) {
                            errorMessages.push(...messages);
                        } else {
                            errorMessages.push(messages);
                        }
                    });
                } else if (errorData.message) {
                    errorMessages.push(errorData.message);
                }

                showError(errorMessages.join('<br>'));
                submitBtn.disabled = false;
                submitBtn.textContent = 'Réserver maintenant';
            }
        } catch (err) {
            console.error('Form submission error:', err);
            showError('Une erreur est survenue. Veuillez réessayer.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Réserver maintenant';
        }
    });

    // Initialize: load blocked periods then render calendar
    loadBlockedPeriods().then(() => {
        updateDisplay();
    });
});
</script>

@endsection
