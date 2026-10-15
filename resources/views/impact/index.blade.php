@extends('layouts.frontend')
@section('title', 'Mon impact écologique')
@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8"><div class="mx-auto max-w-7xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-green-600">SolarShare</p><h1 class="mt-2 text-4xl font-bold text-slate-900">Mon impact écologique</h1><p class="mt-2 text-slate-500">Suivez l’énergie produite et le CO2 évité grâce à vos locations.</p></div>
        <a href="{{ route('impact.create') }}" class="rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">+ Déclarer l’impact</a>
    </div>
    @if(session('success'))<div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">{{ session('success') }}</div>@endif
    <div class="mb-8 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-green-900 p-6 text-white"><p class="text-sm text-green-100">Total kWh produits</p><p class="mt-2 text-3xl font-bold">{{ number_format($totalKwh, 2) }} kWh</p></div>
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200"><p class="text-sm text-slate-500">CO2 évité</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($totalCo2, 2) }} kg</p></div>
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-200"><p class="text-sm text-slate-500">Badges obtenus</p><p class="mt-2 text-3xl font-bold text-amber-600">{{ $badges->count() }}</p></div>
    </div>
    <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="text-lg font-bold text-slate-900">Mes badges</h2><div class="mt-4 flex flex-wrap gap-3">
        @forelse($badges as $badge)<span class="rounded-full px-4 py-2 text-sm font-semibold {{ $badge->niveau === 'or' ? 'bg-yellow-100 text-yellow-800' : ($badge->niveau === 'argent' ? 'bg-slate-200 text-slate-700' : 'bg-orange-100 text-orange-800') }}">{{ $badge->nom }} · {{ ucfirst($badge->niveau) }}</span>@empty<span class="text-sm text-slate-500">Aucun badge pour le moment.</span>@endforelse
    </div></div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left"><thead class="bg-slate-50"><tr class="text-xs uppercase tracking-wider text-slate-500"><th class="px-6 py-4">Équipement</th><th class="px-6 py-4">Période</th><th class="px-6 py-4">Heures</th><th class="px-6 py-4">kWh</th><th class="px-6 py-4">CO2 évité</th><th class="px-6 py-4">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
    @forelse($releves as $releve)<tr class="hover:bg-green-50/40"><td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ $releve->reservation->equipment_label }}</td><td class="px-6 py-4 text-sm text-slate-600">{{ $releve->reservation->date_debut->format('d/m/Y') }} → {{ $releve->reservation->date_fin->format('d/m/Y') }}</td><td class="px-6 py-4 text-sm">{{ number_format($releve->heures_utilisation, 2) }}</td><td class="px-6 py-4 text-sm font-semibold">{{ number_format($releve->kwh_estimes, 2) }}</td><td class="px-6 py-4 text-sm">{{ number_format($releve->co2_evite_kg, 2) }} kg</td><td class="px-6 py-4"><div class="flex gap-2"><a class="text-sm font-semibold text-green-700" href="{{ route('impact.show', $releve) }}">Voir</a><a class="text-sm font-semibold text-slate-600" href="{{ route('impact.edit', $releve) }}">Modifier</a><form method="POST" action="{{ route('impact.destroy', $releve) }}" onsubmit="return confirm('Supprimer ce relevé ?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-600">Supprimer</button></form></div></td></tr>
    @empty<tr><td colspan="6" class="px-6 py-16 text-center"><p class="font-semibold text-slate-800">Vous n’avez pas encore de relevé.</p><a href="{{ route('impact.create') }}" class="mt-5 inline-flex rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white">Déclarer mon premier impact</a></td></tr>@endforelse
    </tbody></table></div>@if($releves->hasPages())<div class="border-t px-6 py-4">{{ $releves->links() }}</div>@endif</div>
</div></div>
@endsection
