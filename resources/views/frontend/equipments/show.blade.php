@extends('layouts.frontend')

@section('title', $equipment->name . ' - SolarShare')

@section('content')

<div class="min-h-screen bg-gray-50">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Back --}}
        <a
            href="{{ route('equipments.index') }}"
            class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 mb-8"
        >
            <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                />
            </svg>

            Retour aux équipements
        </a>


        {{-- Success message --}}
        @if(session('success'))

            <div
                class="mb-6 bg-green-50 border border-green-200
                       text-green-800 px-5 py-4 rounded-xl"
            >
                {{ session('success') }}
            </div>

        @endif


        <div class="grid lg:grid-cols-3 gap-10">

            {{-- ================================================= --}}
            {{-- MAIN CONTENT --}}
            {{-- ================================================= --}}

            <div class="lg:col-span-2 space-y-8">


                {{-- IMAGE --}}
                <div
                    class="relative bg-white rounded-2xl overflow-hidden
                           shadow-sm border border-green-100"
                >

                    @if($equipment->image)

                        <img
                            src="{{ asset('storage/' . $equipment->image) }}"
                            alt="{{ $equipment->name }}"
                            class="w-full h-96 object-cover bg-green-50"
                        >

                    @else

                        <div
                            class="w-full h-96 bg-green-50
                                   flex flex-col items-center justify-center
                                   text-green-700"
                        >
                            <div class="text-7xl mb-4">
                                ☀️
                            </div>

                            <p class="font-semibold">
                                Aucune photo disponible
                            </p>
                        </div>

                    @endif


                    {{-- Category + availability --}}
                    <div class="absolute top-4 left-4 flex flex-wrap gap-2">

                        <span
                            class="bg-white text-green-700 text-sm font-medium
                                   px-3 py-1 rounded-full border
                                   border-green-100 shadow-sm"
                        >
                            {{ $equipment->category?->name ?? 'Sans catégorie' }}
                        </span>


                        @if($equipment->availability)

                            <span
                                class="bg-green-500 text-white text-sm
                                       font-medium px-3 py-1 rounded-full"
                            >
                                Disponible
                            </span>

                        @else

                            <span
                                class="bg-gray-500 text-white text-sm
                                       font-medium px-3 py-1 rounded-full"
                            >
                                Indisponible
                            </span>

                        @endif


                        @if($equipment->status === 'inactive')

                            <span
                                class="bg-red-500 text-white text-sm
                                       font-medium px-3 py-1 rounded-full"
                            >
                                Inactif
                            </span>

                        @endif

                    </div>

                </div>


                {{-- DETAILS CARD --}}
                <div
                    class="bg-white rounded-2xl border
                           border-green-100 p-8 shadow-sm"
                >

                    {{-- Title --}}
                    <h1
                        class="text-4xl font-bold text-green-900 mb-6"
                        style="font-family: Fraunces, Georgia, serif"
                    >
                        {{ $equipment->name }}
                    </h1>


                    {{-- OWNER --}}
                    <div
                        class="flex items-center gap-4 pb-8 mb-8
                               border-b border-green-100"
                    >

                        <div
                            class="w-12 h-12 rounded-full bg-green-100
                                   text-green-700 font-bold flex
                                   items-center justify-center text-lg shrink-0"
                        >
                            {{ strtoupper(substr($equipment->user?->name ?? 'U', 0, 1)) }}
                        </div>


                        <div class="flex-1">

                            <p class="font-semibold text-green-900">
                                {{ $equipment->user?->name ?? 'Utilisateur SolarShare' }}
                            </p>

                            <p class="text-sm text-gray-600">
                                {{ $equipment->location }}

                                @if($equipment->user?->created_at)
                                    · Membre depuis
                                    {{ $equipment->user->created_at->format('Y') }}
                                @endif
                            </p>

                            <p class="text-xs text-green-600 mt-1">
                                Propriétaire de l'équipement
                            </p>

                        </div>


                        {{-- Owner actions --}}
                        @auth

                            @if(auth()->id() === $equipment->user_id)

                                <div class="flex gap-2">

                                    <a
                                        href="{{ route('equipments.edit', $equipment) }}"
                                        class="px-4 py-2 border border-blue-200
                                               text-blue-700 text-sm font-medium
                                               rounded-xl hover:bg-blue-50
                                               transition-colors"
                                    >
                                        Modifier
                                    </a>


                                    <form
                                        action="{{ route('equipments.destroy', $equipment) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Voulez-vous vraiment supprimer cet équipement ?'
                                        )"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="px-4 py-2 border border-red-200
                                                   text-red-600 text-sm font-medium
                                                   rounded-xl hover:bg-red-50
                                                   transition-colors"
                                        >
                                            Supprimer
                                        </button>

                                    </form>

                                </div>

                            @else

                                <button
                                    type="button"
                                    class="px-4 py-2 border border-green-200
                                           text-green-700 text-sm font-medium
                                           rounded-xl hover:bg-green-50
                                           transition-colors"
                                >
                                    Contacter
                                </button>

                            @endif

                        @endauth

                    </div>


                    {{-- LOCATION --}}
                    <div
                        class="flex items-center gap-2 pb-8 mb-8
                               border-b border-green-100"
                    >

                        <svg
                            class="w-5 h-5 text-green-600 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998
                                1.998 0 01-2.827 0l-4.244-4.243a8
                                8 0 1111.314 0z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0
                                016 0z"
                            />
                        </svg>

                        <span class="text-lg text-gray-700">
                            {{ $equipment->location }}
                        </span>

                    </div>


                    {{-- DESCRIPTION --}}
                    <div
                        class="pb-8 mb-8 border-b border-green-100"
                    >

                        <h2
                            class="text-xl font-bold text-green-900 mb-4"
                        >
                            Description
                        </h2>

                        <p
                            class="text-gray-600 leading-relaxed text-base"
                        >
                            {{ $equipment->description }}
                        </p>

                    </div>


                    {{-- SPECIFICATIONS --}}
                    <div>

                        <h2
                            class="text-xl font-bold text-green-900 mb-6"
                        >
                            Caractéristiques
                        </h2>


                        <div class="grid sm:grid-cols-2 gap-5">

                            {{-- Category --}}
                            <div
                                class="bg-green-50 rounded-xl p-6
                                       border border-green-100"
                            >
                                <p
                                    class="text-xs text-green-600 font-semibold
                                           uppercase mb-2 tracking-wide"
                                >
                                    Catégorie
                                </p>

                                <p
                                    class="text-lg font-semibold text-green-900"
                                >
                                    {{ $equipment->category?->name ?? 'Non définie' }}
                                </p>
                            </div>


                            {{-- Brand --}}
                            <div
                                class="bg-green-50 rounded-xl p-6
                                       border border-green-100"
                            >
                                <p
                                    class="text-xs text-green-600 font-semibold
                                           uppercase mb-2 tracking-wide"
                                >
                                    Marque
                                </p>

                                <p
                                    class="text-lg font-semibold text-green-900"
                                >
                                    {{ $equipment->brand ?: 'Non renseignée' }}
                                </p>
                            </div>


                            {{-- Power --}}
                            <div
                                class="bg-green-50 rounded-xl p-6
                                       border border-green-100"
                            >
                                <p
                                    class="text-xs text-green-600 font-semibold
                                           uppercase mb-2 tracking-wide"
                                >
                                    Puissance
                                </p>

                                <p
                                    class="text-lg font-semibold text-green-900"
                                >
                                    @if($equipment->power)
                                        {{ number_format((float)$equipment->power, 0) }} W
                                    @else
                                        Non renseignée
                                    @endif
                                </p>
                            </div>


                            {{-- Capacity --}}
                            <div
                                class="bg-green-50 rounded-xl p-6
                                       border border-green-100"
                            >
                                <p
                                    class="text-xs text-green-600 font-semibold
                                           uppercase mb-2 tracking-wide"
                                >
                                    Capacité
                                </p>

                                <p
                                    class="text-lg font-semibold text-green-900"
                                >
                                    @if($equipment->capacity)
                                        {{ number_format((float)$equipment->capacity, 0) }} Wh
                                    @else
                                        Non renseignée
                                    @endif
                                </p>
                            </div>


                            {{-- Condition --}}
                            <div
                                class="bg-green-50 rounded-xl p-6
                                       border border-green-100"
                            >
                                <p
                                    class="text-xs text-green-600 font-semibold
                                           uppercase mb-2 tracking-wide"
                                >
                                    État
                                </p>

                                <p
                                    class="text-lg font-semibold text-green-900"
                                >
                                    @switch($equipment->condition)

                                        @case('excellent')
                                            Excellent
                                            @break

                                        @case('good')
                                            Bon
                                            @break

                                        @case('used')
                                            Utilisé
                                            @break

                                        @default
                                            {{ ucfirst($equipment->condition) }}

                                    @endswitch
                                </p>
                            </div>


                            {{-- Location --}}
                            <div
                                class="bg-green-50 rounded-xl p-6
                                       border border-green-100"
                            >
                                <p
                                    class="text-xs text-green-600 font-semibold
                                           uppercase mb-2 tracking-wide"
                                >
                                    Localisation
                                </p>

                                <p
                                    class="text-lg font-semibold text-green-900"
                                >
                                    {{ $equipment->location }}
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- RIGHT SIDE / RENTAL PREVIEW --}}
            {{-- ================================================= --}}

            <div class="lg:col-span-1">

                <div
                    class="bg-white rounded-2xl border
                           border-green-100 p-6 shadow-sm sticky top-24"
                >

                    {{-- PRICE --}}
                    <div class="mb-6">

                        <span
                            class="text-3xl font-bold text-green-700"
                            id="bookingPrice"
                        >
                            {{ number_format((float)$equipment->price_per_day, 2) }}
                            TND
                        </span>

                        <span class="text-gray-500">
                            / jour
                        </span>

                    </div>


                    {{-- Booking preview --}}
                    <div
                        id="bookingForm"
                        class="space-y-4 mb-6"
                    >

                        <div>

                            <label
                                for="startDate"
                                class="block text-sm font-semibold
                                       text-gray-700 mb-2"
                            >
                                Date de début
                            </label>

                            <input
                                type="date"
                                id="startDate"
                                min="{{ now()->format('Y-m-d') }}"
                                class="w-full px-4 py-3 border
                                       border-gray-200 rounded-xl text-sm
                                       focus:outline-none focus:ring-2
                                       focus:ring-green-400"
                            >

                        </div>


                        <div>

                            <label
                                class="block text-sm font-semibold
                                       text-gray-700 mb-2"
                            >
                                Durée de location
                            </label>

                            <div class="grid grid-cols-2 gap-2">

                                <button
                                    type="button"
                                    class="durationBtn py-2 px-3 text-sm
                                           rounded-xl border border-gray-200
                                           text-gray-600 hover:border-green-300
                                           hover:bg-green-50 transition-colors"
                                    data-days="1"
                                >
                                    1 jour
                                </button>

                                <button
                                    type="button"
                                    class="durationBtn py-2 px-3 text-sm
                                           rounded-xl border border-gray-200
                                           text-gray-600 hover:border-green-300
                                           hover:bg-green-50 transition-colors"
                                    data-days="3"
                                >
                                    3 jours
                                </button>

                                <button
                                    type="button"
                                    class="durationBtn py-2 px-3 text-sm
                                           rounded-xl border border-gray-200
                                           text-gray-600 hover:border-green-300
                                           hover:bg-green-50 transition-colors"
                                    data-days="7"
                                >
                                    1 semaine
                                </button>

                                <button
                                    type="button"
                                    class="durationBtn py-2 px-3 text-sm
                                           rounded-xl border border-gray-200
                                           text-gray-600 hover:border-green-300
                                           hover:bg-green-50 transition-colors"
                                    data-days="14"
                                >
                                    2 semaines
                                </button>

                            </div>

                        </div>

                    </div>


                    {{-- Price calculation --}}
                    <div
                        id="priceBreakdown"
                        class="border-t border-gray-100
                               pt-4 mb-6 space-y-2"
                    >

                        <div
                            class="flex justify-between
                                   text-sm text-gray-600"
                        >
                            <span id="priceCalc">
                                {{ number_format((float)$equipment->price_per_day, 2) }}
                                TND × 1 jour
                            </span>

                            <span id="priceTotal">
                                {{ number_format((float)$equipment->price_per_day, 2) }}
                                TND
                            </span>
                        </div>


                        <div
                            class="flex justify-between
                                   text-sm text-gray-600"
                        >
                            <span>
                                Frais de service
                            </span>

                            <span id="serviceFee">
                                1.00 TND
                            </span>
                        </div>


                        <div
                            class="flex justify-between font-bold
                                   text-green-900 pt-2
                                   border-t border-gray-100"
                        >
                            <span>
                                Total
                            </span>

                            <span id="grandTotal">
                                {{ number_format((float)$equipment->price_per_day + 1, 2) }}
                                TND
                            </span>
                        </div>

                    </div>


                    {{-- Button --}}
                    @if($equipment->availability)

                        @auth

                            @if(auth()->id() === $equipment->user_id)

                                <div
                                    class="bg-blue-50 border border-blue-100
                                           rounded-xl p-4 text-center"
                                >
                                    <p
                                        class="text-sm text-blue-800 font-medium"
                                    >
                                        Vous êtes le propriétaire de cet équipement.
                                    </p>
                                </div>

                            @else

                                <button
                                    id="bookingBtn"
                                    type="button"
                                    class="w-full py-4 font-bold rounded-xl
                                           transition-colors bg-green-600
                                           hover:bg-green-700 text-white"
                                >
                                    Réserver maintenant
                                </button>

                            @endif

                        @else

                            <a
                                href="{{ route('login') }}"
                                class="block text-center w-full py-4
                                       font-bold rounded-xl bg-green-600
                                       hover:bg-green-700 text-white
                                       transition-colors"
                            >
                                Se connecter pour réserver
                            </a>

                        @endauth

                    @else

                        <button
                            disabled
                            type="button"
                            class="w-full py-4 font-bold rounded-xl
                                   bg-gray-200 text-gray-400
                                   cursor-not-allowed"
                        >
                            Non disponible
                        </button>

                    @endif


                    <p
                        class="text-xs text-center text-gray-400 mt-3"
                    >
                        Le module de réservation sera connecté prochainement.
                    </p>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- RELATED EQUIPMENT --}}
    {{-- ================================================= --}}

    @if($related->count() > 0)

        <div
            class="bg-white py-16 mt-8
                   border-t border-green-100"
        >

            <div
                class="max-w-7xl mx-auto
                       px-4 sm:px-6 lg:px-8"
            >

                <h2
                    class="text-3xl font-bold
                           text-green-900 mb-10"
                >
                    Équipements similaires
                </h2>


                <div
                    class="grid grid-cols-1
                           sm:grid-cols-2
                           lg:grid-cols-3 gap-6"
                >

                    @foreach($related as $eq)

                        <a
                            href="{{ route('equipments.show', $eq) }}"
                            class="block bg-white border
                                   border-green-100 rounded-2xl
                                   overflow-hidden shadow-sm
                                   hover:shadow-md transition"
                        >

                            @if($eq->image)

                                <img
                                    src="{{ asset('storage/' . $eq->image) }}"
                                    alt="{{ $eq->name }}"
                                    class="w-full h-48 object-cover"
                                >

                            @else

                                <div
                                    class="h-48 bg-green-50 flex
                                           items-center justify-center
                                           text-5xl"
                                >
                                    ☀️
                                </div>

                            @endif


                            <div class="p-5">

                                <p
                                    class="text-sm text-green-600
                                           font-medium mb-1"
                                >
                                    {{ $eq->category?->name ?? 'Sans catégorie' }}
                                </p>

                                <h3
                                    class="text-lg font-bold
                                           text-green-900 mb-2"
                                >
                                    {{ $eq->name }}
                                </h3>

                                <div
                                    class="flex justify-between
                                           items-center"
                                >
                                    <span class="text-gray-500 text-sm">
                                        {{ $eq->location }}
                                    </span>

                                    <span
                                        class="font-bold text-green-700"
                                    >
                                        {{ number_format((float)$eq->price_per_day, 2) }}
                                        TND/j
                                    </span>
                                </div>

                            </div>

                        </a>

                    @endforeach

                </div>

            </div>

        </div>

    @endif

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const equipmentPrice =
        {{ (float) $equipment->price_per_day }};

    const durationButtons =
        document.querySelectorAll('.durationBtn');

    const startDateInput =
        document.getElementById('startDate');

    const bookingButton =
        document.getElementById('bookingBtn');

    let selectedDays = 1;


    function updatePriceCalculation() {

        const itemTotal =
            equipmentPrice * selectedDays;

        const serviceFee = 1;

        const grandTotal =
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


                alert(
                    'Le module de réservation sera connecté prochainement.'
                );

            }
        );

    }


    updatePriceCalculation();

});
</script>

@endsection