@extends('layouts.backend')

@section('title', 'Éditer ' . $compensationCode->code . ' - SolarShare Admin')

@section('content')

<div class="max-w-2xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.compensation-codes.show', $compensationCode->id) }}" class="text-amber-500 hover:text-amber-600 font-600 mb-4 inline-flex items-center gap-2" style="font-family: Outfit, sans-serif">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Retour
        </a>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Éditer {{ $compensationCode->code }}</h2>
        @if($compensationCode->uses_count > 0)
            <p class="text-red-600 text-sm mt-2">⚠️ Ce code a été utilisé {{ $compensationCode->uses_count }} fois et ne peut plus être édité.</p>
        @endif
    </div>

    {{-- Form --}}
    @if($compensationCode->uses_count === 0)
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form action="{{ route('admin.compensation-codes.update', $compensationCode->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Type and Value Row --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Type --}}
                    <div>
                        <label for="type" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Type de Réduction</label>
                        <select
                            name="type"
                            id="type"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900 bg-white"
                            style="font-family: Outfit, sans-serif"
                            required
                        >
                            <option value="percentage" @selected($compensationCode->type === 'percentage')>Pourcentage (%)</option>
                            <option value="fixed" @selected($compensationCode->type === 'fixed')>Montant Fixe (TND)</option>
                        </select>
                        @error('type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Value --}}
                    <div>
                        <label for="value" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Valeur</label>
                        <input
                            type="number"
                            step="0.01"
                            name="value"
                            id="value"
                            value="{{ old('value', $compensationCode->value) }}"
                            placeholder="Ex: 20 ou 50.50"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900"
                            style="font-family: Outfit, sans-serif"
                            required
                        />
                        @error('value')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Client Selection --}}
                <div>
                    <label for="user_id" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Attribuer à Client</label>
                    <select
                        name="user_id"
                        id="user_id"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900 bg-white"
                        style="font-family: Outfit, sans-serif"
                        required
                    >
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($compensationCode->user_id === $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Max Uses --}}
                <div>
                    <label for="max_uses" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Utilisations Maximum</label>
                    <input
                        type="number"
                        min="1"
                        name="max_uses"
                        id="max_uses"
                        value="{{ old('max_uses', $compensationCode->max_uses) }}"
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900"
                        style="font-family: Outfit, sans-serif"
                        required
                    />
                    @error('max_uses')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Validity Period Row --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- Valid From --}}
                    <div>
                        <label for="valid_from" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Valide À Partir De</label>
                        <input
                            type="datetime-local"
                            name="valid_from"
                            id="valid_from"
                            value="{{ old('valid_from', $compensationCode->valid_from->format('Y-m-d\TH:i')) }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900"
                            style="font-family: Outfit, sans-serif"
                            required
                        />
                        @error('valid_from')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Valid Until --}}
                    <div>
                        <label for="valid_until" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Valide Jusqu'au</label>
                        <input
                            type="datetime-local"
                            name="valid_until"
                            id="valid_until"
                            value="{{ old('valid_until', $compensationCode->valid_until->format('Y-m-d\TH:i')) }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900"
                            style="font-family: Outfit, sans-serif"
                            required
                        />
                        @error('valid_until')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Is Active --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer" style="font-family: Outfit, sans-serif">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked($compensationCode->is_active)
                            class="w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400"
                        />
                        <span class="text-sm font-600 text-slate-900">Code actif</span>
                    </label>
                    @error('is_active')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">Description (Optionnel)</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        placeholder="Notes internes sur ce code..."
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-900"
                        style="font-family: Outfit, sans-serif"
                    >{{ old('description', $compensationCode->description) }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end pt-4 border-t border-slate-200">
                    <a
                        href="{{ route('admin.compensation-codes.show', $compensationCode->id) }}"
                        class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg font-600 hover:bg-slate-50 transition-colors"
                        style="font-family: Outfit, sans-serif"
                    >
                        Annuler
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2.5 bg-amber-500 text-white rounded-lg font-600 hover:bg-amber-600 transition-colors"
                        style="font-family: Outfit, sans-serif"
                    >
                        Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-6">
            <p class="text-yellow-900" style="font-family: Outfit, sans-serif">
                ⚠️ Ce code a déjà été utilisé et ne peut plus être édité pour garantir l'intégrité des données.
            </p>
            <a
                href="{{ route('admin.compensation-codes.show', $compensationCode->id) }}"
                class="mt-4 inline-block px-4 py-2.5 bg-amber-500 text-white rounded-lg font-600 hover:bg-amber-600 transition-colors"
                style="font-family: Outfit, sans-serif"
            >
                Retour au Détail
            </a>
        </div>
    @endif
</div>

@endsection
