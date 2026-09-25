@extends('layouts.backend')

@section('title', 'Utilisateurs - SolarShare Admin')

@section('content')

@php
    $avatarColors = [
        'AK' => 'bg-blue-500', 'MT' => 'bg-green-500', 'YB' => 'bg-amber-500',
        'FS' => 'bg-violet-500', 'MB' => 'bg-rose-500', 'SH' => 'bg-teal-500', 'AD' => 'bg-slate-700',
    ];
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Utilisateurs</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">{{ count($users) }} utilisateurs trouvés</p>
        </div>
    </div>

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
                    value="{{ $search }}"
                    placeholder="Rechercher un utilisateur..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700 placeholder-slate-400"
                    style="font-family: Outfit, sans-serif"
                />
            </div>
            <select
                name="role"
                class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white"
                style="font-family: Outfit, sans-serif"
            >
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
            <select
                name="status"
                class="px-3 py-2.5 text-sm font-medium border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white"
                style="font-family: Outfit, sans-serif"
            >
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
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
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Utilisateur</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden md:table-cell" style="font-family: Outfit, sans-serif">Email</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Rôle</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden lg:table-cell" style="font-family: Outfit, sans-serif">Inscription</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Statut</th>
                        <th class="text-right px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="{{ $avatarColors[$user['avatar']] ?? 'bg-slate-500' }} w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-700 flex-shrink-0" style="font-family: Outfit, sans-serif">
                                        {{ $user['avatar'] }}
                                    </div>
                                    <div>
                                        <p class="font-500 text-slate-800" style="font-family: Outfit, sans-serif">{{ $user['name'] }}</p>
                                        <p class="text-xs text-slate-400 md:hidden" style="font-family: Outfit, sans-serif">{{ $user['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600 hidden md:table-cell" style="font-family: Outfit, sans-serif">{{ $user['email'] }}</td>
                            <td class="px-5 py-4">
                                <x-backend.status-badge :status="$user['role']" />
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-xs hidden lg:table-cell" style="font-family: Outfit, sans-serif">{{ $user['joinDate'] }}</td>
                            <td class="px-5 py-4 hidden sm:table-cell">
                                <x-backend.status-badge :status="$user['status']" />
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                    <button class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400" style="font-family: Outfit, sans-serif">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
