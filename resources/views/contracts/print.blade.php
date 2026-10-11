<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->contract_number }} - SolarShare</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
        }
        
        .company-info h1 {
            color: #0066cc;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .company-info p {
            color: #666;
            font-size: 13px;
        }
        
        .contract-details {
            text-align: right;
        }
        
        .contract-details .label {
            color: #666;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .contract-details .value {
            font-size: 16px;
            color: #333;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 12px 15px;
            border-left: 4px solid #0066cc;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        .section-content {
            padding: 0 15px;
        }
        
        .row {
            display: flex;
            margin-bottom: 12px;
        }
        
        .row.two-cols {
            justify-content: space-between;
        }
        
        .col {
            flex: 1;
        }
        
        .col.half {
            flex: 0 1 48%;
        }
        
        .label {
            color: #0066cc;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
        }
        
        .value {
            color: #333;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background-color: #0066cc;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: bold;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .total-row td {
            font-weight: bold;
            background-color: #f0f0f0;
            border-top: 2px solid #0066cc;
        }
        
        .terms {
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 4px;
            font-size: 12px;
            line-height: 1.8;
            color: #555;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .footer {
            border-top: 2px solid #e0e0e0;
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .footer-left {
            float: left;
            text-align: left;
        }
        
        .footer-right {
            float: right;
            text-align: right;
        }
        
        .footer::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .highlight {
            background-color: #fffacd;
            padding: 2px 4px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                max-width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        
        .print-button button {
            background-color: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .print-button button:hover {
            background-color: #0052a3;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button onclick="window.print()">Imprimer ce contrat</button>
        <button onclick="window.history.back()" style="background-color: #666; margin-left: 10px;">Retour</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>SolarShare</h1>
                <p>Plateforme de location d'équipements solaires</p>
                <p>support@solarshare.fr</p>
            </div>
            <div class="contract-details">
                <div class="label">Numéro de Contrat</div>
                <div class="value">{{ $contract->contract_number }}</div>
                
                <div class="label" style="margin-top: 15px;">Statut</div>
                <div class="value">
                    <span class="status-badge 
                        @if($contract->status === 'active') status-active
                        @elseif($contract->status === 'completed') status-completed
                        @else status-cancelled
                        @endif">
                        @if($contract->status === 'active')
                            ACTIF
                        @elseif($contract->status === 'completed')
                            TERMINÉ
                        @else
                            ANNULÉ
                        @endif
                    </span>
                </div>
                
                <div class="label" style="margin-top: 15px;">Date d'Émission</div>
                <div class="value">{{ $contract->created_at->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Section: Parties -->
        <div class="section">
            <div class="section-title">Parties au Contrat</div>
            <div class="section-content">
                <div class="row two-cols">
                    <div class="col half">
                        <div class="label">Prestataire</div>
                        <div class="value">SolarShare SARL</div>
                        <p class="value" style="font-size: 12px; color: #666; margin-top: 5px;">
                            Plateforme de location d'équipements
                        </p>
                    </div>
                    <div class="col half">
                        <div class="label">Locataire</div>
                        <div class="value">{{ $contract->user->name }}</div>
                        <p class="value" style="font-size: 12px; color: #666; margin-top: 5px;">
                            {{ $contract->user->email }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Équipement -->
        <div class="section">
            <div class="section-title">Équipement Loué</div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th style="text-align: center;">Catégorie</th>
                            <th style="text-align: right;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ $contract->equipment->name ?? $contract->equipment->title }}</strong>
                                <p style="color: #666; font-size: 12px; margin-top: 3px;">
                                    @if($contract->equipment->description)
                                        {{ Str::limit($contract->equipment->description, 100) }}
                                    @endif
                                </p>
                            </td>
                            <td style="text-align: center;">
                                @if($contract->equipment->category)
                                    {{ $contract->equipment->category->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <strong>{{ number_format($contract->amount, 2, ',', ' ') }} TND</strong>
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2" style="text-align: right;">Total à Payer:</td>
                            <td style="text-align: right; font-size: 16px; color: #0066cc;">
                                {{ number_format($contract->amount, 2, ',', ' ') }} TND
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section: Détails de Location -->
        <div class="section">
            <div class="section-title">Période de Location</div>
            <div class="section-content">
                <div class="row two-cols">
                    <div class="col half">
                        <div class="label">Date de Début</div>
                        <div class="value">{{ $contract->start_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="col half">
                        <div class="label">Date de Fin</div>
                        <div class="value">{{ $contract->end_date->format('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="row" style="margin-top: 15px;">
                    <div class="col">
                        <div class="label">Durée de Location</div>
                        <div class="value">{{ $contract->start_date->diffInDays($contract->end_date) }} jours</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Conditions Générales -->
        <div class="section">
            <div class="section-title">Conditions Générales</div>
            <div class="section-content">
                <div class="terms">{{ $contract->terms_and_conditions }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                <strong>Signataires:</strong><br><br>
                Pour SolarShare: ____________________<br>
                Date: ____________________
            </div>
            <div class="footer-right">
                <strong>Locataire: ____________________</strong><br>
                Date: ____________________<br><br>
            </div>
            <p style="text-align: center; margin-top: 30px; font-size: 11px; color: #999;">
                Ce contrat a été généré automatiquement par SolarShare.<br>
                Contrat N° {{ $contract->contract_number }} | Créé le {{ $contract->created_at->format('d/m/Y \à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
