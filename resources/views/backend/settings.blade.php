@extends('layouts.backend')

@section('title', 'Paramètres - SolarShare Admin')

@section('content')

<div class="space-y-6 max-w-3xl">
    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Paramètres</h2>
        <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">Gérez vos préférences d'administration.</p>
    </div>

    {{-- Account info --}}
    <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-base" style="font-family: Outfit, sans-serif">Informations du compte</h3>
            <p class="text-xs text-slate-400 mt-0.5" style="font-family: Outfit, sans-serif">Modifiez vos informations personnelles.</p>
        </div>
        <div class="p-6 space-y-5">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-500 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0" style="font-family: Outfit, sans-serif">
                    A
                </div>
                <div>
                    <p class="font-600 text-slate-800" style="font-family: Outfit, sans-serif">Administrateur</p>
                    <p class="text-sm text-slate-500" style="font-family: Outfit, sans-serif">admin@solarshare.tn</p>
                    <button class="mt-1.5 text-xs text-amber-600 font-600 hover:text-amber-700 transition-colors" style="font-family: Outfit, sans-serif">
                        Changer la photo
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Prénom</label>
                    <input type="text" value="Administrateur" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700" style="font-family: Outfit, sans-serif" />
                </div>
                <div>
                    <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Nom</label>
                    <input type="text" value="SolarShare" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700" style="font-family: Outfit, sans-serif" />
                </div>
                <div>
                    <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Email</label>
                    <input type="email" value="admin@solarshare.tn" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700" style="font-family: Outfit, sans-serif" />
                </div>
                <div>
                    <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Téléphone</label>
                    <input type="text" value="+216 71 000 000" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700" style="font-family: Outfit, sans-serif" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Mot de passe</label>
                <input type="password" value="••••••••" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-700" style="font-family: Outfit, sans-serif" />
            </div>
            <div class="flex justify-end">
                <button class="px-5 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors" style="font-family: Outfit, sans-serif">
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </section>

    {{-- Preferences --}}
    <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-base" style="font-family: Outfit, sans-serif">Préférences</h3>
            <p class="text-xs text-slate-400 mt-0.5" style="font-family: Outfit, sans-serif">Personnalisez votre interface.</p>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Langue</label>
                    <select class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white" style="font-family: Outfit, sans-serif">
                        <option value="fr">Français</option>
                        <option value="ar">Arabe</option>
                        <option value="en">English</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-600 text-slate-700 mb-1.5" style="font-family: Outfit, sans-serif">Fuseau horaire</label>
                    <select class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 text-slate-700 bg-white" style="font-family: Outfit, sans-serif">
                        <option value="Africa/Tunis">Africa/Tunis (UTC+1)</option>
                        <option value="Europe/Paris">Europe/Paris (UTC+2)</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="px-5 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors" style="font-family: Outfit, sans-serif">
                    Enregistrer
                </button>
            </div>
        </div>
    </section>

    {{-- Notifications --}}
    <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-base" style="font-family: Outfit, sans-serif">Notifications</h3>
            <p class="text-xs text-slate-400 mt-0.5" style="font-family: Outfit, sans-serif">Choisissez quand vous souhaitez être alerté.</p>
        </div>
        <div class="p-6 divide-y divide-slate-100">
            @php
                $notificationSettings = [
                    ['key' => 'newRental', 'label' => 'Nouvelle location', 'desc' => 'Recevoir une alerte à chaque nouvelle demande de location.', 'enabled' => true],
                    ['key' => 'newUser', 'label' => 'Nouvel utilisateur', 'desc' => 'Recevoir une alerte à chaque inscription.', 'enabled' => true],
                    ['key' => 'rentalEnd', 'label' => 'Fin de location', 'desc' => 'Recevoir une alerte à la fin d\'une location.', 'enabled' => false],
                    ['key' => 'weeklyReport', 'label' => 'Rapport hebdomadaire', 'desc' => 'Recevoir un résumé chaque semaine.', 'enabled' => true],
                ];
            @endphp
            @foreach($notificationSettings as $item)
                <div class="flex items-center justify-between py-4 first:pt-0 last:pb-0">
                    <div>
                        <p class="text-sm font-600 text-slate-800" style="font-family: Outfit, sans-serif">{{ $item['label'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5" style="font-family: Outfit, sans-serif">{{ $item['desc'] }}</p>
                    </div>
                    <button class="relative inline-flex w-11 h-6 items-center rounded-full transition-colors duration-200 flex-shrink-0 {{ $item['enabled'] ? 'bg-amber-500' : 'bg-slate-200' }} toggle-notification" data-key="{{ $item['key'] }}">
                        <span class="inline-block w-4 h-4 rounded-full bg-white shadow-sm transform transition-transform duration-200 {{ $item['enabled'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
            <button class="px-5 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors" style="font-family: Outfit, sans-serif">
                Enregistrer les préférences
            </button>
        </div>
    </section>

    {{-- Danger zone --}}
    <section class="bg-white rounded-xl border border-red-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-100 bg-red-50">
            <h3 class="font-bold text-red-700 text-base" style="font-family: Outfit, sans-serif">Zone de danger</h3>
            <p class="text-xs text-red-400 mt-0.5" style="font-family: Outfit, sans-serif">Ces actions sont irréversibles.</p>
        </div>
        <div class="p-6">
            <button class="px-5 py-2.5 bg-white border border-red-300 text-red-600 rounded-lg text-sm font-600 hover:bg-red-50 transition-colors" style="font-family: Outfit, sans-serif">
                Supprimer le compte administrateur
            </button>
        </div>
    </section>
</div>

<script>
    document.querySelectorAll('.toggle-notification').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('bg-amber-500');
            this.classList.toggle('bg-slate-200');
            const span = this.querySelector('span');
            span.classList.toggle('translate-x-6');
            span.classList.toggle('translate-x-1');
        });
    });
</script>

@endsection
