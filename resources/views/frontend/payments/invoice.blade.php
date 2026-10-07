@extends('layouts.frontend')

@section('title', 'Facture - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            {{-- Action Buttons (Hidden on Print) --}}
            <div class="mb-8 flex flex-col sm:flex-row gap-4 print:hidden">
                <button
                    onclick="window.print()"
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4m16 0a2 2 0 00-2-2h-.5a2 2 0 00-2 2m16 0v4a2 2 0 00-2 2h-.5a2 2 0 00-2-2"></path>
                    </svg>
                    🖨 Imprimer la facture
                </button>

                <a
                    href="{{ route('payments.show', $payment) }}"
                    class="flex items-center justify-center gap-2 px-6 py-3 border border-green-200 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    ← Retour au paiement
                </a>
            </div>

            {{-- Invoice Container --}}
            <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-8 sm:p-12 print:rounded-none print:border-0 print:shadow-none print:p-0">
                
                {{-- Invoice Header --}}
                <div class="mb-12 pb-8 border-b-2 border-green-100">
                    <div class="flex items-start justify-between mb-8">
                        {{-- Logo/Brand --}}
                        <div>
                            <h1 class="text-3xl font-bold text-green-700">SOLARSHARE</h1>
                            <p class="text-sm text-gray-500 mt-1">Plateforme de location d'équipements solaires</p>
                        </div>

                        {{-- Invoice Title --}}
                        <div class="text-right">
                            <h2 class="text-4xl font-bold text-gray-900">FACTURE</h2>
                            <p class="text-sm text-gray-500 mt-2">
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 font-semibold rounded-full">
                                    PAYÉE
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Invoice Info --}}
                    <div class="grid sm:grid-cols-3 gap-8 text-sm">
                        <div>
                            <p class="text-gray-500 font-semibold uppercase tracking-wide mb-1">Numéro de facture</p>
                            <p class="font-mono font-bold text-gray-900 text-lg">FAC-2026-{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold uppercase tracking-wide mb-1">Date de facture</p>
                            <p class="font-semibold text-gray-900">{{ $payment->payment_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 font-semibold uppercase tracking-wide mb-1">Date d'échéance</p>
                            <p class="font-semibold text-gray-900">{{ $payment->payment_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Client Information --}}
                <div class="mb-12 pb-8 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">Informations client</h3>
                    
                    <div class="grid sm:grid-cols-2 gap-12">
                        {{-- From --}}
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-3">De</p>
                            <div class="space-y-1 text-sm">
                                <p class="font-bold text-gray-900">SOLARSHARE</p>
                                <p class="text-gray-600">Tunisie</p>
                                <p class="text-gray-600">support@solarshare.tn</p>
                            </div>
                        </div>

                        {{-- To --}}
                        <div>
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-3">Pour</p>
                            <div class="space-y-1 text-sm">
                                <p class="font-bold text-gray-900">
                                    @if (auth()->check())
                                        {{ auth()->user()->name ?? 'Client SolarShare' }}
                                    @else
                                        Client SolarShare
                                    @endif
                                </p>
                                @if (auth()->check())
                                    <p class="text-gray-600">{{ auth()->user()->email ?? '' }}</p>
                                @else
                                    <p class="text-gray-600">Email non disponible</p>
                                @endif
                                <p class="text-gray-600">Tunisie</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Invoice Items --}}
                <div class="mb-12">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-green-100">
                                    <th class="text-left py-3 px-4 font-bold text-gray-900">Description</th>
                                    <th class="text-center py-3 px-4 font-bold text-gray-900">Montant</th>
                                    <th class="text-center py-3 px-4 font-bold text-gray-900">TVA</th>
                                    <th class="text-right py-3 px-4 font-bold text-gray-900">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 print:hover:bg-transparent">
                                    <td class="py-4 px-4 text-gray-700">
                                        <p class="font-semibold">{{ $payment->description ?? 'Paiement de location' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Référence: #{{ $payment->id }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-center text-gray-900 font-semibold">
                                        {{ number_format($payment->amount, 2, ',', ' ') }} TND
                                    </td>
                                    <td class="py-4 px-4 text-center text-gray-600">
                                        -
                                    </td>
                                    <td class="py-4 px-4 text-right text-gray-900 font-semibold">
                                        {{ number_format($payment->amount, 2, ',', ' ') }} TND
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="flex justify-end mt-8 w-full">
                        <div class="w-full sm:w-80 space-y-3 border-t-2 border-green-100 pt-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sous-total :</span>
                                <span class="font-semibold text-gray-900">{{ number_format($payment->amount, 2, ',', ' ') }} TND</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">TVA (19%) :</span>
                                <span class="font-semibold text-gray-900">0,00 TND</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold bg-green-50 border border-green-100 rounded-lg p-4 mt-4">
                                <span class="text-gray-900">Total à payer :</span>
                                <span class="text-green-700">{{ number_format($payment->amount, 2, ',', ' ') }} TND</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Details Section --}}
                <div class="mb-12 pb-8 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">Détails du paiement</h3>
                    
                    <div class="grid sm:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Méthode de paiement</p>
                            <div class="flex items-center gap-2 mt-1">
                                @if ($payment->method === 'card')
                                    <span class="text-xl">💳</span>
                                    <p class="font-semibold text-gray-900">Carte bancaire</p>
                                @elseif ($payment->method === 'bank_transfer')
                                    <span class="text-xl">🏦</span>
                                    <p class="font-semibold text-gray-900">Virement bancaire</p>
                                @elseif ($payment->method === 'cash')
                                    <span class="text-xl">💵</span>
                                    <p class="font-semibold text-gray-900">Espèces</p>
                                @else
                                    <p class="font-semibold text-gray-900">{{ ucfirst($payment->method) }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Statut du paiement</p>
                            <div class="flex items-center gap-2 mt-1">
                                @if ($payment->status === 'paid')
                                    <span class="inline-block w-3 h-3 bg-green-600 rounded-full"></span>
                                    <p class="font-semibold text-green-700">Payé</p>
                                @elseif ($payment->status === 'pending')
                                    <span class="inline-block w-3 h-3 bg-yellow-600 rounded-full"></span>
                                    <p class="font-semibold text-yellow-700">En attente</p>
                                @elseif ($payment->status === 'failed')
                                    <span class="inline-block w-3 h-3 bg-red-600 rounded-full"></span>
                                    <p class="font-semibold text-red-700">Échoué</p>
                                @else
                                    <span class="inline-block w-3 h-3 bg-gray-600 rounded-full"></span>
                                    <p class="font-semibold text-gray-700">{{ ucfirst($payment->status) }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">ID du paiement</p>
                            <p class="font-mono font-semibold text-gray-900 text-sm break-all mt-1">#{{ $payment->id }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Date du paiement</p>
                            <p class="font-semibold text-gray-900 mt-1">{{ $payment->payment_date->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Transaction Details --}}
                @if ($transaction)
                    <div class="mb-12 pb-8 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">Détails de la transaction</h3>
                        
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                <p class="text-xs text-blue-600 font-semibold uppercase mb-2 tracking-wide">Référence transaction</p>
                                <p class="font-mono font-semibold text-blue-900 text-sm break-all mt-1">{{ $transaction->reference }}</p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Type</p>
                                <p class="font-semibold text-gray-900 mt-1">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Montant</p>
                                <p class="font-semibold text-gray-900 text-lg mt-1">{{ number_format($transaction->amount, 2, ',', ' ') }} TND</p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Statut</p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if ($transaction->status === 'completed')
                                        <span class="inline-block w-3 h-3 bg-green-600 rounded-full"></span>
                                        <p class="font-semibold text-green-700">Complétée</p>
                                    @elseif ($transaction->status === 'pending')
                                        <span class="inline-block w-3 h-3 bg-yellow-600 rounded-full"></span>
                                        <p class="font-semibold text-yellow-700">En attente</p>
                                    @elseif ($transaction->status === 'failed')
                                        <span class="inline-block w-3 h-3 bg-red-600 rounded-full"></span>
                                        <p class="font-semibold text-red-700">Échouée</p>
                                    @else
                                        <span class="inline-block w-3 h-3 bg-gray-600 rounded-full"></span>
                                        <p class="font-semibold text-gray-700">{{ ucfirst($transaction->status) }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Date transaction</p>
                                <p class="font-semibold text-gray-900 mt-1">{{ $transaction->transaction_date->format('d/m/Y à H:i') }}</p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Liée au paiement</p>
                                <p class="font-semibold text-gray-900 mt-1">#{{ $transaction->payment_id }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Footer --}}
                <div class="pt-8 border-t border-gray-100 space-y-4">
                    <div class="text-center space-y-3">
                        <p class="text-sm text-gray-600">
                            <strong>Merci d'avoir utilisé SolarShare !</strong>
                        </p>
                        <p class="text-xs text-gray-500">
                            Cette facture est générée automatiquement. Elle n'est pas un document fiscal officiel.
                        </p>
                        <div class="flex flex-wrap justify-center gap-4 text-xs text-gray-500 mt-4">
                            <a href="{{ route('home') }}" class="hover:text-green-600 hover:underline">Accueil</a>
                            <span class="text-gray-300">•</span>
                            <a href="{{ route('contact') }}" class="hover:text-green-600 hover:underline">Contact</a>
                            <span class="text-gray-300">•</span>
                            <a href="{{ route('about') }}" class="hover:text-green-600 hover:underline">À propos</a>
                        </div>
                    </div>

                    <div class="text-center pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400">
                            © {{ date('Y') }} SolarShare. Tous droits réservés.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            body {
                background-color: white;
            }
            .print\:hidden {
                display: none !important;
            }
            .print\:rounded-none {
                border-radius: 0 !important;
            }
            .print\:border-0 {
                border: 0 !important;
            }
            .print\:shadow-none {
                box-shadow: none !important;
            }
            .print\:p-0 {
                padding: 0 !important;
            }
            .print\:hover\:bg-transparent:hover {
                background-color: transparent !important;
            }
        }
    </style>

@endsection
