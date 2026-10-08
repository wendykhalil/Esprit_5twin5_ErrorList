@extends('layouts.backend')

@section('title', $user->name.' - Utilisateur')

@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.users') }}" class="text-sm text-amber-700 hover:text-amber-800">← Retour à la liste</a>
            <h2 class="text-2xl font-bold text-slate-900 mt-2" style="font-family: Outfit, sans-serif">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $user->email }}</p>
        </div>
        <a
            href="{{ route('admin.users.edit', $user) }}"
            class="inline-flex justify-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors"
        >
            Modifier
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-5 shadow-sm">
        <div class="flex items-center gap-4">
            @if($user->profile_photo)
                <img src="{{ $user->getProfilePhotoUrl() }}" alt="" class="w-16 h-16 rounded-full object-cover border border-slate-200">
            @else
                <div class="w-16 h-16 rounded-full bg-amber-500 flex items-center justify-center text-white text-lg font-bold">
                    {{ $user->getInitials() }}
                </div>
            @endif
            <div>
                <x-backend.status-badge :status="$user->roleLabel()" />
                <p class="text-xs text-slate-500 mt-2">Inscrit le {{ $user->created_at?->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-slate-500 font-medium">Téléphone</dt>
                <dd class="text-slate-900 mt-0.5">{{ $user->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 font-medium">Ville</dt>
                <dd class="text-slate-900 mt-0.5">{{ $user->city ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 font-medium">E-mail vérifié</dt>
                <dd class="mt-0.5">
                    <x-backend.status-badge :status="$user->email_verified_at ? 'Actif' : 'Inactif'" />
                </dd>
            </div>
            <div>
                <dt class="text-slate-500 font-medium">Identifiant</dt>
                <dd class="text-slate-900 mt-0.5">#{{ $user->id }}</dd>
            </div>
        </dl>

        @if($user->serviceProvider)
            <div class="border-t border-slate-100 pt-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Profil prestataire</h3>
                <p class="text-sm text-slate-600">{{ $user->serviceProvider->specialty }} — {{ ucfirst($user->serviceProvider->status) }}</p>
                <a href="{{ route('admin.service-providers.show', $user->serviceProvider) }}" class="inline-block mt-2 text-sm text-amber-700 hover:text-amber-800">
                    Voir le profil prestataire →
                </a>
            </div>
        @endif
    </div>

    @if(auth()->id() !== $user->id)
        <form
            method="POST"
            action="{{ route('admin.users.destroy', $user) }}"
            onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');"
            class="bg-white border border-red-200 rounded-xl p-5"
        >
            @csrf
            @method('DELETE')
            <p class="text-sm text-slate-600 mb-3">Suppression définitive du compte (si aucune donnée liée ne bloque).</p>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">
                Supprimer l'utilisateur
            </button>
        </form>
    @endif
</div>

@endsection
