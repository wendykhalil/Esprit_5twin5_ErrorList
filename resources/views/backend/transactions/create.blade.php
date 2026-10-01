@extends('layouts.backend')

@section('title', 'Nouvelle transaction - SolarShare Admin')

@section('content')

<div class="max-w-2xl">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Créer une transaction</h2>
        <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">Remplissez le formulaire ci-dessous pour enregistrer une nouvelle transaction</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('admin.transactions.store') }}" class="space-y-5">
            @csrf

            {{-- Payment --}}
            <div>
                <label for="payment_id" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Paiement <span class="text-red-500">*</span>
                </label>
                <select
                    id="payment_id"
                    name="payment_id"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('payment_id') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 bg-white"
                    style="font-family: Outfit, sans-serif"
                >
                    <option value="">-- Sélectionner un paiement --</option>
                    @foreach($payments as $payment)
                        <option value="{{ $payment->id }}" {{ old('payment_id') == $payment->id ? 'selected' : '' }}>
                            #{{ $payment->id }} - {{ number_format($payment->amount, 2) }} TND ({{ ucfirst($payment->status) }})
                        </option>
                    @endforeach
                </select>
                @error('payment_id')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Reference --}}
            <div>
                <label for="reference" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Référence <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="reference"
                    name="reference"
                    value="{{ old('reference') }}"
                    placeholder="Ex: TXN-2024-001"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('reference') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 placeholder-slate-400"
                    style="font-family: Outfit, sans-serif"
                />
                @error('reference')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Type --}}
            <div>
                <label for="type" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Type de transaction <span class="text-red-500">*</span>
                </label>
                <select
                    id="type"
                    name="type"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('type') ? 'border-red-500' : 'border-slate-200' }} text-slate-800 bg-white"
                    style="font-family: Outfit, sans-serif"
                >
                    <option value="">-- Sélectionner un type --</option>
                    <option value="payment" {{ old('type') === 'payment' ? 'selected' : '' }}>Paiement</option>
                    <option value="refund" {{ old('type') === 'refund' ? 'selected' : '' }}>Remboursement</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

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
                        value="{{ old('amount') }}"
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
                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Complétée</option>
                    <option value="failed" {{ old('status') === 'failed' ? 'selected' : '' }}>Échouée</option>
                    <option value="refunded" {{ old('status') === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.414-1.414l-3.85 3.85-1.414-1.414a1 1 0 00-1.414 1.414l2.828 2.828a1 1 0 001.414 0l5.656-5.656z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Transaction date --}}
            <div>
                <label for="transaction_date" class="block text-sm font-600 text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Date de transaction <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="transaction_date"
                    name="transaction_date"
                    value="{{ old('transaction_date') }}"
                    class="w-full px-4 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent {{ $errors->has('transaction_date') ? 'border-red-500' : 'border-slate-200' }} text-slate-800"
                    style="font-family: Outfit, sans-serif"
                />
                @error('transaction_date')
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
                    Créer la transaction
                </button>
                <a
                    href="{{ route('admin.transactions.index') }}"
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
