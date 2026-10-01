@extends('layouts.backend')

@section('title', 'Modifier le paiement - SolarShare Admin')

@section('content')

<div class="max-w-2xl">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Modifier le paiement #{{ $payment->id }}</h2>
        <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">Mettez à jour les informations du paiement ci-dessous</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Amount --}}
            <div>
                <label for="amount" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Montant <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        id="amount"
                        name="amount"
                        value="{{ old('amount', $payment->amount) }}"
                        placeholder="0.00"
                        class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('amount') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 placeholder-slate-400"
                        style="font-family: Outfit, sans-serif"
                    />
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-600" style="font-family: Outfit, sans-serif">TND</span>
                </div>
                @error('amount')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Method --}}
            <div>
                <label for="method" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Méthode de paiement <span class="text-red-500">*</span>
                </label>
                <select
                    id="method"
                    name="method"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('method') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 bg-white"
                    style="font-family: Outfit, sans-serif"
                >
                    <option value="">-- Sélectionner une méthode --</option>
                    <option value="card" {{ old('method', $payment->method) === 'card' ? 'selected' : '' }}>Carte bancaire</option>
                    <option value="cash" {{ old('method', $payment->method) === 'cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="bank_transfer" {{ old('method', $payment->method) === 'bank_transfer' ? 'selected' : '' }}>Virement bancaire</option>
                </select>
                @error('method')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('status') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 bg-white"
                    style="font-family: Outfit, sans-serif"
                >
                    <option value="">-- Sélectionner un statut --</option>
                    <option value="pending" {{ old('status', $payment->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="paid" {{ old('status', $payment->status) === 'paid' ? 'selected' : '' }}>Payé</option>
                    <option value="failed" {{ old('status', $payment->status) === 'failed' ? 'selected' : '' }}>Échoué</option>
                    <option value="refunded" {{ old('status', $payment->status) === 'refunded' ? 'selected' : '' }}>Remboursé</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Payment date --}}
            <div>
                <label for="payment_date" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Date de paiement <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="payment_date"
                    name="payment_date"
                    value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('payment_date') ? 'border-red-500' : 'border-slate-200' }} text-slate-800"
                    style="font-family: Outfit, sans-serif"
                />
                @error('payment_date')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Description (optionnel)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Entrez des détails supplémentaires sur ce paiement..."
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-800 placeholder-slate-400 resize-none"
                    style="font-family: Outfit, sans-serif"
                >{{ old('description', $payment->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-4 border-t border-slate-200">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm"
                    style="font-family: Outfit, sans-serif"
                >
                    Enregistrer les modifications
                </button>
                <a
                    href="{{ route('admin.payments.show', $payment) }}"
                    class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-600 hover:bg-slate-200 transition-colors text-center"
                    style="font-family: Outfit, sans-serif"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
