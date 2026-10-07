@extends('layouts.backend')

@section('title', 'Détails de la transaction - SolarShare Admin')

@section('content')

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Transaction {{ $transaction->reference }}</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">Créée le {{ $transaction->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.transactions.edit', $transaction) }}" class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                Modifier
            </a>
            <form method="POST" action="{{ route('admin.transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 bg-red-500 text-white rounded-lg text-sm font-600 hover:bg-red-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Transaction Details --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">Informations de la transaction</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Référence</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">{{ $transaction->reference }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Type</label>
                            <p class="text-slate-900 font-600 mt-1 capitalize" style="font-family: Outfit, sans-serif">{{ $transaction->type === 'refund' ? 'Remboursement' : 'Paiement' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Montant</label>
                            <p class="text-slate-900 font-600 text-lg mt-1" style="font-family: Outfit, sans-serif">{{ number_format($transaction->amount, 2) }} TND</p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</label>
                            <div class="mt-1">
                                <x-backend.status-badge :status="$transaction->status" />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Date de transaction</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">
                                {{ $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Modifiée le</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">{{ $transaction->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Payment --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">Paiement associé</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">ID du paiement</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">
                                <a href="{{ route('admin.payments.show', $transaction->payment) }}" class="text-blue-600 hover:text-blue-800 font-500">
                                    #{{ $transaction->payment->id }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Montant du paiement</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">{{ number_format($transaction->payment->amount, 2) }} TND</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Méthode de paiement</label>
                            <p class="text-slate-900 font-600 mt-1 capitalize" style="font-family: Outfit, sans-serif">{{ $transaction->payment->method === 'bank_transfer' ? 'Virement bancaire' : ($transaction->payment->method === 'cash' ? 'Espèces' : 'Carte bancaire') }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut du paiement</label>
                            <div class="mt-1">
                                <x-backend.status-badge :status="$transaction->payment->status" />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Date de paiement</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">
                                {{ $transaction->payment->payment_date ? $transaction->payment->payment_date->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Récapitulatif</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Montant</span>
                        <span class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ number_format($transaction->amount, 2) }} TND</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Type</span>
                        <span class="font-bold text-slate-900 capitalize" style="font-family: Outfit, sans-serif">{{ $transaction->type === 'refund' ? 'Remboursement' : 'Paiement' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Statut</span>
                        <div>
                            <x-backend.status-badge :status="$transaction->status" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Back Button --}}
            <a href="{{ route('admin.transactions.index') }}" class="block mt-4 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-600 hover:bg-slate-200 transition-colors text-center" style="font-family: Outfit, sans-serif">
                ← Retour à la liste
            </a>
        </div>
    </div>
</div>

@endsection
