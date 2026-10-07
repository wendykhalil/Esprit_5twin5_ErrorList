@extends('layouts.frontend')

@section('title', 'Équipements - SolarShare')

@section('content')

<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>
                    <h1
                        class="text-3xl font-bold text-green-900 mb-2"
                        style="font-family: Fraunces, Georgia, serif"
                    >
                        Équipements disponibles
                    </h1>

                    <p class="text-gray-500">
                        {{ $equipments->total() }}
                        {{ $equipments->total() > 1 ? 'équipements trouvés' : 'équipement trouvé' }}
                    </p>
                </div>


                @auth
                    <a
                        href="{{ route('equipments.create') }}"
                        class="inline-flex items-center justify-center
                               px-5 py-3 bg-green-600 hover:bg-green-700
                               text-white font-semibold rounded-xl transition"
                    >
                        + Publier un équipement
                    </a>
                @endauth

            </div>


            {{-- Search + Sort --}}
            <div class="flex flex-col sm:flex-row gap-3 mt-6">

                <div class="flex-1 relative">

                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2
                               w-5 h-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0
                               11-14 0 7 7 0 0114 0z"
                        />
                    </svg>

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Rechercher un équipement..."
                        value="{{ request('q') }}"
                        class="w-full pl-10 pr-4 py-3
                               border border-gray-200 rounded-xl
                               text-sm focus:outline-none
                               focus:ring-2 focus:ring-green-400"
                    >

                </div>


                <select
                    id="sortSelect"
                    class="sm:w-52 px-4 py-3 border border-gray-200
                           rounded-xl text-sm text-gray-600
                           focus:outline-none focus:ring-2
                           focus:ring-green-400 bg-white"
                >
                    <option value="">
                        Plus récents
                    </option>

                    <option
                        value="price_asc"
                        {{ request('sort') === 'price_asc' ? 'selected' : '' }}
                    >
                        Prix croissant
                    </option>

                    <option
                        value="price_desc"
                        {{ request('sort') === 'price_desc' ? 'selected' : '' }}
                    >
                        Prix décroissant
                    </option>
                </select>

            </div>

        </div>
    </div>



    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Success --}}
        @if(session('success'))

            <div
                class="mb-6 bg-green-50 border border-green-200
                       text-green-800 px-5 py-4 rounded-xl"
            >
                {{ session('success') }}
            </div>

        @endif


        <div class="flex flex-col lg:flex-row gap-8">

            {{-- ================================================= --}}
            {{-- FILTERS --}}
            {{-- ================================================= --}}

            <aside class="lg:w-64 shrink-0">

                <div
                    class="bg-white rounded-2xl border border-green-100
                           p-6 space-y-6 sticky top-24"
                >

                    <h2 class="font-bold text-green-900 text-lg">
                        Filtres
                    </h2>


                    {{-- Category --}}
                    <div>

                        <label
                            class="block text-sm font-semibold
                                   text-gray-700 mb-3"
                        >
                            Catégorie
                        </label>


                        <div class="space-y-2">

                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    name="categoryFilter"
                                    value=""
                                    {{ request('category') ? '' : 'checked' }}
                                    class="accent-green-600"
                                >

                                <span class="text-sm text-gray-600">
                                    Toutes
                                </span>
                            </label>


                            @foreach($categories as $cat)

                                <label
                                    class="flex items-center gap-2 cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        name="categoryFilter"
                                        value="{{ $cat->id }}"
                                        {{ (string) request('category') === (string) $cat->id ? 'checked' : '' }}
                                        class="accent-green-600"
                                    >

                                    <span class="text-sm text-gray-600">
                                        {{ $cat->name }}
                                    </span>
                                </label>

                            @endforeach

                        </div>

                    </div>


                    {{-- Location --}}
                    <div>

                        <label
                            for="locationSelect"
                            class="block text-sm font-semibold
                                   text-gray-700 mb-2"
                        >
                            Ville
                        </label>


                        <select
                            id="locationSelect"
                            class="w-full px-3 py-2.5 border
                                   border-gray-200 rounded-xl text-sm
                                   focus:outline-none focus:ring-2
                                   focus:ring-green-400 bg-white"
                        >

                            <option value="">
                                Toutes les villes
                            </option>

                            @foreach($locations as $location)

                                <option
                                    value="{{ $location }}"
                                    {{ request('location') === $location ? 'selected' : '' }}
                                >
                                    {{ $location }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Maximum price --}}
                    <div>

                        <label
                            for="maxPriceInput"
                            class="block text-sm font-semibold
                                   text-gray-700 mb-2"
                        >
                            Prix maximum
                        </label>

                        <div class="relative">

                            <input
                                type="number"
                                id="maxPriceInput"
                                min="0"
                                step="1"
                                placeholder="Ex: 100"
                                value="{{ request('maxPrice') }}"
                                class="w-full px-3 py-2.5 pr-14
                                       border border-gray-200 rounded-xl
                                       text-sm focus:outline-none
                                       focus:ring-2 focus:ring-green-400"
                            >

                            <span
                                class="absolute right-3 top-2.5
                                       text-sm text-gray-400"
                            >
                                TND
                            </span>

                        </div>

                    </div>


                    {{-- Availability --}}
                    <div>

                        <label
                            class="flex items-center gap-3 cursor-pointer"
                        >

                            <input
                                type="checkbox"
                                id="availabilityCheckbox"
                                {{ request()->boolean('available') ? 'checked' : '' }}
                                class="w-4 h-4 accent-green-600"
                            >

                            <span
                                class="text-sm font-semibold text-gray-700"
                            >
                                Disponible uniquement
                            </span>

                        </label>

                    </div>


                    {{-- Reset --}}
                    <a
                        href="{{ route('equipments.index') }}"
                        class="w-full py-2.5 text-sm text-green-600
                               border border-green-200 rounded-xl
                               hover:bg-green-50 transition-colors
                               font-medium text-center block"
                    >
                        Réinitialiser les filtres
                    </a>

                </div>

            </aside>



            {{-- ================================================= --}}
            {{-- EQUIPMENT LIST --}}
            {{-- ================================================= --}}

            <div class="flex-1">

                @if($equipments->count() === 0)

                    <div
                        class="bg-white border border-green-100
                               rounded-2xl text-center py-20 px-6"
                    >

                        <div class="text-6xl mb-4">
                            🔍
                        </div>

                        <h3
                            class="text-xl font-bold
                                   text-green-900 mb-2"
                        >
                            Aucun équipement trouvé
                        </h3>

                        <p class="text-gray-500 mb-6">
                            Aucun équipement ne correspond actuellement
                            à vos critères.
                        </p>


                        @if(request()->hasAny([
                            'q',
                            'category',
                            'location',
                            'maxPrice',
                            'available',
                            'sort'
                        ]))

                            <a
                                href="{{ route('equipments.index') }}"
                                class="inline-block px-5 py-3
                                       bg-green-600 hover:bg-green-700
                                       text-white rounded-xl
                                       font-semibold transition"
                            >
                                Réinitialiser les filtres
                            </a>

                        @elseif(auth()->check())

                            <a
                                href="{{ route('equipments.create') }}"
                                class="inline-block px-5 py-3
                                       bg-green-600 hover:bg-green-700
                                       text-white rounded-xl
                                       font-semibold transition"
                            >
                                Publier le premier équipement
                            </a>

                        @endif

                    </div>


                @else

                    <div
                        class="grid grid-cols-1
                               sm:grid-cols-2 xl:grid-cols-3 gap-6"
                    >

                        @foreach($equipments as $eq)

                            <a
                                href="{{ route('equipments.show', $eq) }}"
                                class="group bg-white rounded-2xl
                                       border border-green-100
                                       overflow-hidden shadow-sm
                                       hover:shadow-lg
                                       hover:-translate-y-1
                                       transition duration-200"
                            >

                                {{-- Image --}}
                                <div
                                    class="relative h-52 bg-green-50
                                           overflow-hidden"
                                >

                                    @if($eq->image)

                                        <img
                                            src="{{ asset('storage/' . $eq->image) }}"
                                            alt="{{ $eq->name }}"
                                            class="w-full h-full object-cover
                                                   group-hover:scale-105
                                                   transition duration-300"
                                        >

                                    @else

                                        <div
                                            class="w-full h-full flex
                                                   flex-col items-center
                                                   justify-center text-green-700"
                                        >
                                            <span class="text-5xl">
                                                ☀️
                                            </span>

                                            <span class="text-sm mt-2">
                                                Aucune photo
                                            </span>
                                        </div>

                                    @endif


                                    {{-- Availability --}}
                                    <div
                                        class="absolute top-3 right-3"
                                    >

                                        @if($eq->availability)

                                            <span
                                                class="bg-green-500 text-white
                                                       text-xs font-semibold
                                                       px-3 py-1 rounded-full"
                                            >
                                                Disponible
                                            </span>

                                        @else

                                            <span
                                                class="bg-gray-500 text-white
                                                       text-xs font-semibold
                                                       px-3 py-1 rounded-full"
                                            >
                                                Indisponible
                                            </span>

                                        @endif

                                    </div>

                                </div>


                                {{-- Information --}}
                                <div class="p-5">

                                    <p
                                        class="text-sm text-green-600
                                               font-semibold mb-2"
                                    >
                                        {{ $eq->category?->name ?? 'Sans catégorie' }}
                                    </p>


                                    <h2
                                        class="text-lg font-bold
                                               text-green-900 mb-2
                                               line-clamp-1"
                                    >
                                        {{ $eq->name }}
                                    </h2>


                                    <p
                                        class="text-sm text-gray-500
                                               line-clamp-2 min-h-[40px]
                                               mb-4"
                                    >
                                        {{ $eq->description }}
                                    </p>


                                    <div
                                        class="flex items-center
                                               justify-between gap-3
                                               pt-4 border-t
                                               border-gray-100"
                                    >

                                        <div>

                                            <p
                                                class="text-xs
                                                       text-gray-400"
                                            >
                                                {{ $eq->location }}
                                            </p>

                                            @if($eq->brand)

                                                <p
                                                    class="text-xs
                                                           text-gray-500 mt-1"
                                                >
                                                    {{ $eq->brand }}
                                                </p>

                                            @endif

                                        </div>


                                        <div class="text-right">

                                            <span
                                                class="text-xl font-bold
                                                       text-green-700"
                                            >
                                                {{ number_format((float) $eq->price_per_day, 2) }}
                                            </span>

                                            <span
                                                class="text-xs
                                                       text-gray-500"
                                            >
                                                TND/j
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </a>

                        @endforeach

                    </div>


                    {{-- Laravel Pagination --}}
                    @if($equipments->hasPages())

                        <div class="mt-10">

                            {{ $equipments->links() }}

                        </div>

                    @endif

                @endif

            </div>

        </div>

    </div>

