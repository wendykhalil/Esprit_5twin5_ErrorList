@extends('layouts.backend')
@section('title', $badge->nom.' - Badge')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <a href="{{ route('admin.badges.index') }}" class="text-sm text-amber-700">← Retour au catalogue</a>
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex gap-5">@if($badge->image)<img src="{{ str_starts_with($badge->image, 'images/') ? asset($badge->image) : Storage::url($badge->image) }}" alt="" class="h-24 w-24 rounded-xl object-cover">@else<span class="text-6xl">🏅</span>@endif<div><h2 class="text-2xl font-bold">{{ $badge->nom }}</h2><p class="mt-1 text-slate-500">{{ $badge->description }}</p><p class="mt-3 text-sm">Critère : {{ str_replace('_', ' ', $badge->critere) }} · Seuil : {{ $badge->seuil }} · {{ ucfirst($badge->niveau) }}</p></div></div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-6"><h3 class="text-lg font-bold">Utilisateurs récompensés</h3><div class="mt-4 space-y-3">@forelse($badge->users as $user)<div class="flex items-center justify-between border-b border-slate-100 pb-3"><span>{{ $user->name }} <span class="text-xs text-slate-500">{{ $user->pivot->date_obtention }}</span></span><form method="POST" action="{{ route('admin.badges.revoke', [$badge, $user]) }}">@csrf @method('DELETE')<button class="text-sm text-red-600">Retirer</button></form></div>@empty<p class="text-sm text-slate-500">Aucun utilisateur n’a encore obtenu ce badge.</p>@endforelse</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-6"><h3 class="text-lg font-bold">Attribution manuelle</h3><form class="mt-4 flex gap-3" method="POST" action="{{ route('admin.badges.assign', $badge) }}">@csrf<select name="user_id" required class="flex-1 rounded-lg border-slate-200">@foreach(\App\Models\User::orderBy('name')->get() as $user)<option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>@endforeach</select><button class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white">Attribuer</button></form></div>
</div>
@endsection
