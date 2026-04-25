<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; }
    .header { background: #1e40af; color: white; padding: 20px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
    .header p { font-size: 10px; opacity: 0.85; }
    .section { margin: 0 24px 18px; }
    .section-title { font-size: 11px; font-weight: bold; color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
    .stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; }
    .stat-label { font-size: 9px; color: #64748b; margin-bottom: 4px; }
    .stat-value { font-size: 13px; font-weight: bold; color: #1e293b; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    thead tr { background: #1e40af; color: white; }
    thead th { padding: 7px 8px; text-align: left; font-weight: 600; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 20px; font-size: 9px; font-weight: 600; }
    .badge-ok { background: #dcfce7; color: #166534; }
    .badge-warn { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .footer { margin: 20px 24px 0; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>

<div class="header">
    <h1>Rapport d'exécution budgétaire</h1>
    <p>Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} — Université Joseph KI-ZERBO / CIFEU</p>
</div>

@foreach($projets as $projet)
<div class="section">
    <div class="section-title">{{ $projet['titre'] }}</div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Budget prévu (conventions)</div>
            <div class="stat-value">{{ number_format($projet['budget_prevu'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Versements reçus</div>
            <div class="stat-value">{{ number_format($projet['total_versements'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total dépensé</div>
            <div class="stat-value">{{ number_format($projet['total_depenses'], 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    @if(!empty($projet['rubriques']))
    <table>
        <thead>
            <tr>
                <th>Rubrique</th>
                <th>Convention / Bailleur</th>
                <th class="text-right">Prévu (FCFA)</th>
                <th class="text-right">Dépensé (FCFA)</th>
                <th class="text-right">Disponible</th>
                <th class="text-center">Taux</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projet['rubriques'] as $r)
            @php
                $taux = $r['montant_prevu'] > 0 ? round(($r['consomme'] / $r['montant_prevu']) * 100) : 0;
                $classe = $taux >= 100 ? 'badge-danger' : ($taux >= 80 ? 'badge-warn' : 'badge-ok');
            @endphp
            <tr>
                <td>{{ $r['libelle'] }}</td>
                <td>{{ $r['convention'] }}</td>
                <td class="text-right">{{ number_format($r['montant_prevu'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($r['consomme'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format(max(0, $r['montant_prevu'] - $r['consomme']), 0, ',', ' ') }}</td>
                <td class="text-center"><span class="badge {{ $classe }}">{{ $taux }}%</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endforeach

<div class="footer">
    Document généré automatiquement par le système CIFEU — DSI Université Joseph KI-ZERBO
</div>
</body>
</html>
