@extends('layouts.frontend')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Notifications</h1>
        @if (auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Marquer tout comme lu
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->count() > 0)
        <div class="space-y-4">
            @foreach ($notifications as $notification)
                <div class="bg-white rounded-lg shadow p-6 {{ is_null($notification->read_at) ? 'border-l-4 border-green-600' : '' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            @php
                                $data = $notification->data;
                                $type = $data['type'] ?? 'unknown';
                            @endphp

                            @if ($type === 'reservation_created')
                                <h3 class="text-lg font-semibold text-slate-900">Nouvelle réservation</h3>
                                <p class="text-slate-600 mt-2">
                                    {{ $data['user_name'] ?? 'Un client' }} a créé une réservation pour 
                                    <strong>{{ $data['equipment_name'] ?? 'un équipement' }}</strong>
                                    du {{ $data['date_debut'] ?? 'N/A' }} au {{ $data['date_fin'] ?? 'N/A' }}.
                                </p>
                                <p class="text-slate-500 text-sm mt-2">Total: <strong>{{ $data['prix_total'] ?? 0 }} TND</strong></p>
                            @elseif ($type === 'reservation_approved')
                                <h3 class="text-lg font-semibold text-green-900">Réservation acceptée</h3>
                                <p class="text-slate-600 mt-2">
                                    Votre réservation pour <strong>{{ $data['equipment_name'] ?? 'un équipement' }}</strong>
                                    a été acceptée.
                                </p>
                                <p class="text-slate-500 text-sm mt-2">Du {{ $data['date_debut'] ?? 'N/A' }} au {{ $data['date_fin'] ?? 'N/A' }}</p>
                            @elseif ($type === 'reservation_rejected')
                                <h3 class="text-lg font-semibold text-red-900">Réservation refusée</h3>
                                <p class="text-slate-600 mt-2">
                                    Votre réservation pour <strong>{{ $data['equipment_name'] ?? 'un équipement' }}</strong>
                                    a été refusée.
                                </p>
                                <p class="text-slate-500 text-sm mt-2">Du {{ $data['date_debut'] ?? 'N/A' }} au {{ $data['date_fin'] ?? 'N/A' }}</p>
                            @else
                                <h3 class="text-lg font-semibold text-slate-900">Notification</h3>
                                <p class="text-slate-600 mt-2">{{ $data['message'] ?? 'Aucun message' }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <time class="text-sm text-slate-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                            
                            @if (is_null($notification->read_at))
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-slate-200 text-slate-700 rounded text-xs hover:bg-slate-300">
                                        Marquer comme lu
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">Lu</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="bg-slate-50 rounded-lg p-8 text-center">
            <p class="text-slate-500">Aucune notification pour le moment.</p>
        </div>
    @endif
</div>
@endsection
