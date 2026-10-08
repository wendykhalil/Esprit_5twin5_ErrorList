@extends('layouts.frontend')

@section('title', 'Demander une intervention - SolarShare')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Demander une intervention</h1>
        <p class="text-slate-500 mt-2" style="font-family: Outfit, sans-serif">Veuillez remplir ce formulaire. Votre demande sera envoyée avec le statut "En attente".</p>
    </div>

    {{-- Provider Info Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-slate-800 mb-4" style="font-family: Outfit, sans-serif">Prestataire sélectionné</h2>
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center text-white text-xl font-bold shrink-0">
                {{ substr($serviceProvider->user->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <p class="font-medium text-slate-900" style="font-family: Outfit, sans-serif">{{ $serviceProvider->user->name ?? 'Utilisateur inconnu' }}</p>
                <p class="text-sm text-slate-500" style="font-family: Outfit, sans-serif">{{ $serviceProvider->specialty }} • {{ $serviceProvider->location }}</p>
            </div>
            <div class="mt-2 sm:mt-0 sm:ml-auto">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-sm font-medium">
                    {{ $serviceProvider->hourly_rate ? number_format((float) $serviceProvider->hourly_rate, 2) . ' TND/h' : 'Tarif sur devis' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('service-requests.store', $serviceProvider) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        @csrf

        <div class="space-y-6">
            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Titre de la demande <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Requested Date --}}
            <div>
                <label for="requested_date" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Date souhaitée <span class="text-red-500">*</span></label>
                <input type="date" id="requested_date" name="requested_date" value="{{ old('requested_date') }}" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                @error('requested_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Address --}}
            <div>
                <label for="address" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Adresse de l'intervention <span class="text-red-500">*</span></label>
                <input type="text" id="address" name="address" value="{{ old('address') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                @error('address') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Equipment Optional --}}
            <div>
                <label for="equipment_id" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Équipement concerné (Optionnel)</label>
                <select id="equipment_id" name="equipment_id" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors bg-white">
                    <option value="">Aucun équipement spécifique</option>
                    @foreach($equipments as $equipment)
                        <option value="{{ $equipment->id }}" {{ old('equipment_id') == $equipment->id ? 'selected' : '' }}>
                            {{ $equipment->name }} {{ $equipment->brand ? ' - ' . $equipment->brand : '' }}
                        </option>
                    @endforeach
                </select>
                @error('equipment_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Description détaillée <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="4" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row sm:items-center justify-end gap-3">
            <a href="{{ route('service-providers.show', $serviceProvider) }}" class="px-6 py-2.5 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors text-center" style="font-family: Outfit, sans-serif">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                Envoyer la demande
            </button>
        </div>
    </form>
</div>
@endsection
