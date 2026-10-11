@extends('layouts.frontend')

@section('title', 'Mes Codes de Compensation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Mes Codes de Compensation</h1>
                <p class="text-gray-600 mt-2">Consultez les codes de compensation disponibles pour vos futures réservations</p>
            </div>
        </div>

        <!-- Messages -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                {{ session('info') }}
            </div>
        @endif

        <!-- Summary Stats -->
        @php
            $stats = [
                'total' => $compensationCodes->total(),
                'available' => $compensationCodes->getCollection()->filter(fn($code) => $code->isValid())->count(),
                'used' => $compensationCodes->getCollection()->filter(fn($code) => $code->uses_count >= $code->max_uses)->count(),
                'expired' => $compensationCodes->getCollection()->filter(fn($code) => $code->isExpired())->count(),
            ];
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Total</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Disponibles</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['available'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Utilisés</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['used'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Expirés</div>
                <div class="text-2xl font-bold text-red-600">{{ $stats['expired'] }}</div>
            </div>
        </div>

        <!-- Compensation Codes List -->
        @forelse ($compensationCodes as $code)
            <div class="bg-white rounded-lg shadow mb-4 p-6 hover:shadow-lg transition">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <!-- Code Display -->
                        <div class="flex items-center gap-2 mb-3">
                            <code class="bg-gray-100 px-3 py-1 rounded font-mono font-bold text-lg text-gray-900">
                                {{ $code->code }}
                            </code>
                            <button type="button"
                                    x-data="{ copied: false }"
                                    @click="navigator.clipboard.writeText('{{ $code->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold transition focus:outline-none focus:ring-1 focus:ring-blue-500 rounded p-1"
                                    :class="copied ? 'text-green-600' : 'text-blue-600'"
                                    :aria-label="copied ? 'Code copié' : 'Copier le code'"
                                    :title="copied ? 'Code copié !' : 'Cliquez pour copier'">
                                <span class="sr-only" x-text="copied ? 'Code copié' : 'Copier le code'"></span>
                                <span aria-hidden="true" x-text="copied ? '✓' : '📋'"></span>
                            </button>
                        </div>

                        <!-- Discount Info -->
                        <div class="mb-3">
                            @if ($code->type === 'percentage')
                                <p class="text-lg font-semibold text-green-600">
                                    Réduction de {{ $code->value }}%
                                </p>
                            @else
                                <p class="text-lg font-semibold text-green-600">
                                    Réduction de {{ number_format($code->value, 2, ',', ' ') }} TND
                                </p>
                            @endif
                        </div>

                        <!-- Source Info -->
                        @if ($code->reclamation)
                            <p class="text-sm text-gray-600">
                                Issu de la réclamation #{{ $code->reclamation->id }}
                                @if ($code->reclamation->reservation)
                                    - {{ $code->reclamation->reservation->getEquipmentLabelAttribute() }}
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-gray-600">
                                Code administrateur
                            </p>
                        @endif
                    </div>

                    <!-- Status Badge -->
                    <div class="text-right">
                        @php
                            $statusClasses = [
                                'Disponible' => 'bg-green-100 text-green-800',
                                'Expiré' => 'bg-red-100 text-red-800',
                                'Utilisé' => 'bg-blue-100 text-blue-800',
                                'Pas encore valide' => 'bg-yellow-100 text-yellow-800',
                                'Désactivé' => 'bg-gray-100 text-gray-800',
                            ];
                            $status = $code->status;
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>

                <!-- Validity and Usage Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 py-4 border-t border-b border-gray-200">
                    <div>
                        <p class="text-xs text-gray-600">Valide de</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $code->valid_from->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Jusqu'au</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $code->valid_until->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Utilisations</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $code->uses_count }} / {{ $code->max_uses }}
                        </p>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                            <div class="bg-blue-600 h-1.5 rounded-full"
                                 style="width: {{ min(($code->uses_count / $code->max_uses) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Créé le</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $code->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <!-- Last Used -->
                @if ($code->last_used_at)
                    <p class="text-xs text-gray-500 mb-4">
                        Dernière utilisation : {{ $code->last_used_at->format('d/m/Y à H:i') }}
                    </p>
                @endif

                <!-- Description -->
                @if ($code->description)
                    <p class="text-sm text-gray-600 mb-4">{{ $code->description }}</p>
                @endif

                <!-- Action -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('compensation-codes.show', $code->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Voir les détails →
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun code de compensation</h3>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore de code de compensation. Soumettez une réclamation pour en obtenir un.</p>
                <a href="{{ route('reclamations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                    Créer une réclamation
                </a>
            </div>
        @endforelse

        <!-- Pagination -->
        @if ($compensationCodes->hasPages())
            <div class="mt-8">
                {{ $compensationCodes->links() }}
            </div>
        @endif

        <!-- Info Section -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">💡 Comment utiliser un code de compensation ?</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Copiez le code de compensation en cliquant sur l'icône 📋</li>
                <li>Accédez à l'étape de paiement lors d'une nouvelle réservation</li>
                <li>Collez le code de compensation dans le champ prévu</li>
                <li>La réduction s'appliquera automatiquement à votre facture</li>
            </ol>
        </div>
    </div>
</div>

@endsection
