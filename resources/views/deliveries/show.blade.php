@extends('layouts.frontend')

@section('title', 'Livraison - ' . $delivery->equipment->name)

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('deliveries.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Retour aux livraisons
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-linear-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
                <h1 class="text-3xl font-bold mb-2">{{ $delivery->equipment->name }}</h1>
                <p class="text-blue-100">Réservation {{ $delivery->reservation->reference ?? '#' . $delivery->reservation->id }}</p>
            </div>

            <div class="p-6">
                <!-- Status -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Statut de votre livraison</h2>
                        @php
                            $statusClasses = match($delivery->status) {
                                'a_preparer' => 'bg-slate-100 text-slate-800',
                                'prete' => 'bg-blue-100 text-blue-800',
                                'remise_au_client' => 'bg-green-100 text-green-800',
                                'retour_recu' => 'bg-amber-100 text-amber-800',
                                'terminee' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusClasses }}">
                            {{ $statuses[$delivery->status] ?? ucfirst($delivery->status) }}
                        </span>
                    </div>

                    <!-- Timeline -->
                    <div class="space-y-4">
                        <!-- À préparer -->
                        <div class="flex items-start">
                            <div class="flex flex-col items-center mr-4">
                                @if(in_array($delivery->status, ['a_preparer', 'prete', 'remise_au_client', 'retour_recu', 'terminee']))
                                    <div class="w-8 h-8 rounded-full bg-slate-400 flex items-center justify-center text-white font-bold">✓</div>
                                @else
                                    <div class="w-8 h-8 rounded-full border-2 border-gray-300"></div>
                                @endif
                                @if(!$delivery->isComplete())
                                    <div class="w-1 h-12 bg-gray-300 mt-2"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <p class="font-semibold text-gray-900">À préparer</p>
                                <p class="text-sm text-gray-600">Votre équipement est en cours de préparation</p>
                            </div>
                        </div>

                        <!-- Prête -->
                        <div class="flex items-start">
                            <div class="flex flex-col items-center mr-4">
                                @if(in_array($delivery->status, ['prete', 'remise_au_client', 'retour_recu', 'terminee']))
                                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">✓</div>
                                @else
                                    <div class="w-8 h-8 rounded-full border-2 border-gray-300"></div>
                                @endif
                                @if(!$delivery->isComplete())
                                    <div class="w-1 h-12 bg-gray-300 mt-2"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <p class="font-semibold text-gray-900">Prête</p>
                                <p class="text-sm text-gray-600">Votre équipement est prêt pour être retiré</p>
                            </div>
                        </div>

                        <!-- Remise au client -->
                        <div class="flex items-start">
                            <div class="flex flex-col items-center mr-4">
                                @if(in_array($delivery->status, ['remise_au_client', 'retour_recu', 'terminee']))
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">✓</div>
                                @else
                                    <div class="w-8 h-8 rounded-full border-2 border-gray-300"></div>
                                @endif
                                @if(!$delivery->isComplete())
                                    <div class="w-1 h-12 bg-gray-300 mt-2"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <p class="font-semibold text-gray-900">Remise au client</p>
                                @if($delivery->actual_delivery_date)
                                    <p class="text-sm text-green-700">✓ {{ $delivery->actual_delivery_date->format('d/m/Y à H:i') }}</p>
                                @else
                                    <p class="text-sm text-gray-600">En attente</p>
                                @endif
                            </div>
                        </div>

                        <!-- Retour reçu -->
                        <div class="flex items-start">
                            <div class="flex flex-col items-center mr-4">
                                @if(in_array($delivery->status, ['retour_recu', 'terminee']))
                                    <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold">✓</div>
                                @else
                                    <div class="w-8 h-8 rounded-full border-2 border-gray-300"></div>
                                @endif
                                @if(!$delivery->isComplete())
                                    <div class="w-1 h-12 bg-gray-300 mt-2"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <p class="font-semibold text-gray-900">Retour reçu</p>
                                @if($delivery->actual_return_date)
                                    <p class="text-sm text-green-700">✓ {{ $delivery->actual_return_date->format('d/m/Y à H:i') }}</p>
                                @else
                                    <p class="text-sm text-gray-600">En attente</p>
                                @endif
                            </div>
                        </div>

                        <!-- Terminée -->
                        <div class="flex items-start">
                            <div class="flex flex-col items-center mr-4">
                                @if($delivery->status === 'terminee')
                                    <div class="w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center text-white font-bold">✓</div>
                                @else
                                    <div class="w-8 h-8 rounded-full border-2 border-gray-300"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <p class="font-semibold text-gray-900">Terminée</p>
                                <p class="text-sm text-gray-600">Transaction complète</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Dates -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-4">Dates</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-600">Livraison prévue</dt>
                                <dd class="text-gray-900 font-semibold">{{ $delivery->planned_delivery_date->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-600">Retour prévu</dt>
                                <dd class="text-gray-900 font-semibold">{{ $delivery->planned_return_date->format('d/m/Y') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Équipement -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-4">Équipement</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-600">Nom</dt>
                                <dd class="text-gray-900 font-semibold">{{ $delivery->equipment->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-600">Catégorie</dt>
                                <dd class="text-gray-900 font-semibold">{{ $delivery->equipment->category?->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Notes -->
                @if($delivery->delivery_notes || $delivery->return_notes)
                    <div class="mb-8">
                        <h3 class="font-semibold text-gray-900 mb-4">Notes</h3>
                        <div class="space-y-3">
                            @if($delivery->delivery_notes)
                                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-sm text-green-900 font-medium mb-1">Notes de livraison</p>
                                    <p class="text-green-800">{{ $delivery->delivery_notes }}</p>
                                </div>
                            @endif
                            @if($delivery->return_notes)
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <p class="text-sm text-amber-900 font-medium mb-1">Notes de retour</p>
                                    <p class="text-amber-800">{{ $delivery->return_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Related Reservation -->
                <div class="mt-8 pt-8 border-t">
                    <h3 class="font-semibold text-gray-900 mb-4">Réservation associée</h3>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-900">
                            <span class="font-medium">Référence:</span> {{ $delivery->reservation->reference ?? '#' . $delivery->reservation->id }}
                        </p>
                        <p class="text-sm text-blue-900 mt-2">
                            <span class="font-medium">Statut:</span> 
                            @php
                                $reservationStatuses = [
                                    'en_attente' => 'En attente',
                                    'confirmee' => 'Confirmée',
                                    'en_cours' => 'En cours',
                                    'terminee' => 'Terminée',
                                    'litige' => 'Litige',
                                    'annulee' => 'Annulée',
                                    'refusee' => 'Refusée',
                                ];
                            @endphp
                            {{ $reservationStatuses[$delivery->reservation->statut] ?? ucfirst($delivery->reservation->statut) }}
                        </p>
                        <a href="{{ route('reservations.show', $delivery->reservation) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium mt-3 inline-block">
                            Voir la réservation →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
