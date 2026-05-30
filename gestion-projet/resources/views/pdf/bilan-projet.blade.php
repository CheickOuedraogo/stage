<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; }
    .header { background: #1e40af; color: white; padding: 20px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
    .header .subtitle { font-size: 10px; opacity: 0.85; }
    .badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 20px; font-size: 10px; margin-top: 6px; }
    .section { margin: 0 24px 18px; }
    .section-title { font-size: 11px; font-weight: bold; color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .info-item { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; }
    .info-label { font-size: 9px; color: #64748b; margin-bottom: 2px; }
    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .stat-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px; }
    .stat-box.success { background: #f0fdf4; border-color: #bbf7d0; }
    .stat-box.warn { background: #fef3c7; border-color: #fde68a; }
    .stat-box.danger { background: #fee2e2; border-color: #fca5a5; }
    .stat-label { font-size: 9px; color: #64748b; margin-bottom: 4px; }
    .stat-value { font-size: 13px; font-weight: bold; }
    .stat-value.blue { color: #1e40af; }
    .stat-value.success { color: #065f46; }
    .stat-value.warn { color: #92400e; }
    .stat-value.danger { color: #991b1b; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 4px; }
    thead tr { background: #1e40af; color: white; }
    thead th { padding: 7px 8px; text-align: left; font-weight: 600; }
    tbody tr:nth-child(even) { background: #f1f5f9; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    tfoot tr { background: #e2e8f0; font-weight: bold; }
    tfoot td { padding: 7px 8px; }
    .text-right { text-align: right; }
    .empty-notice { font-size: 10px; color: #94a3b8; font-style: italic; padding: 8px 0; }
    .footer { margin: 20px 24px 0; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>

<div class="header">
    <h1>Bilan de clôture — {{ $bilan['projet']['titre'] }}</h1>
    <div class="subtitle">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} — Université Joseph KI-ZERBO / CIFEU</div>
    <div class="badge">{{ $bilan['projet']['libelle_statut'] }}</div>
    @if($bilan['projet']['statut_final_label'])
    <div class="badge" style="background: {{ $bilan['projet']['statut_final'] === 'succes' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' }}; margin-left: 6px;">
        {{ $bilan['projet']['statut_final_label'] }}
    </div>
    @endif
</div>

{{-- Informations projet --}}
<div class="section">
    <div class="section-title">Informations du projet</div>
    <div class="info-row">
        <div class="info-item">
            <div class="info-label">Porteur de projet</div>
            <strong>{{ $bilan['projet']['porteur'] }}</strong>
        </div>
        <div class="info-item">
            <div class="info-label">Date de début</div>
            {{ $bilan['projet']['date_debut'] ? \Carbon\Carbon::parse($bilan['projet']['date_debut'])->format('d/m/Y') : '—' }}
        </div>
        <div class="info-item">
            <div class="info-label">Date de fin prévue</div>
            {{ $bilan['projet']['date_fin_prevue'] ? \Carbon\Carbon::parse($bilan['projet']['date_fin_prevue'])->format('d/m/Y') : '—' }}
        </div>
        <div class="info-item">
            <div class="info-label">Date de clôture réelle</div>
            <strong>{{ $bilan['projet']['date_fin_reelle'] ? \Carbon\Carbon::parse($bilan['projet']['date_fin_reelle'])->format('d/m/Y') : '—' }}</strong>
        </div>
    </div>
</div>

{{-- Synthèse financière --}}
<div class="section">
    <div class="section-title">Synthèse financière</div>
    @php
        $analyse = $bilan['analyse_ecarts'];
        $ecartBudget = $analyse['ecart_budget'];
        $ecartTemps = $analyse['ecart_temps_jours'] ?? 0;
    @endphp
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Budget prévu (conventions)</div>
            <div class="stat-value blue">{{ number_format($analyse['budget_prevu'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total versements reçus</div>
            <div class="stat-value blue">{{ number_format($analyse['total_versements'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-box {{ $analyse['taux_execution'] <= 100 ? 'success' : 'danger' }}">
            <div class="stat-label">Taux d'exécution</div>
            <div class="stat-value {{ $analyse['taux_execution'] <= 100 ? 'success' : 'danger' }}">{{ $analyse['taux_execution'] }}%</div>
            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">{{ number_format($analyse['total_consomme'], 0, ',', ' ') }} FCFA consommés</div>
        </div>
        <div class="stat-box {{ $ecartBudget >= 0 ? 'success' : 'danger' }}">
            <div class="stat-label">Écart budgétaire</div>
            <div class="stat-value {{ $ecartBudget >= 0 ? 'success' : 'danger' }}">
                {{ $ecartBudget >= 0 ? '+' : '' }}{{ number_format($ecartBudget, 0, ',', ' ') }} FCFA
            </div>
            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">{{ $ecartBudget >= 0 ? 'Sous-consommation' : 'Dépassement' }}</div>
        </div>
    </div>
    @if($analyse['ecart_temps_label'])
    <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
        Délais : <strong style="color: {{ $ecartTemps > 0 ? '#991b1b' : '#065f46' }}">{{ $analyse['ecart_temps_label'] }}</strong>
    </div>
    @endif
</div>

{{-- Conventions --}}
@if(!empty($bilan['conventions']))
<div class="section">
    <div class="section-title">Conventions de financement</div>
    <table>
        <thead>
            <tr>
                <th>Bailleur</th>
                <th>Convention</th>
                <th class="text-right">Montant prévu (FCFA)</th>
                <th class="text-right">Versements reçus (FCFA)</th>
                <th class="text-right">Dépenses (FCFA)</th>
                <th class="text-right">Solde (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bilan['conventions'] as $c)
            <tr>
                <td>{{ $c['bailleur_sigle'] ?? $c['bailleur'] }}</td>
                <td>{{ $c['titre'] }}</td>
                <td class="text-right">{{ number_format($c['montant_fcfa'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($c['total_versements'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($c['total_consomme'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($c['total_versements'] - $c['total_consomme'], 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td class="text-right">{{ number_format(array_sum(array_column($bilan['conventions'], 'montant_fcfa')), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format(array_sum(array_column($bilan['conventions'], 'total_versements')), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($bilan['analyse_ecarts']['total_consomme'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format(array_sum(array_column($bilan['conventions'], 'total_versements')) - array_sum(array_column($bilan['conventions'], 'total_consomme')), 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Demandes de dépense terminées --}}
<div class="section">
    <div class="section-title">Demandes de dépense terminées</div>
    @if(!empty($bilan['demandes']))
    <table>
        <thead>
            <tr>
                <th>Objet</th>
                <th>Convention</th>
                <th>Rubrique</th>
                <th class="text-right">Montant (FCFA)</th>
                <th>Date de paiement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bilan['demandes'] as $d)
            <tr>
                <td>{{ $d['objet'] }}</td>
                <td>{{ $d['convention'] }}</td>
                <td>{{ $d['rubrique'] }}</td>
                <td class="text-right">{{ number_format($d['montant'], 0, ',', ' ') }}</td>
                <td>{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total demandes</td>
                <td class="text-right">{{ number_format(array_sum(array_column($bilan['demandes'], 'montant')), 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p class="empty-notice">Aucune demande de dépense terminée.</p>
    @endif
</div>

{{-- Paiements directs --}}
<div class="section">
    <div class="section-title">Paiements directs</div>
    @if(!empty($bilan['paiements_directs']))
    <table>
        <thead>
            <tr>
                <th>Objet</th>
                <th>Convention</th>
                <th>Rubrique</th>
                <th class="text-right">Montant (FCFA)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bilan['paiements_directs'] as $p)
            <tr>
                <td>{{ $p['objet'] }}</td>
                <td>{{ $p['convention'] }}</td>
                <td>{{ $p['rubrique'] ?? '—' }}</td>
                <td class="text-right">{{ number_format($p['montant'], 0, ',', ' ') }}</td>
                <td>{{ $p['date_paiement'] ? \Carbon\Carbon::parse($p['date_paiement'])->format('d/m/Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total paiements directs</td>
                <td class="text-right">{{ number_format(array_sum(array_column($bilan['paiements_directs'], 'montant')), 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p class="empty-notice">Aucun paiement direct enregistré.</p>
    @endif
</div>

<div class="footer">
    Document généré automatiquement par le système CIFEU — DSI Université Joseph KI-ZERBO
</div>
</body>
</html>
