@extends('layouts.frontend')

@section('title', 'Code de Compensation ' . $compensationCode->code)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Code de Compensation</h1>
                    <p class="text-gray-600 mt-2">Détails du code</p>
                </div>
                <div class="text-right">
                    @php
                        $statusClasses = [
                            'Disponible' => 'bg-green-100 text-green-800',
                            'Expiré' => 'bg-red-100 text-red-800',
                            'Utilisé' => 'bg-blue-100 text-blue-800',
                            'Pas encore valide' => 'bg-yellow-100 text-yellow-800',
                            'Désactivé' => 'bg-gray-100 text-gray-800',
                        ];
                        $status = $compensationCode->status;
                    @endphp
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2">
                <!-- Code Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Le Code</h2>

                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-6">
                        <p class="text-sm text-gray-600 mb-2">Votre code de compensation</p>
                        <code class="block font-mono font-bold text-3xl text-gray-900 mb-4 tracking-wider">
                            {{ $compensationCode->code }}
                        </code>
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ $compensationCode->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :class="copied ? 'bg-green-600' : 'bg-blue-600'"
                                :aria-label="copied ? 'Code copié' : 'Copier le code de compensation'"
                                :title="copied ? 'Code copié !' : 'Cliquez pour copier'">
                                <span class="sr-only" x-text="copied ? 'Code copié' : 'Copier le code'"></span>
                                <span aria-hidden="true" x-text="copied ? '✓ Copié !' : '📋 Copier le code'"></span>
                        </button>
                    </div>

                    <!-- Discount Details -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Valeur de la Réduction</h3>
                        @if ($compensationCode->type === 'percentage')
                            <div class="flex items-center gap-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="text-4xl font-bold text-green-600">{{ $compensationCode->value }}%</div>
                                <div>
                                    <p class="text-sm text-gray-600">Réduction en pourcentage</p>
                                    <p class="text-sm text-gray-600 mt-1">S'applique sur le prix total de la réservation</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="text-4xl font-bold text-green-600">{{ number_format($compensationCode->value, 2, ',', ' ') }} TND</div>
                                <div>
                                    <p class="text-sm text-gray-600">Réduction forfaitaire</p>
                                    <p class="text-sm text-gray-600 mt-1">Montant fixe à déduire de la facture</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Source Information -->
                @if ($compensationCode->reclamation)
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Réclamation Associée</h2>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Numéro</p>
                                <a href="{{ route('reclamations.show', $compensationCode->reclamation->id) }}"
                                   class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                    Réclamation #{{ $compensationCode->reclamation->id }}
                                </a>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600">Titre</p>
                                <p class="text-gray-900">{{ $compensationCode->reclamation->title }}</p>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600">Description</p>
                                <p class="text-gray-900">{{ Str::limit($compensationCode->reclamation->description, 300) }}</p>
                            </div>

                            @if ($compensationCode->reclamation->reservation)
                                <div class="pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600">Équipement</p>
                                    <p class="text-gray-900">
                                        <strong>{{ $compensationCode->reclamation->reservation->getEquipmentLabelAttribute() }}</strong>
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600">Période de location</p>
                                    <p class="text-gray-900">
                                        {{ $compensationCode->reclamation->reservation->date_debut->format('d/m/Y') }}
                                        au
                                        {{ $compensationCode->reclamation->reservation->date_fin->format('d/m/Y') }}
                                    </p>
                                </div>
                            @endif

                            <div class="pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600">Réclamation créée le</p>
                                <p class="text-gray-900">{{ $compensationCode->reclamation->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <p class="text-gray-700">Ce code a été créé manuellement par un administrateur.</p>
                    </div>
                @endif

                <!-- Validity and Usage -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Validité et Utilisation</h2>

                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Valide de</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $compensationCode->valid_from->format('d/m/Y à H:i') }}
                            </p>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Jusqu'au</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $compensationCode->valid_until->format('d/m/Y à H:i') }}
                            </p>
                            @if ($compensationCode->isExpired())
                                <p class="text-xs text-red-600 mt-2">⚠️ Ce code a expiré</p>
                            @else
                                <p class="text-xs text-green-600 mt-2">✓ Ce code est toujours valide</p>
                            @endif
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-3">Utilisations</p>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-900">
                                    {{ $compensationCode->uses_count }} / {{ $compensationCode->max_uses }}
                                </span>
                                <span class="text-sm text-gray-600">
                                    @if ($compensationCode->max_uses == 1)
                                        Code à usage unique
                                    @else
                                        {{ $compensationCode->max_uses - $compensationCode->uses_count }} utilisation(s) restante(s)
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all"
                                     style="width: {{ min(($compensationCode->uses_count / $compensationCode->max_uses) * 100, 100) }}%"></div>
                            </div>
                        </div>

                        @if ($compensationCode->last_used_at)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Dernière utilisation</p>
                                <p class="text-gray-900">{{ $compensationCode->last_used_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & Actions -->
            <div class="lg:col-span-1">
                <!-- Summary Card -->
                <div class="bg-white rounded-lg shadow p-6 sticky top-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé</h3>

                    <div class="space-y-4">
                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Code</p>
                            <p class="text-lg font-bold font-mono text-gray-900 break-all">
                                {{ $compensationCode->code }}
                            </p>
                        </div>

                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Type</p>
                            <p class="text-gray-900 font-semibold">
                                @if ($compensationCode->type === 'percentage')
                                    Pourcentage
                                @else
                                    Montant fixe
                                @endif
                            </p>
                        </div>

                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">État</p>
                            @php
                                $statusColors = [
                                    'Disponible' => 'text-green-600',
                                    'Expiré' => 'text-red-600',
                                    'Utilisé' => 'text-blue-600',
                                    'Pas encore valide' => 'text-yellow-600',
                                    'Désactivé' => 'text-gray-600',
                                ];
                            @endphp
                            <p class="font-semibold {{ $statusColors[$compensationCode->status] ?? 'text-gray-900' }}">
                                {{ $compensationCode->status }}
                            </p>
                        </div>

                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Créé le</p>
                            <p class="text-gray-900">{{ $compensationCode->created_at->format('d/m/Y à H:i') }}</p>
                        </div>

                        @if ($compensationCode->description)
                            <div class="pt-4">
                                <p class="text-sm text-gray-600 mb-2">Description</p>
                                <p class="text-sm text-gray-900 italic">{{ $compensationCode->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Card -->
                @if ($compensationCode->isValid())
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-green-900 mb-3">✓ Code Disponible</h3>
                        <p class="text-sm text-green-800 mb-4">
                            Ce code est prêt à être utilisé lors de votre prochaine réservation.
                        </p>
                        <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText('{{ $compensationCode->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                :class="copied ? 'bg-green-800' : 'bg-green-600'"
                                :aria-label="copied ? 'Code copié' : 'Copier le code'"
                                :title="copied ? 'Code copié !' : 'Cliquez pour copier'">
                                <span class="sr-only" x-text="copied ? 'Code copié' : 'Copier le code'"></span>
                                <span aria-hidden="true" x-text="copied ? '✓ Copié !' : '📋 Copier et utiliser'"></span>
                        </button>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-red-900 mb-3">⚠️ Code Non Disponible</h3>
                        <p class="text-sm text-red-800">
                            @if ($compensationCode->isExpired())
                                Ce code a expiré le {{ $compensationCode->valid_until->format('d/m/Y') }}.
                            @elseif (!$compensationCode->hasStarted())
                                Ce code sera valide à partir du {{ $compensationCode->valid_from->format('d/m/Y') }}.
                            @elseif (!$compensationCode->hasUsesRemaining())
                                Ce code a déjà été utilisé.
                            @elseif (!$compensationCode->is_active)
                                Ce code a été désactivé.
                            @endif
                        </p>
                    </div>
                @endif

                <!-- Info Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="font-semibold text-blue-900 mb-3">💡 Conseils</h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li>✓ Conservez ce code en lieu sûr</li>
                        <li>✓ Vous pouvez le copier avec le bouton ci-dessus</li>
                        <li>✓ Valable uniquement pour vous</li>
                        @if ($compensationCode->max_uses > 1)
                            <li>✓ Peut être utilisé {{ $compensationCode->max_uses }} fois</li>
                        @else
                            <li>✓ À usage unique</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mb-8">
            <a href="{{ route('compensation-codes.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                ← Retour aux codes de compensation
            </a>
        </div>
    </div>
</div>

@endsection
