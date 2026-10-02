@extends('layouts.frontend')

@section('title', 'Mes factures - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">Mes factures</h1>
            <p class="text-gray-600 mb-8">Liste de toutes vos factures</p>

            @if ($payments->isEmpty())
                <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-12 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Aucune facture</h2>
                    <p class="text-gray-600 mb-6">Vous n'avez pas encore de facture. Commencez par effectuer un paiement !</p>
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
                            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-4 items-center justify-between">
                                {{-- Invoice Number --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Facture</p>
                                    <p class="text-lg font-bold text-gray-900">FAC-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</p>
                                </div>

                                {{-- Amount --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Montant</p>
                                    <p class="text-xl font-bold text-green-700">{{ number_format($payment->amount, 2, ',', ' ') }} <span class="text-sm">TND</span></p>
                                </div>

                                {{-- Date --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Date</p>
                                    <p class="text-gray-900 font-semibold">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase mb-1 tracking-wide">Statut</p>
                                    @php
                                        $statusBgClass = match($payment->status) {
                                            'paid' => 'bg-green-100',
                                            'pending' => 'bg-yellow-100',
                                            'refunded' => 'bg-blue-100',
                                            default => 'bg-red-100',
                                        };
                                        $statusTextClass = match($payment->status) {
                                            'paid' => 'text-green-700',
                                            'pending' => 'text-yellow-700',
                                            'refunded' => 'text-blue-700',
                                            default => 'text-red-700',
                                        };
                                        $statusLabel = match($payment->status) {
                                            'paid' => 'Payé',
                                            'pending' => 'En attente',
                                            'refunded' => 'Remboursé',
                                            default => 'Échoué',
                                        };
                                    @endphp
                                    <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusBgClass }} {{ $statusTextClass }}">
                                        {{ $statusLabel }}
                                    </div>
                                </div>

                                {{-- Button --}}
                                <div class="flex gap-2 justify-end">
                                    <a
                                        href="{{ route('payments.invoice', $payment) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection
