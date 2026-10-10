@extends('layouts.frontend')

@section('title', 'Équipements - SolarShare')

@section('content')
<div class="min-h-screen bg-slate-50">
    {{-- Page header and controls --}}
    <header class="bg-white border-b border-green-100">
        <div class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-10 2xl:px-12 py-7 lg:py-9">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-600 mb-2">Explorer SolarShare</p>
                    <h1 class="text-3xl lg:text-4xl font-bold text-green-950" style="font-family: Fraunces, Georgia, serif">Équipements disponibles</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ $equipments->total() }} {{ $equipments->total() > 1 ? 'équipements trouvés' : 'équipement trouvé' }} pour vos projets énergétiques</p>
                </div>
                @if(request()->hasAny(['q', 'category', 'location', 'maxPrice', 'available', 'sort']))
                    <a href="{{ route('equipments.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-900 underline underline-offset-4">Effacer tous les filtres</a>
                @endif
            </div>

            <div class="mt-7 grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_220px] gap-3">
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke-width="1.8"/><path d="m20 20-4-4" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <input type="search" id="searchInput" aria-label="Rechercher un équipement" placeholder="Rechercher un équipement, une marque..." value="{{ request('q') }}" class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:bg-white transition">
                </div>
                <select id="sortSelect" aria-label="Trier les équipements" class="h-12 w-full px-4 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="" {{ request('sort', '') === '' ? 'selected' : '' }}>Plus récents</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                </select>
            </div>
        </div>
    </header>

    <main class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-10 2xl:px-12 py-7 lg:py-9">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl" role="status">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[280px_minmax(0,1fr)] xl:grid-cols-[300px_minmax(0,1fr)] gap-6 xl:gap-8 items-start">
            {{-- Filters sidebar --}}
            <aside class="bg-white rounded-2xl border border-green-100 shadow-sm p-5 sm:p-6 lg:sticky lg:top-24" aria-label="Filtres des équipements">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-green-950">Filtres</h2>
                    <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M7 12h10m-7 5h4"/></svg>
                </div>

                <div class="mt-5">
                    <h3 class="text-sm font-bold text-slate-800 mb-3">Catégorie</h3>
                    <div class="space-y-2.5">
                        <label class="flex items-center gap-3 cursor-pointer group"><input type="radio" name="categoryFilter" value="" {{ request('category') ? '' : 'checked' }} class="w-4 h-4 accent-green-600"><span class="text-sm text-slate-600 group-hover:text-green-700">Toutes les catégories</span></label>
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-3 cursor-pointer group"><input type="radio" name="categoryFilter" value="{{ $cat->id }}" {{ (string) request('category') === (string) $cat->id ? 'checked' : '' }} class="w-4 h-4 accent-green-600"><span class="text-sm text-slate-600 group-hover:text-green-700">{{ $cat->name }}</span></label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100">
                    <label for="locationSelect" class="block text-sm font-bold text-slate-800 mb-2">Ville</label>
                    <select id="locationSelect" class="w-full h-11 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">Toutes les villes</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('location') === $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100">
                    <label for="maxPriceInput" class="block text-sm font-bold text-slate-800 mb-2">Prix maximum / jour</label>
                    <div class="relative">
                        <input type="number" id="maxPriceInput" min="0" step="any" placeholder="Ex : 100" value="{{ request('maxPrice') }}" class="w-full h-11 pl-3 pr-16 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 pointer-events-none">TND</span>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100">
                    <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" id="availabilityCheckbox" {{ request()->boolean('available') ? 'checked' : '' }} class="w-4 h-4 accent-green-600"><span class="text-sm font-semibold text-slate-700">Disponible uniquement</span></label>
                </div>

                <a href="{{ route('equipments.index') }}" class="mt-6 flex items-center justify-center w-full h-11 rounded-xl border border-green-200 text-sm font-semibold text-green-700 hover:bg-green-50 transition">Réinitialiser les filtres</a>
            </aside>

            {{-- Catalogue --}}
            <section class="min-w-0" aria-label="Liste des équipements">
                @if($equipments->count() === 0)
                    <div class="bg-white border border-green-100 rounded-2xl text-center px-6 py-20 shadow-sm">
                        <div class="mx-auto mb-5 w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center text-3xl" aria-hidden="true">🔎</div>
                        <h2 class="text-xl font-bold text-green-950">Aucun équipement trouvé</h2>
                        <p class="mt-2 text-slate-500 text-sm">Essayez d'autres critères pour découvrir nos équipements.</p>
                        @if(request()->hasAny(['q', 'category', 'location', 'maxPrice', 'available', 'sort']))
                            <a href="{{ route('equipments.index') }}" class="inline-flex mt-6 px-6 py-3 rounded-xl bg-green-600 text-white font-semibold text-sm hover:bg-green-700">Réinitialiser les filtres</a>
                        @elseif(auth()->check() && auth()->user()->isAdmin())
                            <a href="{{ route('admin.equipments.create') }}" class="inline-flex mt-6 px-6 py-3 rounded-xl bg-green-600 text-white font-semibold text-sm hover:bg-green-700">Ajouter un équipement (admin)</a>
                        @endif
                    </div>
                @else
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-green-950">Découvrez les équipements</h2>
                            <p class="text-xs text-slate-500 mt-1">Sélectionnez un équipement pour consulter ses détails</p>
                        </div>
                        <span class="hidden sm:inline-flex px-3 py-1.5 bg-white border border-green-100 rounded-full text-xs font-semibold text-green-700">{{ $equipments->total() }} résultats</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5 lg:gap-6">
                        @foreach($equipments as $eq)
                            <a href="{{ route('equipments.show', $eq) }}" class="group flex flex-col min-w-0 bg-white rounded-2xl border border-green-100 shadow-sm hover:shadow-xl hover:border-green-200 hover:-translate-y-1 transition-all duration-200 overflow-hidden">
                                <div class="relative h-56 lg:h-60 bg-green-50 overflow-hidden shrink-0">
                                    @if($eq->image)
                                        <img src="{{ asset('storage/' . $eq->image) }}" alt="{{ $eq->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-green-50 to-emerald-50 text-green-700">
                                            <div class="w-20 h-20 bg-white/80 rounded-2xl flex items-center justify-center shadow-sm"><svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 16 5-4 4 3 3-3 6 5"/></svg></div>
                                            <span class="mt-3 text-xs font-medium text-green-700/80">Aucune photo disponible</span>
                                        </div>
                                    @endif
                                    <span class="absolute top-3 right-3 px-3 py-1.5 rounded-full text-xs font-bold shadow-sm {{ $eq->availability ? 'bg-green-600 text-white' : 'bg-slate-600 text-white' }}">{{ $eq->availability ? 'Disponible' : 'Indisponible' }}</span>
                                </div>

                                <div class="p-5 flex flex-col flex-1">
                                    <p class="text-xs font-bold uppercase tracking-wide text-green-600 mb-2">{{ $eq->category?->name ?? 'Sans catégorie' }}</p>
                                    <h3 class="text-lg font-bold text-green-950 leading-snug line-clamp-2 min-h-[48px] group-hover:text-green-700 transition-colors">{{ $eq->name }}</h3>
                                    <p class="mt-2 text-sm text-slate-500 leading-6 line-clamp-2 min-h-[48px]">{{ $eq->description ?: 'Découvrez les détails et caractéristiques de cet équipement.' }}</p>

                                    <div class="mt-auto pt-5">
                                        <div class="flex items-center gap-2 text-xs text-slate-500 mb-4 min-w-0">
                                            <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                            <span class="truncate">{{ $eq->location ?: 'Localisation non renseignée' }}</span>
                                            @if($eq->brand)<span class="text-slate-300">•</span><span class="truncate">{{ $eq->brand }}</span>@endif
                                        </div>
                                        <div class="flex items-end justify-between gap-2 border-t border-slate-100 pt-4">
                                            <div>
                                                <p class="text-[11px] text-slate-400 mb-1">À partir de</p>
                                                <span class="text-xl font-extrabold text-green-700">{{ number_format((float) $eq->price_per_day, 2, ',', ' ') }}</span>
                                                <span class="text-xs text-slate-500">TND/j</span>
                                            </div>
                                            <span class="w-10 h-10 rounded-xl bg-green-50 text-green-700 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors" aria-hidden="true"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if($equipments->hasPages())
                        <div class="mt-10">{{ $equipments->links() }}</div>
                    @endif
                @endif
            </section>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateParameter(name, value) {
        const params = new URLSearchParams(window.location.search);
        if (value === null || value === undefined || value === '') {
            params.delete(name);
        } else {
            params.set(name, value);
        }
        params.delete('page');
        const query = params.toString();
        window.location.href = @json(route('equipments.index')) + (query ? '?' + query : '');
    }

    document.getElementById('searchInput')?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            updateParameter('q', this.value.trim());
        }
    });
    document.getElementById('sortSelect')?.addEventListener('change', function () { updateParameter('sort', this.value); });
    document.getElementById('locationSelect')?.addEventListener('change', function () { updateParameter('location', this.value); });

    const maxPriceInput = document.getElementById('maxPriceInput');
    maxPriceInput?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') { event.preventDefault(); updateParameter('maxPrice', this.value); }
    });
    maxPriceInput?.addEventListener('change', function () { updateParameter('maxPrice', this.value); });

    document.querySelectorAll('input[name="categoryFilter"]').forEach(function (radio) {
        radio.addEventListener('change', function () { updateParameter('category', this.value); });
    });
    document.getElementById('availabilityCheckbox')?.addEventListener('change', function () {
        updateParameter('available', this.checked ? '1' : '');
    });
});
</script>
@endsection
