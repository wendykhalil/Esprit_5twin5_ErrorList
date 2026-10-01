@extends('layouts.backend')

@section('title', 'Équipements - SolarShare Admin')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

        <div>
            <h2
                class="text-2xl font-bold text-slate-900"
                style="font-family: Outfit, sans-serif"
            >
                Équipements
            </h2>

            <p
                class="text-slate-500 text-sm mt-0.5"
                style="font-family: Outfit, sans-serif"
            >
                {{ $equipments->total() }}
                {{ $equipments->total() > 1 ? 'équipements trouvés' : 'équipement trouvé' }}
            </p>
        </div>

        <a
            href="{{ route('equipments.create') }}"
            class="flex items-center gap-2 px-4 py-2.5
                   bg-amber-500 text-white rounded-lg
                   text-sm font-semibold hover:bg-amber-600
                   transition-colors shadow-sm
                   self-start sm:self-auto"
            style="font-family: Outfit, sans-serif"
        >
            <svg
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4.5v15m7.5-7.5h-15"
                />
            </svg>

            Ajouter un équipement
        </a>

    </div>


    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">

        <form
            method="GET"
            action="{{ route('admin.equipments') }}"
            class="flex flex-col sm:flex-row gap-3"
        >

            {{-- Search --}}
            <div class="relative flex-1">

                <svg
                    class="absolute left-3 top-1/2 -translate-y-1/2
                           w-4 h-4 text-slate-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5
                           7.5 0 105.196 5.196a7.5
                           7.5 0 0010.607 10.607z"
                    />
                </svg>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Rechercher un équipement, une marque ou un propriétaire..."
                    class="w-full pl-9 pr-4 py-2.5
                           text-sm font-medium border border-slate-200
                           rounded-lg focus:outline-none
                           focus:ring-2 focus:ring-amber-400
                           focus:border-transparent text-slate-700
                           placeholder-slate-400"
                    style="font-family: Outfit, sans-serif"
                >

            </div>


            {{-- Category --}}
            <select
                name="category"
                class="px-3 py-2.5 text-sm font-medium
                       border border-slate-200 rounded-lg
                       focus:outline-none focus:ring-2
                       focus:ring-amber-400 text-slate-700 bg-white"
                style="font-family: Outfit, sans-serif"
            >

                <option value="">
                    Toutes les catégories
                </option>

                @foreach($categories as $category)

                    <option
                        value="{{ $category->id }}"
                        {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}
                    >
                        {{ $category->name }}
                    </option>

                @endforeach

            </select>


            <button
                type="submit"
                class="px-4 py-2.5 bg-amber-500
                       text-white rounded-lg text-sm
                       font-semibold hover:bg-amber-600
                       transition-colors"
                style="font-family: Outfit, sans-serif"
            >
                Filtrer
            </button>


            <a
                href="{{ route('admin.equipments') }}"
                class="px-4 py-2.5 border border-slate-200
                       text-slate-600 rounded-lg text-sm
                       font-semibold hover:bg-slate-50
                       transition-colors text-center"
                style="font-family: Outfit, sans-serif"
            >
                Réinitialiser
            </a>

        </form>

    </div>


    {{-- Table --}}
    <div
        class="bg-white rounded-xl
               border border-slate-200 overflow-hidden"
    >

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr class="bg-slate-50 border-b border-slate-200">

                        <th
                            class="text-left px-5 py-3.5 text-xs
                                   font-semibold text-slate-500
                                   uppercase tracking-wider"
                            style="font-family: Outfit, sans-serif"
                        >
                            Équipement
                        </th>

                        <th
                            class="text-left px-5 py-3.5 text-xs
                                   font-semibold text-slate-500
                                   uppercase tracking-wider
                                   hidden md:table-cell"
                            style="font-family: Outfit, sans-serif"
                        >
                            Catégorie
                        </th>

                        <th
                            class="text-left px-5 py-3.5 text-xs
                                   font-semibold text-slate-500
                                   uppercase tracking-wider
                                   hidden lg:table-cell"
                            style="font-family: Outfit, sans-serif"
                        >
                            Propriétaire
                        </th>

                        <th
                            class="text-left px-5 py-3.5 text-xs
                                   font-semibold text-slate-500
                                   uppercase tracking-wider
                                   hidden sm:table-cell"
                            style="font-family: Outfit, sans-serif"
                        >
                            Prix/jour
                        </th>

                        <th
                            class="text-left px-5 py-3.5 text-xs
                                   font-semibold text-slate-500
                                   uppercase tracking-wider"
                            style="font-family: Outfit, sans-serif"
                        >
                            Disponibilité
                        </th>

                        <th
                            class="text-right px-5 py-3.5 text-xs
                                   font-semibold text-slate-500
                                   uppercase tracking-wider"
                            style="font-family: Outfit, sans-serif"
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                    @forelse($equipments as $equipment)

                        <tr class="hover:bg-slate-50 transition-colors">

                            {{-- Equipment --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    @if($equipment->image)

                                        <img
                                            src="{{ asset('storage/' . $equipment->image) }}"
                                            alt="{{ $equipment->name }}"
                                            class="w-10 h-10 rounded-lg
                                                   object-cover bg-slate-100
                                                   flex-shrink-0"
                                        >

                                    @else

                                        <div
                                            class="w-10 h-10 rounded-lg
                                                   bg-green-50 flex
                                                   items-center justify-center
                                                   flex-shrink-0"
                                        >
                                            ☀️
                                        </div>

                                    @endif


                                    <div>

                                        <p
                                            class="font-medium text-slate-800"
                                            style="font-family: Outfit, sans-serif"
                                        >
                                            {{ $equipment->name }}
                                        </p>


                                        @if($equipment->brand)

                                            <p
                                                class="text-xs text-slate-400"
                                                style="font-family: Outfit, sans-serif"
                                            >
                                                {{ $equipment->brand }}
                                            </p>

                                        @endif


                                        <p
                                            class="text-xs text-slate-400 md:hidden"
                                            style="font-family: Outfit, sans-serif"
                                        >
                                            {{ $equipment->category?->name ?? 'Sans catégorie' }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- Category --}}
                            <td
                                class="px-5 py-4 text-slate-600 hidden md:table-cell"
                                style="font-family: Outfit, sans-serif"
                            >
                                {{ $equipment->category?->name ?? 'Sans catégorie' }}
                            </td>


                            {{-- Owner --}}
                            <td
                                class="px-5 py-4 text-slate-600 hidden lg:table-cell"
                                style="font-family: Outfit, sans-serif"
                            >
                                {{ $equipment->user?->name ?? 'Utilisateur inconnu' }}
                            </td>


                            {{-- Price --}}
                            <td
                                class="px-5 py-4 font-semibold
                                       text-slate-800 hidden sm:table-cell"
                                style="font-family: Outfit, sans-serif"
                            >
                                {{ number_format((float) $equipment->price_per_day, 2) }}
                                TND
                            </td>


                            {{-- Availability --}}
                            <td class="px-5 py-4">

                                <x-backend.status-badge
                                    :status="$equipment->availability
                                        ? 'Disponible'
                                        : 'Indisponible'"
                                />

                            </td>


                            {{-- Actions --}}
                            <td class="px-5 py-4">

                                <div
                                    class="flex items-center justify-end gap-2"
                                >

                                    {{-- View --}}
                                    <a
                                        href="{{ route('equipments.show', $equipment) }}"
                                        class="p-1.5 text-slate-400
                                               hover:text-blue-600
                                               hover:bg-blue-50
                                               rounded-lg transition-colors"
                                        title="Voir"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.036 12.322a1.012
                                                   1.012 0 010-.639C3.423
                                                   7.51 7.36 4.5 12 4.5c4.638
                                                   0 8.573 3.007 9.963
                                                   7.178.07.207.07.431
                                                   0 .639C20.577 16.49
                                                   16.64 19.5 12 19.5c-4.638
                                                   0-8.573-3.007-9.963-7.178z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6
                                                   0 3 3 0 016 0z"
                                            />
                                        </svg>
                                    </a>


                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('equipments.edit', $equipment) }}"
                                        class="p-1.5 text-slate-400
                                               hover:text-amber-600
                                               hover:bg-amber-50
                                               rounded-lg transition-colors"
                                        title="Modifier"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688
                                                   a1.875 1.875 0 112.652
                                                   2.652L10.582 16.07a4.5
                                                   4.5 0 01-1.897 1.13L6
                                                   18l.8-2.685a4.5 4.5
                                                   0 011.13-1.897l8.932-8.931z"
                                            />
                                        </svg>
                                    </a>


                                    {{-- Delete --}}
                                    <form
                                        method="POST"
                                        action="{{ route('equipments.destroy', $equipment) }}"
                                        onsubmit="return confirm(
                                            'Voulez-vous vraiment supprimer cet équipement ?'
                                        )"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="p-1.5 text-slate-400
                                                   hover:text-red-600
                                                   hover:bg-red-50
                                                   rounded-lg transition-colors"
                                            title="Supprimer"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M14.74 9l-.346 9m-4.788
                                                       0L9.26 9m9.968-3.21c.342
                                                       .052.682.107 1.022.166M4.772
                                                       5.79l1.068 13.883A2.25
                                                       2.25 0 008.084
                                                       21.75h7.832a2.25 2.25
                                                       0 002.244-2.077L19.228
                                                       5.79"
                                                />
                                            </svg>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="text-center py-12 text-slate-400"
                                style="font-family: Outfit, sans-serif"
                            >
                                Aucun équipement trouvé.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Pagination --}}
    @if($equipments->hasPages())

        <div class="mt-4">

            {{ $equipments->links() }}

        </div>

    @endif

</div>

@endsection