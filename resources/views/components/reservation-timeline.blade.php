@props(['reservation'])

@php
    // Determine which steps are completed based on reservation and related data
    $steps = [
        [
            'id' => 1,
            'label' => 'Réservation',
            'description' => 'Demande créée',
            'completed' => true, // Always completed when we have a reservation
            'active' => false,
        ],
        [
            'id' => 2,
            'label' => 'Confirmée',
            'description' => 'Acceptée par admin',
            'completed' => in_array($reservation->statut, ['confirmee', 'en_cours', 'terminee', 'litige']),
            'active' => $reservation->statut === 'confirmee',
        ],
        [
            'id' => 3,
            'label' => 'Paiement',
            'description' => 'Montant versé',
            'completed' => $reservation->payments()->whereIn('status', ['completed', 'paid'])->exists(),
            'active' => false,
        ],
        [
            'id' => 4,
            'label' => 'Contrat',
            'description' => 'Accepté',
            'completed' => $reservation->contract()->where('accepted_at', '!=', null)->exists(),
            'active' => false,
        ],
        [
            'id' => 5,
            'label' => 'Livraison',
            'description' => 'Équipement remis',
            'completed' => $reservation->delivery && in_array($reservation->delivery->status, ['remise_au_client', 'retour_recu', 'terminee']),
            'active' => $reservation->delivery && $reservation->delivery->status === 'remise_au_client',
        ],
        [
            'id' => 6,
            'label' => 'Retour',
            'description' => 'Équipement récupéré',
            'completed' => $reservation->delivery && in_array($reservation->delivery->status, ['retour_recu', 'terminee']),
            'active' => $reservation->delivery && $reservation->delivery->status === 'retour_recu',
        ],
    ];

    // Determine the overall status color
    $statusColor = match ($reservation->statut) {
        'en_attente' => 'amber',
        'confirmee' => 'blue',
        'en_cours' => 'cyan',
        'terminee' => 'green',
        'litige' => 'red',
        'annulee' => 'slate',
        'refusee' => 'red',
        default => 'slate',
    };
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <div class="mb-8">
        <h2 class="text-lg font-bold text-slate-900">Cycle de vie de la réservation</h2>
        <p class="mt-1 text-sm text-slate-500">Suivi des étapes de votre réservation</p>
    </div>

    <!-- Horizontal Timeline -->
    <div class="overflow-x-auto">
        <div class="inline-block min-w-full">
            <!-- Timeline track -->
            <div class="relative flex items-center justify-between px-2 py-8">
                <!-- Background line -->
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-slate-200 -translate-y-1/2" style="z-index: 1;"></div>

                <!-- Colored progress line -->
                @php
                    // Calculate progress: count completed steps
                    $completedCount = count(array_filter($steps, fn($s) => $s['completed']));
                    $progressPercent = ($completedCount / count($steps)) * 100;
                @endphp
                <div class="absolute top-1/2 left-0 h-1 -translate-y-1/2 transition-all duration-500 ease-out" 
                     style="background-color: rgb(34, 197, 94); width: {{ $progressPercent }}%; z-index: 2;"></div>

                <!-- Timeline steps -->
                <div class="relative flex w-full justify-between" style="z-index: 3;">
                    @foreach ($steps as $step)
                        @php
                            $isCompleted = $step['completed'];
                            $isActive = $step['active'];
                            
                            $circleClasses = 'flex items-center justify-center w-10 h-10 rounded-full border-4 transition-all';
                            if ($isCompleted) {
                                $circleClasses .= ' bg-green-500 border-white shadow-md';
                            } elseif ($isActive) {
                                $circleClasses .= ' bg-blue-500 border-white shadow-md animate-pulse';
                            } else {
                                $circleClasses .= ' bg-slate-200 border-white shadow-sm';
                            }
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="{{ $circleClasses }}">
                                @if ($isCompleted)
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif ($isActive)
                                    <div class="w-2 h-2 bg-white rounded-full"></div>
                                @else
                                    <div class="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
                                @endif
                            </div>
                            
                            <!-- Step label and description -->
                            <div class="mt-4 text-center whitespace-nowrap">
                                <p class="text-xs font-semibold text-slate-900">{{ $step['label'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Status summary -->
    <div class="mt-8 border-t border-slate-100 pt-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Reservation Status -->
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Statut Réservation</p>
                <p class="mt-2 text-sm font-bold text-slate-900">
                    {{ ucfirst(str_replace('_', ' ', $reservation->statut)) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">Créée: {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <!-- Payment Status -->
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Paiement</p>
                @php
                    $payment = $reservation->payments()->whereIn('status', ['completed', 'paid'])->first();
                @endphp
                @if ($payment)
                    <p class="mt-2 text-sm font-bold text-green-600">✓ Effectué</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $payment->payment_date?->format('d/m/Y') ?? 'Date non disponible' }}</p>
                @else
                    <p class="mt-2 text-sm font-bold text-amber-600">⏳ En attente</p>
                    <p class="mt-1 text-xs text-slate-500">Non encore reçu</p>
                @endif
            </div>

            <!-- Contract Status -->
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Contrat</p>
                @php
                    $contract = $reservation->contract()->first();
                @endphp
                @if ($contract && $contract->accepted_at)
                    <p class="mt-2 text-sm font-bold text-green-600">✓ Accepté</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $contract->accepted_at->format('d/m/Y') }}</p>
                @else
                    <p class="mt-2 text-sm font-bold text-amber-600">⏳ En attente</p>
                    <p class="mt-1 text-xs text-slate-500">Non encore accepté</p>
                @endif
            </div>

            <!-- Delivery Status -->
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Livraison</p>
                @php
                    $delivery = $reservation->delivery;
                @endphp
                @if ($delivery)
                    <p class="mt-2 text-sm font-bold text-slate-900">
                        {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        @if ($delivery->actual_delivery_date)
                            {{ $delivery->actual_delivery_date->format('d/m/Y') }}
                        @elseif ($delivery->planned_delivery_date)
                            Prévue: {{ $delivery->planned_delivery_date->format('d/m/Y') }}
                        @else
                            Non disponible
                        @endif
                    </p>
                @else
                    <p class="mt-2 text-sm font-bold text-slate-500">—</p>
                    <p class="mt-1 text-xs text-slate-500">Non créée</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Reservation details -->
    <div class="mt-8 border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">Détails de la période</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Début</p>
                <p class="mt-1 text-sm font-bold text-slate-900">{{ $reservation->date_debut->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Fin</p>
                <p class="mt-1 text-sm font-bold text-slate-900">{{ $reservation->date_fin->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Durée</p>
                <p class="mt-1 text-sm font-bold text-slate-900">{{ $reservation->date_fin->diffInDays($reservation->date_debut) }} jours</p>
            </div>
        </div>
    </div>
</div>
