@extends('layouts.frontend')

@section('title', 'Détails du paiement - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-8 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <svg class="w-6 h-6 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold text-green-900">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Payment Confirmation --}}
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-8">
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h1 class="text-2xl font-bold text-green-900 mb-2">Paiement confirmé</h1>
                            <p class="text-gray-600">Votre transaction a été traitée avec succès</p>
                        </div>

                        {{-- Amount --}}
                        <div class="bg-green-50 border border-green-100 rounded-xl p-6 mb-6">
                            <p class="text-sm text-green-600 font-semibold uppercase mb-1 tracking-wide">Montant payé</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-green-700">{{ number_format($payment->amount, 2, ',', ' ') }}</span>
                                <span class="text-lg text-green-600">TND</span>
                            </div>
                        </div>

                        {{-- Details Grid --}}
                        <div class="grid sm:grid-cols-2 gap-4 mb-6">
                            {{-- Method --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Méthode</p>
                                <div class="flex items-center gap-2">
                                    @if ($payment->method === 'card')
                                        <span class="text-lg">💳</span>
                                        <p class="font-semibold text-gray-900">Carte bancaire</p>
                                    @elseif ($payment->method === 'bank_transfer')
                                        <span class="text-lg">🏦</span>
                                        <p class="font-semibold text-gray-900">Virement bancaire</p>
                                    @elseif ($payment->method === 'cash')
                                        <span class="text-lg">💵</span>
                                        <p class="font-semibold text-gray-900">Espèces</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Statut</p>
                                <div class="flex items-center gap-2">
                                    @if ($payment->status === 'paid')
                                        <span class="inline-block w-2 h-2 bg-green-600 rounded-full"></span>
                                        <p class="font-semibold text-green-700">Payé</p>
                                    @elseif ($payment->status === 'pending')
                                        <span class="inline-block w-2 h-2 bg-yellow-600 rounded-full"></span>
                                        <p class="font-semibold text-yellow-700">En attente</p>
                                    @elseif ($payment->status === 'failed')
                                        <span class="inline-block w-2 h-2 bg-red-600 rounded-full"></span>
                                        <p class="font-semibold text-red-700">Échoué</p>
                                    @else
                                        <span class="inline-block w-2 h-2 bg-gray-600 rounded-full"></span>
                                        <p class="font-semibold text-gray-700">{{ ucfirst($payment->status) }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Date --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Date</p>
                                <p class="font-semibold text-gray-900">{{ $payment->payment_date->format('d/m/Y à H:i') }}</p>
                            </div>

                            {{-- Payment ID --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">ID du paiement</p>
                                <p class="font-mono font-semibold text-gray-900 break-all">{{ $payment->id }}</p>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if ($payment->description)
                            <div class="border-t border-gray-100 pt-6">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Description</p>
                                <p class="text-gray-700">{{ $payment->description }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Transactions --}}
                    @if ($payment->transactions && $payment->transactions->count() > 0)
                        <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-8">
                            <h2 class="text-xl font-bold text-green-900 mb-6">Transactions associées</h2>
                            
                            <div class="space-y-4">
                                @foreach ($payment->transactions as $transaction)
                                    <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Réf: <span class="font-mono">{{ $transaction->reference }}</span></p>
                                            </div>
                                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </div>
                                        
                                        <div class="grid sm:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500">Montant</p>
                                                <p class="font-semibold text-gray-900">{{ number_format($transaction->amount, 2, ',', ' ') }} TND</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Date</p>
                                                <p class="font-semibold text-gray-900">{{ $transaction->transaction_date->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 sticky top-24 space-y-4">
                        <h3 class="text-lg font-bold text-green-900 mb-4">Prochaines étapes</h3>
                        
                        <div class="space-y-3">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    <div class="w-6 h-6 rounded-full bg-green-100 text-green-700 font-semibold text-xs flex items-center justify-center">✓</div>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Paiement effectué</p>
                                    <p class="text-xs text-gray-500">Votre transaction a été traitée</p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 font-semibold text-xs flex items-center justify-center">2</div>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Détails disponibles</p>
                                    <p class="text-xs text-gray-500">Consultez votre historique de paiements</p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 font-semibold text-xs flex items-center justify-center">3</div>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Préparation de la location</p>
                                    <p class="text-xs text-gray-500">L'équipement est mis à disposition</p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-gray-100">

                        <div class="space-y-2">
                            <a
                                href="{{ route('payments.invoice', $payment) }}"
                                class="block w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors text-center text-sm"
                            >
                                🧾 Voir la facture
                            </a>
                            <a
                                href="{{ route('payments.history') }}"
                                class="block w-full px-4 py-2.5 border border-green-200 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition-colors text-center text-sm"
                            >
                                Voir mes paiements
                            </a>
                            <a
                                href="{{ route('equipments.index') }}"
                                class="block w-full px-4 py-2.5 border border-green-200 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition-colors text-center text-sm"
                            >
                                Continuer les locations
                            </a>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <p class="text-xs text-blue-700">
                                <strong>Besoin d'aide ?</strong><br>
                                Contactez-nous via la page <a href="{{ route('contact') }}" class="font-semibold hover:underline">contact</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
