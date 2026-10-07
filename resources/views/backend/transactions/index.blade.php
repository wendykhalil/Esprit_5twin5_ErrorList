@extends('layouts.backend')

@section('title', 'Transactions - SolarShare Admin')

@section('content')

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Transactions</h2>
            <p class="text-slate-500 text-sm mt-0.5" style="font-family: Outfit, sans-serif">{{ $transactions->total() }} transactions au total</p>
        </div>
        <a href="{{ route('admin.transactions.create') }}" class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm self-start sm:self-auto" style="font-family: Outfit, sans-serif">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Ajouter une transaction
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-800 text-sm" style="font-family: Outfit, sans-serif">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="space-y-4">
            <div class="flex flex-col lg:flex-row gap-3">
                {{-- Search Input --}}
                <div class="flex-1">
                    <input
                        type="text"
                        name="search"
                        placeholder="Rechercher par référence ou ID..."
                        value="{{ $search }}"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-800 placeholder-slate-400"
                        style="font-family: Outfit, sans-serif"
                    />
                </div>

                {{-- Status Filter --}}
                <div class="w-full lg:w-40">
                    <select
                        name="status"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-800 bg-white"
                        style="font-family: Outfit, sans-serif"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Complétée</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Échouée</option>
                        <option value="refunded" {{ $status === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div class="w-full lg:w-40">
                    <select
                        name="type"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-800 bg-white"
                        style="font-family: Outfit, sans-serif"
                    >
                        <option value="">Tous les types</option>
                        <option value="payment" {{ $type === 'payment' ? 'selected' : '' }}>Paiement</option>
                        <option value="refund" {{ $type === 'refund' ? 'selected' : '' }}>Remboursement</option>
                    </select>
                </div>

                {{-- Payment Filter --}}
                <div class="w-full lg:w-40">
                    <select
                        name="payment_id"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-slate-800 bg-white"
                        style="font-family: Outfit, sans-serif"
                    >
                        <option value="">Tous les paiements</option>
                        @foreach($payments as $p)
                            <option value="{{ $p->id }}" {{ $payment_id == $p->id ? 'selected' : '' }}>
                                #{{ $p->id }} - {{ number_format($p->amount, 2) }} TND
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm font-600 hover:bg-amber-600 transition-colors shadow-sm whitespace-nowrap"
                        style="font-family: Outfit, sans-serif"
                    >
                        Rechercher
                    </button>
                    <a
                        href="{{ route('admin.transactions.index') }}"
                        class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-600 hover:bg-slate-200 transition-colors whitespace-nowrap"
                        style="font-family: Outfit, sans-serif"
                    >
                        Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Référence</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden sm:table-cell" style="font-family: Outfit, sans-serif">Paiement</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Type</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Montant</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Statut</th>
                        <th class="text-left px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider hidden md:table-cell" style="font-family: Outfit, sans-serif">Date</th>
                        <th class="text-right px-5 py-3.5 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 font-600 text-slate-800" style="font-family: Outfit, sans-serif">{{ $transaction->reference }}</td>
                            <td class="px-5 py-4 text-slate-600 hidden sm:table-cell" style="font-family: Outfit, sans-serif">
                                <a href="{{ route('admin.payments.show', $transaction->payment) }}" class="text-blue-600 hover:text-blue-800 font-500">
                                    #{{ $transaction->payment->id }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-slate-600 capitalize" style="font-family: Outfit, sans-serif">{{ $transaction->type }}</td>
                            <td class="px-5 py-4 font-600 text-slate-800" style="font-family: Outfit, sans-serif">{{ number_format($transaction->amount, 2) }} TND</td>
                            <td class="px-5 py-4">
                                <x-backend.status-badge :status="$transaction->status" />
                            </td>
                            <td class="px-5 py-4 text-slate-600 hidden md:table-cell text-xs" style="font-family: Outfit, sans-serif">
                                {{ $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.transactions.show', $transaction) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.transactions.edit', $transaction) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                <p class="text-slate-600 text-sm" style="font-family: Outfit, sans-serif">Aucune transaction trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $transactions->links() }}
    </div>
</div>

@endsection
