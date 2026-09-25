@extends('layouts.frontend')

@section('title', 'Équipements - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <div class="bg-white border-b border-green-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h1 class="text-3xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">
                    Équipements disponibles
                </h1>
                <p class="text-gray-500">{{ count($filtered) }} équipements trouvés</p>

                {{-- Search bar --}}
                <div class="flex flex-col sm:flex-row gap-3 mt-6">
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Rechercher un équipement..."
                            value="{{ $query }}"
                            id="searchInput"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                        />
                    </div>
                    <select
                        id="sortSelect"
                        class="sm:w-52 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white"
                    >
                        <option value="">Plus populaires</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Mieux notés</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Filters sidebar --}}
                <aside class="lg:w-64 shrink-0">
                    <div class="bg-white rounded-2xl border border-green-100 p-6 space-y-6 sticky top-24">
                        <h2 class="font-bold text-green-900 text-lg">Filtres</h2>

                        {{-- Category --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Catégorie</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="categoryFilter" value="" {{ $category === '' ? 'checked' : '' }} class="accent-green-600" />
                                    <span class="text-sm text-gray-600">Toutes</span>
                                </label>
                                @foreach($categories as $cat)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="categoryFilter" value="{{ $cat['label'] }}" {{ $category === $cat['label'] ? 'checked' : '' }} class="accent-green-600" />
                                        <span class="text-sm text-gray-600">{{ $cat['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Location --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ville</label>
                            <select
                                id="locationSelect"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white"
                            >
                                <option value="">Toutes les villes</option>
                                <option value="Tunis" {{ $location === 'Tunis' ? 'selected' : '' }}>Tunis</option>
                                <option value="Ariana" {{ $location === 'Ariana' ? 'selected' : '' }}>Ariana</option>
                                <option value="Sousse" {{ $location === 'Sousse' ? 'selected' : '' }}>Sousse</option>
                                <option value="Hammamet" {{ $location === 'Hammamet' ? 'selected' : '' }}>Hammamet</option>
                                <option value="Sfax" {{ $location === 'Sfax' ? 'selected' : '' }}>Sfax</option>
                            </select>
                        </div>

                        {{-- Price --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Prix max: <span class="text-green-600" id="priceDisplay">{{ $maxPrice }} TND/jour</span>
                            </label>
                            <input
                                type="range"
                                id="priceSlider"
                                min="10"
                                max="200"
                                step="5"
                                value="{{ $maxPrice }}"
                                class="w-full accent-green-600"
                            />
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>10 TND</span>
                                <span>200 TND</span>
                            </div>
                        </div>

                        {{-- Availability --}}
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div
                                    id="availabilityToggle"
                                    class="w-11 h-6 rounded-full transition-colors relative cursor-pointer {{ $available ? 'bg-green-500' : 'bg-gray-200' }}"
                                >
                                    <div class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $available ? 'translate-x-5' : 'translate-x-0.5' }}" />
                                </div>
                                <span class="text-sm font-semibold text-gray-700">Disponible uniquement</span>
                            </label>
                        </div>

                        <div class="pt-2"></div>

                        <a
                            href="{{ route('equipments.index') }}"
                            class="w-full py-2.5 text-sm text-green-600 border border-green-200 rounded-xl hover:bg-green-50 transition-colors font-medium text-center block"
                        >
                            Réinitialiser les filtres
                        </a>
                    </div>
                </aside>

                {{-- Grid --}}
                <div class="flex-1">
                    @if(count($equipments) === 0)
                        <div class="text-center py-24">
                            <div class="text-6xl mb-4">🔍</div>
                            <h3 class="text-xl font-bold text-green-900 mb-2">Aucun équipement trouvé</h3>
                            <p class="text-gray-500">Essayez de modifier vos critères de recherche.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach($equipments as $eq)
                                <x-frontend.equipment-card :equipment="$eq" />
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($totalPages > 1)
                            <div class="flex justify-center items-center gap-2 mt-10">
                                @if($page > 1)
                                    <a
                                        href="{{ route('equipments.index', array_merge(request()->query(), ['page' => $page - 1])) }}"
                                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
                                    >
                                        Précédent
                                    </a>
                                @else
                                    <button
                                        disabled
                                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium opacity-40"
                                    >
                                        Précédent
                                    </button>
                                @endif

                                @for($p = 1; $p <= $totalPages; $p++)
                                    @if($p === $page)
                                        <button
                                            disabled
                                            class="w-10 h-10 rounded-xl text-sm font-medium bg-green-600 text-white"
                                        >
                                            {{ $p }}
                                        </button>
                                    @else
                                        <a
                                            href="{{ route('equipments.index', array_merge(request()->query(), ['page' => $p])) }}"
                                            class="w-10 h-10 rounded-xl text-sm font-medium border border-gray-200 hover:bg-gray-50 flex items-center justify-center"
                                        >
                                            {{ $p }}
                                        </a>
                                    @endif
                                @endfor

                                @if($page < $totalPages)
                                    <a
                                        href="{{ route('equipments.index', array_merge(request()->query(), ['page' => $page + 1])) }}"
                                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
                                    >
                                        Suivant
                                    </a>
                                @else
                                    <button
                                        disabled
                                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium opacity-40"
                                    >
                                        Suivant
                                    </button>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sortSelect')?.addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', this.value);
            params.set('page', '1');
            window.location.search = params.toString();
        });

        document.getElementById('locationSelect')?.addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            if (this.value) params.set('location', this.value);
            else params.delete('location');
            params.set('page', '1');
            window.location.search = params.toString();
        });

        document.getElementById('priceSlider')?.addEventListener('change', function() {
            document.getElementById('priceDisplay').textContent = this.value + ' TND/jour';
            const params = new URLSearchParams(window.location.search);
            params.set('maxPrice', this.value);
            params.set('page', '1');
            window.location.search = params.toString();
        });

        document.querySelectorAll('input[name="categoryFilter"]')?.forEach(radio => {
            radio.addEventListener('change', function() {
                const params = new URLSearchParams(window.location.search);
                if (this.value) params.set('category', this.value);
                else params.delete('category');
                params.set('page', '1');
                window.location.search = params.toString();
            });
        });

        document.getElementById('availabilityToggle')?.addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('available')) params.delete('available');
            else params.set('available', '1');
            params.set('page', '1');
            window.location.search = params.toString();
        });

        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const params = new URLSearchParams(window.location.search);
                if (this.value) params.set('q', this.value);
                else params.delete('q');
                params.set('page', '1');
                window.location.search = params.toString();
            }
        });
    </script>

@endsection
