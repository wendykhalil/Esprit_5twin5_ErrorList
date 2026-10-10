
@extends('layouts.backend')

@section('title', 'Guides d’utilisation - SolarShare Admin')

@section('content')

<div class="space-y-5" style="font-family: Outfit, sans-serif">

    {{-- Success message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                Guides d'utilisation
            </h2>

            <p class="text-slate-500 text-sm mt-1">
                {{ $guides->total() }} guide(s) trouvé(s)
            </p>
        </div>

        <a href="{{ route('admin.equipment-guides.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors shadow-sm self-start sm:self-auto">
            <span class="text-lg leading-none">+</span>
            Ajouter un guide
        </a>
    </div>

    {{-- Search and filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET"
              action="{{ route('admin.equipment-guides.index') }}"
              class="flex flex-col sm:flex-row gap-3">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Rechercher un guide ou un équipement..."
                class="flex-1 px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700"
            >

            <select name="status"
                    class="px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 focus:ring-2 focus:ring-amber-400">
                <option value="">Tous les statuts</option>
                <option value="draft" @selected(request('status') === 'draft')>
                    Brouillon
                </option>
                <option value="pending" @selected(request('status') === 'pending')>
                    En attente
                </option>
                <option value="published" @selected(request('status') === 'published')>
                    Publié
                </option>
            </select>

            <button type="submit"
                    class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors">
                Filtrer
            </button>

            <a href="{{ route('admin.equipment-guides.index') }}"
               class="px-4 py-2.5 border border-slate-200 text-slate-600 rounded-lg text-sm font-semibold hover:bg-slate-50 text-center transition-colors">
                Réinitialiser
            </a>
        </form>
    </div>

    {{-- Guides table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Guide
                        </th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Équipement
                        </th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">
                            Utilisation
                        </th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">
                            Difficulté
                        </th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($guides as $guide)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">
                                    {{ $guide->title }}
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    #{{ $guide->id }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-slate-700">
                                {{ $guide->equipment?->name ?? 'Équipement supprimé' }}
                            </td>

                            <td class="px-5 py-4 text-slate-600 hidden md:table-cell">
                                {{ $guide->usage_context }}
                            </td>

                            <td class="px-5 py-4 text-slate-600 hidden lg:table-cell">
                                @switch($guide->difficulty_level)
                                    @case('beginner')
                                        Débutant
                                        @break
                                    @case('intermediate')
                                        Intermédiaire
                                        @break
                                    @case('advanced')
                                        Avancé
                                        @break
                                    @default
                                        {{ $guide->difficulty_level }}
                                @endswitch
                            </td>

                            <td class="px-5 py-4">
                                @if($guide->status === 'published')
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                                        Publié
                                    </span>
                                @elseif($guide->status === 'pending')
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">
                                        En attente
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                        Brouillon
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- View --}}
                                    <a href="{{ route('admin.equipment-guides.show', $guide) }}"
                                       title="Voir"
                                       class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none"
                                             stroke="currentColor" stroke-width="2"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5s8.577 3.01 9.964 7.183c.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5s-8.577-3.01-9.964-7.178z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.equipment-guides.edit', $guide) }}"
                                       title="Modifier"
                                       class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none"
                                             stroke="currentColor" stroke-width="2"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </a>

                                    {{-- Delete --}}
                                    <form method="POST"
                                          action="{{ route('admin.equipment-guides.destroy', $guide) }}"
                                          onsubmit="return confirm('Supprimer définitivement ce guide ?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                title="Supprimer"
                                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none"
                                                 stroke="currentColor" stroke-width="2"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m5 4v7m4-7v7"/>
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                Aucun guide trouvé.
                                <a href="{{ route('admin.equipment-guides.create') }}"
                                   class="block mt-3 text-amber-600 font-semibold hover:underline">
                                    Créer votre premier guide
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($guides->hasPages())
        <div class="mt-4">
            {{ $guides->links() }}
        </div>
    @endif

</div>

@endsection
