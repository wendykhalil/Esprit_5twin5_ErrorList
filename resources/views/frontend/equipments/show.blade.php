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
                                            <a href="{{ $guide->video_url }}" target="_blank" rel="noopener noreferrer"
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
                    </div>

                    <div id="bookingForm" class="space-y-4 mb-5">
                        <div>
                            <label for="startDate" class="block text-sm font-semibold text-slate-700 mb-2">Date de début</label>
                            <input type="date" id="startDate" min="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                        <div>
                            <p class="block text-sm font-semibold text-slate-700 mb-2">Durée de location</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach([1 => '1 jour', 3 => '3 jours', 7 => '1 semaine', 14 => '2 semaines'] as $days => $label)
                                    <button type="button" data-days="{{ $days }}"
                                            class="durationBtn py-2.5 px-3 text-sm rounded-xl border border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50 transition-colors">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="priceBreakdown" class="border-t border-slate-100 pt-4 mb-5 space-y-3">
                        <div class="flex justify-between gap-3 text-sm text-slate-600">
                            <span id="priceCalc">{{ number_format((float) $equipment->price_per_day, 2) }} TND × 1 jour</span>
                            <span id="priceTotal">{{ number_format((float) $equipment->price_per_day, 2) }} TND</span>
                        </div>
                        <div class="flex justify-between gap-3 text-sm text-slate-600">
                            <span>Frais de service</span><span id="serviceFee">1.00 TND</span>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-slate-100 pt-3 font-bold text-green-950">
                            <span>Total</span><span id="grandTotal">{{ number_format((float) $equipment->price_per_day + 1, 2) }} TND</span>
                        </div>
                    </div>

                    @if($equipment->availability)
                        @auth
                            @if(auth()->id() === $equipment->user_id)
                                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                                    <p class="text-sm text-blue-800 font-medium">Vous êtes le propriétaire de cet équipement.</p>
                                </div>
                            @else
                                <button id="bookingBtn" type="button"
                                        class="w-full py-3.5 font-bold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors">
                                    Réserver maintenant
                                </button>
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

                    <div id="confirmationMsg" class="hidden mt-4 bg-green-50 rounded-xl p-4 text-center border border-green-200">
                        <div class="text-3xl mb-2" aria-hidden="true">✅</div>
                        <p class="font-semibold text-green-900">Réservation confirmée !</p>
                        <p class="text-sm text-slate-600 mt-2">Votre demande est prête. Vous pouvez maintenant procéder au paiement.</p>
                        <div class="mt-4 space-y-2">
                            <button type="button" id="proceedPaymentBtn" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors text-sm">Procéder au paiement →</button>
                            <button type="button" id="resetBtn" class="w-full py-2 text-sm text-green-700 hover:text-green-900 underline">Faire une autre réservation</button>
                        </div>
                    </div>
                    <p class="text-xs text-center text-slate-400 mt-4">Paiement sécurisé via SolarShare.</p>
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

