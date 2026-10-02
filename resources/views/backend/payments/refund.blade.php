@extends('layouts.backend')

@section('title', 'Créer un remboursement - SolarShare Admin')

@section('content')

<div class="max-w-2xl">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.payments.show', $payment) }}" class="inline-flex items-center gap-2 text-amber-600 hover:text-amber-700 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span class="text-sm font-600" style="font-family: Outfit, sans-serif">Retour au paiement</span>
        </a>
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Créer un remboursement</h2>
        <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">Enregistrer une transaction de remboursement pour ce paiement</p>
    </div>

    {{-- Payment Summary --}}
    <div class="bg-linear-to-r from-blue-50 to-blue-50/50 rounded-xl border border-blue-200 p-5 mb-6">
        <h3 class="text-sm font-600 text-blue-900 mb-4" style="font-family: Outfit, sans-serif">Résumé du paiement</h3>
        <div class="grid grid-cols-3 gap-4">
            {{-- Montant initial --}}
            <div class="bg-white rounded-lg p-4 border border-blue-100">
                <p class="text-xs text-slate-600 font-500 mb-1" style="font-family: Outfit, sans-serif">Montant initial</p>
                <p class="text-lg font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ number_format($payment->amount, 2, ',', ' ') }} <span class="text-sm text-slate-600 font-normal">TND</span></p>
            </div>

            {{-- Total remboursé --}}
            <div class="bg-white rounded-lg p-4 border border-blue-100">
                <p class="text-xs text-slate-600 font-500 mb-1" style="font-family: Outfit, sans-serif">Total remboursé</p>
                <p class="text-lg font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ number_format($totalRefunded, 2, ',', ' ') }} <span class="text-sm text-slate-600 font-normal">TND</span></p>
            </div>

            {{-- Remboursable restant --}}
            <div class="bg-white rounded-lg p-4 border border-amber-100">
                <p class="text-xs text-slate-600 font-500 mb-1" style="font-family: Outfit, sans-serif">Remboursable restant</p>
                <p class="text-lg font-bold text-amber-600" style="font-family: Outfit, sans-serif">{{ number_format($remainingRefundable, 2, ',', ' ') }} <span class="text-sm text-slate-600 font-normal">TND</span></p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.payments.refund.store', $payment) }}" class="space-y-5">
            @csrf

            {{-- Amount --}}
            <div>
                <label for="amount" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Montant à rembourser <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        max="{{ $remainingRefundable }}"
                        id="amount"
                        name="amount"
                        value="{{ old('amount') }}"
                        placeholder="0.00"
                        class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('amount') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 placeholder-slate-400"
                        style="font-family: Outfit, sans-serif"
                    />
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-600" style="font-family: Outfit, sans-serif">TND</span>
                </div>
                <p class="text-xs text-slate-500 mt-1.5" style="font-family: Outfit, sans-serif">Maximum : {{ number_format($remainingRefundable, 2, ',', ' ') }} TND</p>
                @error('amount')
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
                    rows="3"
                    placeholder="Motif du remboursement, détails supplémentaires..."
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-800 placeholder-slate-400 resize-none"
                    style="font-family: Outfit, sans-serif"
                >{{ old('description') }}</textarea>
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
                    Créer le remboursement
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
