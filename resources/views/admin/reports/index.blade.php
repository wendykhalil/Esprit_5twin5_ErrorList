@extends('layouts.backend')

@section('title', 'Générateur de rapports - SolarShare Admin')

@section('content')

<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Générateur de Rapports 📊</h2>
        <p class="text-slate-500 text-sm mt-1" style="font-family: Outfit, sans-serif">Générez des rapports détaillés avec analyses IA sur vos réservations, revenus et réclamations.</p>
    </div>

    {{-- Period Selection Form --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form action="{{ route('admin.reports.generate') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Period Type Selection --}}
            <div class="space-y-4">
                <label class="block text-sm font-semibold text-slate-900" style="font-family: Outfit, sans-serif">
                    Sélectionnez une période
                </label>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Week Option --}}
                    <label class="relative flex items-start gap-3 p-4 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-300 hover:bg-amber-50 transition-all group" style="font-family: Outfit, sans-serif">
                        <input type="radio" name="period" value="week" class="mt-1" checked>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900">Cette semaine</p>
                            <p class="text-xs text-slate-500 mt-1">Rapport des 7 derniers jours</p>
                        </div>
                    </label>

                    {{-- Month Option --}}
                    <label class="relative flex items-start gap-3 p-4 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-300 hover:bg-amber-50 transition-all group" style="font-family: Outfit, sans-serif">
                        <input type="radio" name="period" value="month" class="mt-1">
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900">Ce mois</p>
                            <p class="text-xs text-slate-500 mt-1">Du 1er au dernier jour du mois</p>
                        </div>
                    </label>

                    {{-- Year Option --}}
                    <label class="relative flex items-start gap-3 p-4 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-300 hover:bg-amber-50 transition-all group" style="font-family: Outfit, sans-serif">
                        <input type="radio" name="period" value="year" class="mt-1">
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900">Cette année</p>
                            <p class="text-xs text-slate-500 mt-1">Du 1er janvier à maintenant</p>
                        </div>
                    </label>

                    {{-- Custom Option --}}
                    <label class="relative flex items-start gap-3 p-4 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-300 hover:bg-amber-50 transition-all group" style="font-family: Outfit, sans-serif">
                        <input type="radio" name="period" value="custom" class="mt-1">
                        <div class="flex-1">
                            <p class="font-semibold text-slate-900">Personnalisé</p>
                            <p class="text-xs text-slate-500 mt-1">Choisir des dates spécifiques</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Custom Date Range (Hidden by default) --}}
            <div id="custom-dates" class="hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                            Date de début
                        </label>
                        <input type="date" id="start_date" name="start_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400" style="font-family: Outfit, sans-serif">
                        @error('start_date')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                            Date de fin
                        </label>
                        <input type="date" id="end_date" name="end_date" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400" style="font-family: Outfit, sans-serif">
                        @error('end_date')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Générer le rapport
                </button>
            </div>
        </form>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 p-5">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 text-blue-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-blue-900 text-sm" style="font-family: Outfit, sans-serif">Statistiques en temps réel</p>
                    <p class="text-xs text-blue-700 mt-1">Les données sont agrégées directement de votre base de données.</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 p-5">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 text-purple-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-purple-900 text-sm" style="font-family: Outfit, sans-serif">Analyse IA intégrée</p>
                    <p class="text-xs text-purple-700 mt-1">Ollama génère un résumé professionnel en français.</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 p-5">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 text-green-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-green-900 text-sm" style="font-family: Outfit, sans-serif">Optimisé pour la performance</p>
                    <p class="text-xs text-green-700 mt-1">Aucun problème N+1, requêtes optimisées.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle custom date inputs based on selected period
    document.querySelectorAll('input[name="period"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const customDates = document.getElementById('custom-dates');
            if (this.value === 'custom') {
                customDates.classList.remove('hidden');
            } else {
                customDates.classList.add('hidden');
            }
        });
    });
</script>

@endsection
