@extends('layouts.backend')
@section('title', 'Catalogue des badges - SolarShare Admin')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div><h2 class="text-2xl font-bold">Catalogue des badges</h2><p class="text-sm text-slate-500">Badges attribués automatiquement selon les critères d’impact.</p></div>
        <a href="{{ route('admin.badges.create') }}" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white">+ Ajouter un badge</a>
    </div>
    @if(session('success'))<div class="rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>@endif
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500"><th class="px-5 py-3">Badge</th><th class="px-5 py-3">Nom</th><th class="px-5 py-3">Niveau</th><th class="px-5 py-3">Critère</th><th class="px-5 py-3">Seuil</th><th class="px-5 py-3">Obtenu par</th><th class="px-5 py-3">État</th><th class="px-5 py-3 text-right">Actions</th></tr></thead>
        <tbody class="divide-y">@forelse($badges as $badge)<tr>
            <td class="px-5 py-4">@if($badge->image)<img src="{{ str_starts_with($badge->image, 'images/') ? asset($badge->image) : Storage::url($badge->image) }}" alt="" class="h-12 w-12 rounded-lg object-cover">@else<span class="text-2xl">🏅</span>@endif</td>
            <td class="px-5 py-4 font-semibold">{{ $badge->nom }}</td><td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs {{ $badge->niveau === 'or' ? 'bg-yellow-100 text-yellow-800' : ($badge->niveau === 'argent' ? 'bg-slate-200 text-slate-700' : 'bg-orange-100 text-orange-800') }}">{{ ucfirst($badge->niveau) }}</span></td>
            <td class="px-5 py-4">{{ str_replace('_', ' ', ucfirst($badge->critere)) }}</td><td class="px-5 py-4">{{ $badge->seuil }}</td><td class="px-5 py-4">{{ $badge->users_count }}</td>
            <td class="px-5 py-4">{{ $badge->actif ? 'Actif' : 'Inactif' }}</td><td class="px-5 py-4 text-right"><a class="mr-3 text-green-700" href="{{ route('admin.badges.show', $badge) }}">Voir</a><a class="mr-3 text-slate-600" href="{{ route('admin.badges.edit', $badge) }}">Modifier</a><form class="inline" method="POST" action="{{ route('admin.badges.destroy', $badge) }}" onsubmit="return confirm('Supprimer ce badge ?')">@csrf @method('DELETE')<button class="text-red-600">Supprimer</button></form></td>
        </tr>@empty<tr><td colspan="8" class="p-10 text-center text-slate-500">Aucun badge dans le catalogue.</td></tr>@endforelse</tbody></table></div>
        <div class="border-t p-4">{{ $badges->links() }}</div>
    </div>
</div>
@endsection
