@extends('layouts.backend')

@section('title', 'Tableau de bord - SolarShare Admin')

@section('content')

<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Bonjour, Administrateur 👋</h2>
        <p class="text-slate-500 text-sm mt-1" style="font-family: Outfit, sans-serif">Voici un résumé de votre plateforme.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <x-backend.stat-card
            label="Équipements"
            value="124"
            accent="solar"
            :trend="['value' => '+8 ce mois', 'positive' => true]"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>'
        />
        <x-backend.stat-card
            label="Utilisateurs"
            value="86"
            accent="blue"
            :trend="['value' => '+12 ce mois', 'positive' => true]"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
        />
        <x-backend.stat-card
            label="Locations"
            value="57"
            accent="eco"
            :trend="['value' => '+5 cette semaine', 'positive' => true]"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>'
        />
        <x-backend.stat-card
            label="Revenus"
            value="4 850 TND"
            accent="purple"
            :trend="['value' => '+18% vs mois dernier', 'positive' => true]"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        />
        <x-backend.stat-card
            label="Paiements"
            value="{{ $totalPayments }}"
            accent="blue"
            :trend="['value' => $paidPayments . ' payés', 'positive' => true]"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h.008v.008H5.75v-.008zm0 2h.008v.008H5.75V16zm0 2h.008v.008H5.75v-.008zm5-8h.008v.008H10.75v-.008zm0 2h.008v.008H10.75V16zm0 2h.008v.008H10.75v-.008zm5-8h.008v.008H15.75v-.008zm0 2h.008v.008H15.75V16zm0 2h.008v.008H15.75v-.008zm8-5.25c.085 0 .169.002.252.006.486.049.952.27 1.302.461l-1.432 1.901H19.5a.75.75 0 00-.75.75v7.5a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-6.75l1.432 1.901c-.35.191-.816.412-1.302.461-.083.004-.167.006-.252.006H3.75a2.25 2.25 0 01-2.25-2.25v-10.5a2.25 2.25 0 012.25-2.25h16.5a2.25 2.25 0 012.25 2.25v3.75" /></svg>'
        />
        <x-backend.stat-card
            label="Transactions"
            value="{{ $totalTransactions }}"
            accent="eco"
            :trend="['value' => $completedTransactions . ' complétées', 'positive' => true]"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 6.75V3m0 3.75h9m0-3.75v3.75m0 0H21M3.75 6.75h4.5m0 0h-1.5m1.5 0v10.5a1.5 1.5 0 01-1.5 1.5H2.25a1.5 1.5 0 01-1.5-1.5v-10.5m15 0h4.5m0 0h-1.5m1.5 0v10.5a1.5 1.5 0 01-1.5 1.5H12.75a1.5 1.5 0 01-1.5-1.5v-10.5" /></svg>'
        />
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-bold text-slate-800 text-base mb-4" style="font-family: Outfit, sans-serif">Actions rapides</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.equipments') }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Ajouter un équipement
            </a>
            <a href="{{ route('admin.users') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-600 hover:bg-slate-50 transition-colors" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Voir les utilisateurs
            </a>
            <a href="{{ route('admin.rentals') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-600 hover:bg-slate-50 transition-colors" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                Voir les locations
            </a>
            <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-600 hover:bg-slate-50 transition-colors" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h.008v.008H5.75v-.008zm0 2h.008v.008H5.75V16zm0 2h.008v.008H5.75v-.008zm5-8h.008v.008H10.75v-.008zm0 2h.008v.008H10.75V16zm0 2h.008v.008H10.75v-.008zm5-8h.008v.008H15.75v-.008zm0 2h.008v.008H15.75V16zm0 2h.008v.008H15.75v-.008zm8-5.25c.085 0 .169.002.252.006.486.049.952.27 1.302.461l-1.432 1.901H19.5a.75.75 0 00-.75.75v7.5a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-6.75l1.432 1.901c-.35.191-.816.412-1.302.461-.083.004-.167.006-.252.006H3.75a2.25 2.25 0 01-2.25-2.25v-10.5a2.25 2.25 0 012.25-2.25h16.5a2.25 2.25 0 012.25 2.25v3.75" />
                </svg>
                Voir les paiements
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-600 hover:bg-slate-50 transition-colors" style="font-family: Outfit, sans-serif">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 6.75V3m0 3.75h9m0-3.75v3.75m0 0H21M3.75 6.75h4.5m0 0h-1.5m1.5 0v10.5a1.5 1.5 0 01-1.5 1.5H2.25a1.5 1.5 0 01-1.5-1.5v-10.5m15 0h4.5m0 0h-1.5m1.5 0v10.5a1.5 1.5 0 01-1.5 1.5H12.75a1.5 1.5 0 01-1.5-1.5v-10.5" />
                </svg>
                Voir les transactions
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Recent Rentals --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base" style="font-family: Outfit, sans-serif">Locations récentes</h3>
                <a href="{{ route('admin.rentals') }}" class="text-sm text-amber-600 font-600 hover:text-amber-700 transition-colors" style="font-family: Outfit, sans-serif">
                    Voir tout →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-5 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Client</th>
                            <th class="text-left px-5 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Équipement</th>
                            <th class="text-left px-5 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider hidden md:table-cell" style="font-family: Outfit, sans-serif">Prix</th>
                            <th class="text-left px-5 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rentals as $rental)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 font-500 text-slate-800" style="font-family: Outfit, sans-serif">{{ $rental['client'] }}</td>
                                <td class="px-5 py-3.5 text-slate-600 hidden sm:table-cell truncate max-w-35" style="font-family: Outfit, sans-serif">{{ $rental['equipment'] }}</td>
                                <td class="px-5 py-3.5 text-slate-700 font-600 hidden md:table-cell" style="font-family: Outfit, sans-serif">{{ $rental['price'] }} TND</td>
                                <td class="px-5 py-3.5">
                                    <x-backend.status-badge :status="$rental['status']" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Equipment --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base" style="font-family: Outfit, sans-serif">Équipements récents</h3>
                <a href="{{ route('admin.equipments') }}" class="text-sm text-amber-600 font-600 hover:text-amber-700 transition-colors" style="font-family: Outfit, sans-serif">
                    Voir tout →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($equipments as $equipment)
                    <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors">
                        <img
                            src="{{ $equipment['image'] }}"
                            alt="{{ $equipment['name'] }}"
                            class="w-10 h-10 rounded-lg object-cover bg-slate-100 shrink-0"
                        />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-500 text-slate-800" style="font-family: Outfit, sans-serif">{{ $equipment['name'] }}</p>
                            <p class="text-xs text-slate-500" style="font-family: Outfit, sans-serif">{{ $equipment['type'] }} · {{ $equipment['price'] }} TND/j</p>
                        </div>
                        <x-backend.status-badge :status="$equipment['available'] ? 'Disponible' : 'Indisponible'" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
