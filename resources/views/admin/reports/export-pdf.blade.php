<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport SolarShare</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
            background: white;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #f59e0b;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
            margin: 5px 0;
        }

        .period-info {
            background: #f3f4f6;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .period-info strong {
            color: #1f2937;
        }

        .section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .metric-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 4px;
        }

        .metric-card .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .metric-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }

        .metric-card .unit {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 13px;
        }

        table thead {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }

        table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #1f2937;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
        }

        .status-en_attente { background: #fef3c7; color: #92400e; }
        .status-confirmee { background: #dbeafe; color: #1e40af; }
        .status-en_cours { background: #e9d5ff; color: #6b21a8; }
        .status-terminee { background: #dcfce7; color: #15803d; }
        .status-litige { background: #fee2e2; color: #991b1b; }
        .status-annulea { background: #f3f4f6; color: #374151; }
        .status-refusee { background: #fee2e2; color: #991b1b; }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .limited-info {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 15px 0;
            font-size: 12px;
            color: #92400e;
        }

        @media print {
            body {
                background: white;
            }

            .container {
                max-width: 100%;
                padding: 0;
            }

            .section {
                page-break-inside: avoid;
            }

            table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>📊 Rapport SolarShare</h1>
            <p>Analyse détaillée de votre plateforme de location d'équipements</p>
        </div>

        {{-- Period Info --}}
        <div class="period-info">
            <strong>Période du rapport:</strong> {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}<br>
            <strong>Généré le:</strong> {{ $generatedAt->format('d/m/Y \à H:i:s') }}
        </div>

        {{-- Reservations Section --}}
        <div class="section">
            <div class="section-title">Réservations</div>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="label">Total</div>
                    <div class="value">{{ $stats['reservations']['total'] }}</div>
                </div>
                <div class="metric-card">
                    <div class="label">Complétées</div>
                    <div class="value">{{ $stats['reservations']['completed'] }}</div>
                </div>
                <div class="metric-card">
                    <div class="label">Durée moyenne</div>
                    <div class="value">{{ $stats['reservations']['avg_duration_days'] }}</div>
                    <div class="unit">jours</div>
                </div>
                <div class="metric-card">
                    <div class="label">Taux de complétion</div>
                    <div class="value">
                        @if($stats['reservations']['total'] > 0)
                            {{ round(($stats['reservations']['completed'] / $stats['reservations']['total']) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th class="text-right">Nombre</th>
                        <th class="text-right">Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['reservations']['by_status'] as $status => $count)
                        @php
                            $percentage = $stats['reservations']['total'] > 0 ? ($count / $stats['reservations']['total'] * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="status-badge status-{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td class="text-right">{{ $count }}</td>
                            <td class="text-right">{{ round($percentage, 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Aucune réservation</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Revenue Section --}}
        <div class="section">
            <div class="section-title">Revenus</div>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="label">Revenu collecté</div>
                    <div class="value">{{ number_format($stats['revenue']['total'], 2, ',', ' ') }}</div>
                    <div class="unit">TND</div>
                </div>
                <div class="metric-card">
                    <div class="label">Remboursements approuvés</div>
                    <div class="value">{{ number_format($stats['revenue']['refunded'], 2, ',', ' ') }}</div>
                    <div class="unit">TND</div>
                </div>
                @if($stats['reservations']['total'] > 0)
                    <div class="metric-card">
                        <div class="label">Revenu moyen par réservation</div>
                        <div class="value">{{ number_format($stats['revenue']['total'] / $stats['reservations']['total'], 2, ',', ' ') }}</div>
                        <div class="unit">TND</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Reclamations Section --}}
        <div class="section">
            <div class="section-title">Réclamations</div>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="label">Total</div>
                    <div class="value">{{ $stats['reclamations']['total'] }}</div>
                </div>
                <div class="metric-card">
                    <div class="label">Taux de réclamation</div>
                    <div class="value">{{ $stats['reclamations']['rate'] }}%</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th class="text-right">Nombre</th>
                        <th class="text-right">Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['reclamations']['by_status'] as $status => $count)
                        @php
                            $percentage = $stats['reclamations']['total'] > 0 ? ($count / $stats['reclamations']['total'] * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="status-badge status-{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td class="text-right">{{ $count }}</td>
                            <td class="text-right">{{ round($percentage, 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Aucune réclamation</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($stats['reclamations']['by_type'])
                <table>
                    <thead>
                        <tr>
                            <th>Type de problème</th>
                            <th class="text-right">Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['reclamations']['by_type'] as $type => $count)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                <td class="text-right">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Aucun incident</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Equipment Rankings by Reservations --}}
        <div class="section">
            <div class="section-title">Équipements les plus demandés (par nombre de réservations)</div>
            <table>
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Équipement</th>
                        <th class="text-center">Réservations</th>
                        <th class="text-right">Revenu (TND)</th>
                        <th class="text-right">Prix/jour (TND)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['equipment_rankings_by_reservations'] ?? [] as $index => $equipment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $equipment['name'] }} (ID: {{ $equipment['id'] }})</td>
                            <td class="text-center">{{ $equipment['reservation_count'] }}</td>
                            <td class="text-right">{{ number_format($equipment['revenue'], 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($equipment['price_per_day'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Aucun équipement loué durant cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Equipment Rankings by Revenue --}}
        <div class="section">
            <div class="section-title">Équipements générant le plus de revenus</div>
            <table>
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Équipement</th>
                        <th class="text-center">Réservations</th>
                        <th class="text-right">Revenu (TND)</th>
                        <th class="text-right">Prix/jour (TND)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['equipment_rankings'] as $index => $equipment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $equipment['name'] }} (ID: {{ $equipment['id'] }})</td>
                            <td class="text-center">{{ $equipment['reservation_count'] }}</td>
                            <td class="text-right">{{ number_format($equipment['revenue'], 2, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($equipment['price_per_day'], 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Aucun équipement loué durant cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Notes --}}
        <div class="section">
            <div class="limited-info">
                <strong>Important:</strong> Ce rapport contient les statistiques collectées et vérifiées pour la période sélectionnée.
                Les revenus incluent les paiements avec le statut "payé" (TND). Les réclamations sont comptabilisées par statut.
                Les taux de réclamation sont calculés comme: (total réclamations / total réservations) × 100.
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Document généré par SolarShare Admin Reports</p>
            <p>© 2026 SolarShare. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