document.addEventListener('DOMContentLoaded', function () {

    const equipmentPrice = {{ (float) $equipment->price_per_day }};

    const equipmentId = {{ $equipment->id }};

    const equipmentName = @json($equipment->name);

    const durationButtons =

        document.querySelectorAll('.durationBtn');

    const startDateInput =

        document.getElementById('startDate');

    const bookingButton =

        document.getElementById('bookingBtn');

    const bookingForm =

        document.getElementById('bookingForm');

    const priceBreakdown =

        document.getElementById('priceBreakdown');

    const confirmationMessage =

        document.getElementById('confirmationMsg');

    const proceedPaymentButton =

        document.getElementById('proceedPaymentBtn');

    const resetButton =

        document.getElementById('resetBtn');

    let selectedDays = 1;

    let grandTotal = equipmentPrice + 1;

    function updatePriceCalculation() {

        const itemTotal =

            equipmentPrice * selectedDays;

        const serviceFee = 1;

        grandTotal =

            itemTotal + serviceFee;

        const priceCalc =

            document.getElementById('priceCalc');

        const priceTotal =

            document.getElementById('priceTotal');

        const serviceFeeElement =

            document.getElementById('serviceFee');

        const grandTotalElement =

            document.getElementById('grandTotal');

        if (priceCalc) {

            priceCalc.textContent =

                equipmentPrice.toFixed(2)

                + ' TND × '

                + selectedDays

                + ' jour'

                + (selectedDays > 1 ? 's' : '');

        }

        if (priceTotal) {

            priceTotal.textContent =

                itemTotal.toFixed(2)

                + ' TND';

        }

        if (serviceFeeElement) {

            serviceFeeElement.textContent =

                serviceFee.toFixed(2)

                + ' TND';

        }

        if (grandTotalElement) {

            grandTotalElement.textContent =

                grandTotal.toFixed(2)

                + ' TND';

        }

    }

    durationButtons.forEach(function (button, index) {

        if (index === 0) {

            button.classList.add(

                'bg-green-600',

                'text-white',

                'border-green-600'

            );

            button.classList.remove(

                'border-gray-200',

                'text-gray-600'

            );

        }

        button.addEventListener('click', function () {

            selectedDays =

                parseInt(this.dataset.days);

            durationButtons.forEach(function (item) {

                item.classList.remove(

                    'bg-green-600',

                    'text-white',

                    'border-green-600'

                );

                item.classList.add(

                    'border-gray-200',

                    'text-gray-600'

                );

            });

            this.classList.remove(

                'border-gray-200',

                'text-gray-600'

            );

            this.classList.add(

                'bg-green-600',

                'text-white',

                'border-green-600'

            );

            updatePriceCalculation();

        });

    });

    if (bookingButton) {

        bookingButton.addEventListener(

            'click',

            function () {

                if (

                    startDateInput &&

                    !startDateInput.value

                ) {

                    alert(

                        'Veuillez sélectionner une date de début.'

                    );

                    return;

                }

                if (bookingForm) {

                    bookingForm.classList.add('hidden');

                }

                if (priceBreakdown) {

                    priceBreakdown.classList.add('hidden');

                }

                bookingButton.classList.add('hidden');

                if (confirmationMessage) {

                    confirmationMessage.classList.remove('hidden');

                }

            }

        );

    }

    if (proceedPaymentButton) {

        proceedPaymentButton.addEventListener(

            'click',

            function () {

                const description =

                    'Location de '

                    + equipmentName

                    + ' pour '

                    + selectedDays

                    + ' jour'

                    + (selectedDays > 1 ? 's' : '');

                const params =

                    new URLSearchParams({

                        amount: grandTotal.toFixed(2),

                        equipment_id: equipmentId,

                        description: description,

                        start_date: startDateInput

                            ? startDateInput.value

                            : '',

                        days: selectedDays

                    });

                window.location.href =

                    "{{ route('payments.create') }}"

                    + '?'

                    + params.toString();

            }

        );

    }

    if (resetButton) {

        resetButton.addEventListener(

            'click',

            function () {

                if (startDateInput) {

                    startDateInput.value = '';

                }

                selectedDays = 1;

                durationButtons.forEach(

                    function (button, index) {

                        button.classList.remove(

                            'bg-green-600',

                            'text-white',

                            'border-green-600'

                        );

                        button.classList.add(

                            'border-gray-200',

                            'text-gray-600'

                        );

                        if (index === 0) {

                            button.classList.add(

                                'bg-green-600',

                                'text-white',

                                'border-green-600'

                            );

                            button.classList.remove(

                                'border-gray-200',

                                'text-gray-600'

                            );

                        }

                    }

                );

                if (bookingForm) {

                    bookingForm.classList.remove('hidden');

                }

                if (priceBreakdown) {

                    priceBreakdown.classList.remove('hidden');

                }

                if (bookingButton) {

                    bookingButton.classList.remove('hidden');

                }

                if (confirmationMessage) {

                    confirmationMessage.classList.add('hidden');

                }

                updatePriceCalculation();

            }

        );

    }

    updatePriceCalculation();

});

</script>

@endsection
