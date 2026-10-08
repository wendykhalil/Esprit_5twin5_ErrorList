@extends('layouts.frontend')

@section('title', 'Demandes reçues - SolarShare')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Demandes reçues</h1>
        <p class="text-slate-500 mt-2" style="font-family: Outfit, sans-serif">Gérez les interventions techniques qui vous ont été confiées.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl">
            {{ session('info') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @if($serviceRequests->isEmpty())
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-900 mb-1" style="font-family: Outfit, sans-serif">Aucune demande reçue</h3>
                <p class="text-slate-500" style="font-family: Outfit, sans-serif">Vous n'avez pas encore reçu de demande d'intervention.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase">
                        <tr>
                            <th class="px-6 py-4 font-semibold" style="font-family: Outfit, sans-serif">Demande</th>
                            <th class="px-6 py-4 font-semibold" style="font-family: Outfit, sans-serif">Client</th>
                            <th class="px-6 py-4 font-semibold" style="font-family: Outfit, sans-serif">Statut</th>
                            <th class="px-6 py-4 font-semibold text-right" style="font-family: Outfit, sans-serif">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($serviceRequests as $request)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900" style="font-family: Outfit, sans-serif">{{ $request->title }}</p>
                                    <p class="text-xs text-slate-500 mt-1">Prévue le: {{ \Carbon\Carbon::parse($request->requested_date)->format('d/m/Y') }}</p>
                                    @if($request->equipment)
                                        <p class="text-xs text-amber-600 mt-1">Équipement: {{ $request->equipment->name }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-800">{{ $request->user->name ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badges = [
                                            'pending' => ['bg-yellow-100 text-yellow-800', 'En attente'],
                                            'accepted' => ['bg-blue-100 text-blue-800', 'Acceptée'],
                                            'in_progress' => ['bg-purple-100 text-purple-800', 'En cours'],
                                            'completed' => ['bg-green-100 text-green-800', 'Terminée'],
                                            'rejected' => ['bg-red-100 text-red-800', 'Rejetée'],
                                            'cancelled' => ['bg-slate-200 text-slate-800', 'Annulée'],
                                        ];
                                        $badge = $badges[$request->status] ?? ['bg-gray-100 text-gray-800', $request->status];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('provider.service-requests.show', $request) }}" class="text-sm font-medium text-amber-600 hover:text-amber-700" style="font-family: Outfit, sans-serif">Voir les détails</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($serviceRequests->hasPages())
                <div class="p-6 border-t border-slate-200">
                    {{ $serviceRequests->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
