@extends('layouts.frontend')

@section('title', 'Mes paiements - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">Mes paiements</h1>
            <p class="text-gray-600 mb-8">Historique de vos paiements et transactions</p>

            @if ($payments->isEmpty())
                <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-12 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Aucun paiement</h2>
                    <p class="text-gray-600 mb-6">Vous n'avez pas encore effectué de paiement. Commencez par louer un équipement !</p>
                    <a
                        href="{{ route('equipments.index') }}"
                        class="inline-block px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
                    >
                        Voir les équipements
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($payments as $payment)
                        <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
                                {{-- Amount and Date --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Montant</p>
                                    <p class="text-2xl font-bold text-green-700">{{ number_format($payment->amount, 2, ',', ' ') }} <span class="text-lg">TND</span></p>
                                    <p class="text-xs text-gray-500 mt-2">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                </div>

                                {{-- Method --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Méthode</p>
                                    <div class="flex items-center gap-2">
                                        @if ($payment->method === 'card')
                                            <span class="text-lg">💳</span>
                                            <p class="font-semibold text-gray-900">Carte bancaire</p>
                                        @elseif ($payment->method === 'bank_transfer')
                                            <span class="text-lg">🏦</span>
                                            <p class="font-semibold text-gray-900">Virement</p>
                                        @elseif ($payment->method === 'cash')
                                            <span class="text-lg">💵</span>
                                            <p class="font-semibold text-gray-900">Espèces</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Statut</p>
                                    <div class="flex items-center gap-2">
                                        @if ($payment->status === 'paid')
                                            <span class="inline-block w-2 h-2 bg-green-600 rounded-full"></span>
                                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Payé</span>
                                        @elseif ($payment->status === 'pending')
                                            <span class="inline-block w-2 h-2 bg-yellow-600 rounded-full"></span>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">En attente</span>
                                        @elseif ($payment->status === 'failed')
                                            <span class="inline-block w-2 h-2 bg-red-600 rounded-full"></span>
                                            <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Échoué</span>
                                        @else
                                            <span class="inline-block w-2 h-2 bg-gray-600 rounded-full"></span>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">{{ ucfirst($payment->status) }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action --}}
                                <div class="flex justify-end">
                                    <a
                                        href="{{ route('payments.show', $payment) }}"
                                        class="px-4 py-2 border border-green-200 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition-colors text-sm"
                                    >
                                        Détails
                                    </a>
                                </div>
                            </div>

                            {{-- Transactions Preview --}}
                            @if ($payment->transactions && $payment->transactions->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-2 tracking-wide">Transactions</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($payment->transactions as $transaction)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-50 border border-gray-200 rounded text-xs font-mono text-gray-700">
                                                <span class="inline-block w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                                {{ substr($transaction->reference, 0, 20) }}...
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $payments->links() }}
                </div>
            @endif

            {{-- Back Link --}}
            <div class="mt-12">
                <a href="{{ route('equipments.index') }}" class="inline-flex items-center gap-2 text-green-600 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Continuer les locations
                </a>
            </div>
        </div>
    </div>

@endsection
