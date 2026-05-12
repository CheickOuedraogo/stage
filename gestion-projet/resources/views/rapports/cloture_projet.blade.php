<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; }
    .header { background: #065f46; color: white; padding: 20px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
    .header p { font-size: 10px; opacity: 0.85; }
    .section { margin: 0 24px 18px; }
    .section-title { font-size: 11px; font-weight: bold; color: #065f46; border-bottom: 2px solid #065f46; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .stat-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; }
    .stat-box.warn { background: #fef3c7; border-color: #fde68a; }
    .stat-box.danger { background: #fee2e2; border-color: #fca5a5; }
    .stat-label { font-size: 9px; color: #64748b; margin-bottom: 4px; }
    .stat-value { font-size: 13px; font-weight: bold; }
    .stat-value.success { color: #065f46; }
    .stat-value.warn { color: #92400e; }
    .stat-value.danger { color: #991b1b; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    thead tr { background: #065f46; color: white; }
    thead th { padding: 7px 8px; text-align: left; font-weight: 600; }
    tbody tr:nth-child(even) { background: #f0fdf4; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    .text-right { text-align: right; }
    .info-row { display: flex; gap: 20px; margin-bottom: 12px; }
    .info-item { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; }
    .info-label { font-size: 9px; color: #64748b; margin-bottom: 2px; }
    .footer { margin: 20px 24px 0; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>

<div class="header">
    <h1>Rapport de clôture de projet</h1>
    <p>Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} — Université Joseph KI-ZERBO / CIFEU</p>
</div>

<div class="section">
    <div class="section-title">Informations du projet</div>
    <div class="info-row">
        <div class="info-item">
            <div class="info-label">Titre du projet</div>
            <strong>{{ $projet['titre'] }}</strong>
        </div>
        <div class="info-item">
            <div class="info-label">Porteur</div>
            {{ $projet['porteur'] }}
        </div>
        <div class="info-item">
            <div class="info-label">Statut</div>
            {{ $projet['status_label'] }}
        </div>
    </div>
    <div class="info-row">
        <div class="info-item">
            <div class="info-label">Date de début</div>
            {{ $projet['date_debut'] ?? '—' }}
        </div>
        <div class="info-item">
            <div class="info-label">Date de fin prévue</div>
            {{ $projet['date_fin_prevue'] ?? '—' }}
        </div>
        <div class="info-item">
            <div class="info-label">Date de clôture réelle</div>
            <strong>{{ $projet['date_fin_reelle'] ?? 'Non clôturé' }}</strong>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Analyse des écarts</div>

    @php
        $ecartBudget = $analyse['ecart_budget'];
        $ecartTemps = $analyse['ecart_temps_jours'] ?? 0;
        $budgetClasse = $ecartBudget >= 0 ? '' : 'danger';
        $tempsClasse = $ecartTemps <= 0 ? '' : ($ecartTemps <= 30 ? 'warn' : 'danger');
    @endphp

    <div class="stats-grid">
        <div class="stat-box {{ $budgetClasse }}">
            <div class="stat-label">Écart budgétaire</div>
            <div class="stat-value {{ $ecartBudget >= 0 ? 'success' : 'danger' }}">
                {{ $ecartBudget >= 0 ? '+' : '' }}{{ number_format($ecartBudget, 0, ',', ' ') }} FCFA
            </div>
            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                {{ $ecartBudget >= 0 ? 'Sous-consommation' : 'Dépassement budgétaire' }}
            </div>
        </div>
        <div class="stat-box {{ $tempsClasse }}">
            <div class="stat-label">Écart temporel</div>
            <div class="stat-value {{ $ecartTemps <= 0 ? 'success' : ($ecartTemps <= 30 ? 'warn' : 'danger') }}">
                {{ $analyse['ecart_temps_label'] ?? '—' }}
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Budget prévu</div>
            <div class="stat-value success">{{ number_format($analyse['budget_prevu'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Taux d'exécution</div>
            <div class="stat-value {{ $analyse['taux_execution'] <= 100 ? 'success' : 'danger' }}">
                {{ $analyse['taux_execution'] }}%
            </div>
        </div>
    </div>
</div>

@if(!empty($conventions))
<div class="section">
    <div class="section-title">Conventions et financement</div>
    <table>
        <thead>
            <tr>
                <th>Bailleur</th>
                <th>Forme</th>
                <th class="text-right">Montant prévu (FCFA)</th>
                <th class="text-right">Versements reçus (FCFA)</th>
                <th class="text-right">Taux mobilisation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($conventions as $c)
            @php $taux = $c['montant_fcfa'] > 0 ? round(($c['total_versements'] / $c['montant_fcfa']) * 100) : 0; @endphp
            <tr>
                <td>{{ $c['bailleur'] }}</td>
                <td>{{ $c['forme_label'] }}</td>
                <td class="text-right">{{ number_format($c['montant_fcfa'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($c['total_versements'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ $taux }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    Document généré automatiquement par le système CIFEU — DSI Université Joseph KI-ZERBO
</div>
</body>
</html>
