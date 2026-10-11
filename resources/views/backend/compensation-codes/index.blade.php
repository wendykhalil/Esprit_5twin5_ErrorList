@extends('layouts.backend')

@section('title', 'Codes de Compensation - SolarShare Admin')

@section('content')

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Codes de Compensation</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">{{ $compensationCodes->total() }} code(s) trouvé(s)</p>
        </div>
        <a
            href="{{ route('admin.compensation-codes.create') }}"
            class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors text-center"
            style="font-family: Outfit, sans-serif"
        >
            + Nouveau Code
        </a>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" style="font-family: Outfit, sans-serif">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" style="font-family: Outfit, sans-serif">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search', '') }}"
                    placeholder="Chercher par code ou email..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700 placeholder-slate-400"
                    style="font-family: Outfit, sans-serif"
                />
            </div>
            <select
                name="is_active"
                class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white"
                style="font-family: Outfit, sans-serif"
            >
                <option value="">Tous les statuts</option>
                <option value="1" @selected(request('is_active') === '1')>Actif</option>
                <option value="0" @selected(request('is_active') === '0')>Inactif</option>
            </select>
            <select
                name="type"
                class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white"
                style="font-family: Outfit, sans-serif"
            >
                <option value="">Tous les types</option>
                <option value="percentage" @selected(request('type') === 'percentage')>Pourcentage (%)</option>
                <option value="fixed" @selected(request('type') === 'fixed')>Fixe (TND)</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors" style="font-family: Outfit, sans-serif">
                Filtrer
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Code</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden md:table-cell" style="font-family: Outfit, sans-serif">Réduction</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden md:table-cell" style="font-family: Outfit, sans-serif">Client</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden lg:table-cell" style="font-family: Outfit, sans-serif">Utilisation</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Statut</th>
                        <th class="text-right px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($compensationCodes as $code)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex flex-col">
                                    <span class="font-mono font-bold text-amber-600">{{ $code->code }}</span>
                                    <span class="text-xs text-slate-500">{{ $code->created_at->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                @if($code->type === 'percentage')
                                    <span class="text-green-600 font-semibold">{{ $code->value }}%</span>
                                @else
                                    <span class="text-green-600 font-semibold">{{ number_format($code->value, 2) }} TND</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <div class="flex flex-col">
                                    <span class="text-slate-900">{{ $code->user?->name ?? 'N/A' }}</span>
                                    <span class="text-xs text-slate-500">{{ $code->user?->email ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <div class="flex flex-col">
                                    <span class="text-sm">{{ $code->uses_count }} / {{ $code->max_uses }}</span>
                                    <div class="w-16 h-1.5 bg-slate-200 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: {{ min(($code->uses_count / $code->max_uses) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden sm:table-cell">
                                <div class="flex items-center gap-2">
                                    @if($code->is_active)
                                        <span class="inline-block px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-600" style="font-family: Outfit, sans-serif">Actif</span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-full text-xs font-600" style="font-family: Outfit, sans-serif">Inactif</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.compensation-codes.show', $code->id) }}"
                                        class="px-3 py-1.5 text-xs font-600 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                                    >
                                        Voir
                                    </a>
                                    @if($code->uses_count === 0)
                                        <a
                                            href="{{ route('admin.compensation-codes.edit', $code->id) }}"
                                            class="px-3 py-1.5 text-xs font-600 text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors"
                                        >
                                            Éditer
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500" style="font-family: Outfit, sans-serif">
                                Aucun code de compensation trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($compensationCodes->hasPages())
        <div class="flex justify-center">
            {{ $compensationCodes->links() }}
        </div>
    @endif
</div>

@endsection
