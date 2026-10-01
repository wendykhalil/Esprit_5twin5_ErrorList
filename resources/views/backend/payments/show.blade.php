@extends('layouts.backend')

@section('title', 'Détails du paiement - SolarShare Admin')

@section('content')

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Paiement #{{ $payment->id }}</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">Créé le {{ $payment->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.payments.edit', $payment) }}" class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                Modifier
            </a>
            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ? Les transactions associées seront également supprimées.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 bg-red-500 text-white rounded-lg text-sm font-600 hover:bg-red-600 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Payment Details --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">Informations du paiement</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">ID du paiement</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">#{{ $payment->id }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Montant</label>
                            <p class="text-slate-900 font-600 text-lg mt-1" style="font-family: Outfit, sans-serif">{{ number_format($payment->amount, 2) }} TND</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Méthode de paiement</label>
                            <p class="text-slate-900 font-600 mt-1 capitalize" style="font-family: Outfit, sans-serif">{{ $payment->method === 'bank_transfer' ? 'Virement bancaire' : ($payment->method === 'cash' ? 'Espèces' : 'Carte bancaire') }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</label>
                            <div class="mt-1">
                                <x-backend.status-badge :status="$payment->status" />
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Date de paiement</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">
                                {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y à H:i') : '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Modifié le</label>
                            <p class="text-slate-900 font-600 mt-1" style="font-family: Outfit, sans-serif">{{ $payment->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                    @if($payment->description)
                        <div class="border-t border-slate-100 pt-4">
                            <label class="text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Description</label>
                            <p class="text-slate-700 mt-2" style="font-family: Outfit, sans-serif">{{ $payment->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Transactions --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">Transactions associées</h3>
                </div>
                @if($payment->transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="text-left px-6 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Référence</th>
                                    <th class="text-left px-6 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Type</th>
                                    <th class="text-left px-6 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Montant</th>
                                    <th class="text-left px-6 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</th>
                                    <th class="text-left px-6 py-3 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($payment->transactions as $transaction)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-3.5 font-600 text-slate-800" style="font-family: Outfit, sans-serif">{{ $transaction->reference }}</td>
                                        <td class="px-6 py-3.5 text-slate-600 capitalize" style="font-family: Outfit, sans-serif">{{ $transaction->type }}</td>
                                        <td class="px-6 py-3.5 font-600 text-slate-800" style="font-family: Outfit, sans-serif">{{ number_format($transaction->amount, 2) }} TND</td>
                                        <td class="px-6 py-3.5">
                                            <x-backend.status-badge :status="$transaction->status" />
                                        </td>
                                        <td class="px-6 py-3.5 text-slate-600 text-xs" style="font-family: Outfit, sans-serif">
                                            {{ $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        <p class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Aucune transaction associée</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">Récapitulatif</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Montant</span>
                        <span class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ number_format($payment->amount, 2) }} TND</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Transactions</span>
                        <span class="font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ $payment->transactions->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Statut</span>
                        <div>
                            <x-backend.status-badge :status="$payment->status" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Back Button --}}
            <a href="{{ route('admin.payments.index') }}" class="block mt-4 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-600 hover:bg-slate-200 transition-colors text-center" style="font-family: Outfit, sans-serif">
                ← Retour à la liste
            </a>
        </div>
    </div>
</div>

@endsection