</div>



{{-- ================================================= --}}
{{-- FILTER JAVASCRIPT --}}
{{-- ================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    function updateParameter(name, value) {

        const params =
            new URLSearchParams(window.location.search);

        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {

            params.delete(name);

        } else {

            params.set(name, value);

        }

        params.delete('page');

        const query = params.toString();

        window.location.href =
            '{{ route('equipments.index') }}'
            + (query ? '?' + query : '');

    }


    // Search
    const searchInput =
        document.getElementById('searchInput');

    searchInput?.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Enter') {

                event.preventDefault();

                updateParameter(
                    'q',
                    this.value.trim()
                );

            }

        }
    );


    // Sort
    document
        .getElementById('sortSelect')
        ?.addEventListener(
            'change',
            function () {

                updateParameter(
                    'sort',
                    this.value
                );

            }
        );


    // Location
    document
        .getElementById('locationSelect')
        ?.addEventListener(
            'change',
            function () {

                updateParameter(
                    'location',
                    this.value
                );

            }
        );


    // Maximum price
    const maxPriceInput =
        document.getElementById('maxPriceInput');

    maxPriceInput?.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Enter') {

                event.preventDefault();

                updateParameter(
                    'maxPrice',
                    this.value
                );

            }

        }
    );


    maxPriceInput?.addEventListener(
        'change',
        function () {

            updateParameter(
                'maxPrice',
                this.value
            );

        }
    );


    // Category
    document
        .querySelectorAll(
            'input[name="categoryFilter"]'
        )
        .forEach(function (radio) {

            radio.addEventListener(
                'change',
                function () {

                    updateParameter(
                        'category',
                        this.value
                    );

                }
            );

        });


    // Availability
    document
        .getElementById('availabilityCheckbox')
        ?.addEventListener(
            'change',
            function () {

                updateParameter(
                    'available',
                    this.checked ? '1' : ''
                );

            }
        );

});

</script>

@endsection