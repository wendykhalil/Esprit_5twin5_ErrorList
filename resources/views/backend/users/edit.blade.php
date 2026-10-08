@extends('layouts.backend')

@section('title', 'Modifier '.$user->name)

@section('content')

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-amber-700 hover:text-amber-800">← Fiche utilisateur</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-2" style="font-family: Outfit, sans-serif">Modifier l'utilisateur</h2>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4" role="alert">
            <p class="font-semibold text-red-800 mb-2">Veuillez corriger les champs suivants :</p>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nom</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @else border-slate-200 @enderror"
                >
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('email') border-red-400 @else border-slate-200 @enderror"
                >
                @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-slate-700 mb-2">Rôle</label>
                <select
                    id="role"
                    name="role"
                    class="w-full px-4 py-3 border rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('role') border-red-400 @else border-slate-200 @enderror"
                >
                    @foreach($assignableRoles as $roleCase)
                        <option value="{{ $roleCase->value }}" @selected(old('role', $user->role) === $roleCase->value)>
                            {{ $roleCase->label() }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">
                    Prestataire : le profil doit être <strong>approuvé</strong> dans le menu Prestataires avant d'assigner ce rôle.
                </p>
                @error('role')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="{{ route('admin.users.show', $user) }}" class="flex-1 text-center px-4 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Annuler
                </a>
                <button type="submit" class="flex-1 px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
