<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 8mm 8mm 12mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7.5px; color: #1e293b; line-height: 1.3; }

    .header { background: linear-gradient(135deg, #1e40af, #1d4ed8); color: white; padding: 8px 12px; margin-bottom: 6px; }
    .header h1 { font-size: 11px; font-weight: bold; }
    .header .subtitle { font-size: 7px; opacity: 0.85; }
    .header .badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 8px; font-size: 7px; margin-top: 3px; }

    .section { margin: 0 10px 5px; }
    .section-title { font-size: 8px; font-weight: bold; color: #1e40af; border-bottom: 1.5px solid #1e40af; padding-bottom: 1px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; }

    /* Projet info - inline */
    .info-line { font-size: 7.5px; color: #475569; margin-bottom: 3px; }
    .info-line strong { color: #0f172a; }

    /* 2x2 stat cards */
    .stats-grid { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 4px; }
    .stat-card { width: calc(50% - 2px); border-radius: 3px; padding: 5px 7px; }
    .stat-card.blue { background: #eff6ff; border: 0.5px solid #bfdbfe; }
    .stat-card.purple { background: #f5f3ff; border: 0.5px solid #ddd6fe; }
    .stat-card.gray { background: #f8fafc; border: 0.5px solid #e2e8f0; }
    .stat-card.green { background: #f0fdf4; border: 0.5px solid #bbf7d0; }
    .stat-card.red { background: #fef2f2; border: 0.5px solid #fecaca; }
    .stat-label { font-size: 6px; color: #64748b; text-transform: uppercase; letter-spacing: 0.2px; }
    .stat-value { font-size: 9px; font-weight: bold; }
    .stat-value.blue { color: #1e40af; }
    .stat-value.purple { color: #6d28d9; }
    .stat-value.gray { color: #334155; }
    .stat-value.green { color: #065f46; }
    .stat-value.red { color: #991b1b; }
    .stat-sub { font-size: 6px; color: #64748b; }

    /* Bar */
    .bar-row { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; font-size: 7px; }
    .bar-row span { white-space: nowrap; }
    .bar-container { flex: 1; background: #f1f5f9; border-radius: 3px; height: 6px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 3px; }
    .bar-fill.green { background: #10b981; }
    .bar-fill.blue { background: #3b82f6; }
    .bar-fill.amber { background: #f59e0b; }
    .bar-fill.red { background: #ef4444; }

    /* Tables compactes */
    table { width: 100%; border-collapse: collapse; font-size: 7px; }
    thead tr { background: #1e40af; color: white; }
    thead th { padding: 3px 5px; text-align: left; font-weight: 600; font-size: 6.5px; text-transform: uppercase; letter-spacing: 0.2px; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 2.5px 5px; border-bottom: 0.5px solid #f1f5f9; }
    tfoot tr { background: #e2e8f0; font-weight: bold; }
    tfoot td { padding: 3px 5px; font-size: 7px; }
    .text-right { text-align: right; }
    .text-mono { font-family: "DejaVu Sans Mono", monospace; }

    .taux-badge { display: inline-block; padding: 0 5px; border-radius: 6px; font-size: 6.5px; font-weight: 600; }
    .taux-badge.green { background: #d1fae5; color: #065f46; }
    .taux-badge.amber { background: #fef3c7; color: #92400e; }
    .taux-badge.red { background: #fee2e2; color: #991b1b; }

    .alert { background: #fffbeb; border: 0.5px solid #fde68a; border-radius: 3px; padding: 4px 6px; margin-bottom: 4px; font-size: 7px; }
    .alert-title { font-weight: 600; color: #92400e; }
    .alert-text { color: #a16207; }

    .empty-notice { font-size: 7px; color: #94a3b8; font-style: italic; padding: 3px 0; }

    .footer { position: fixed; bottom: 0; left: 8mm; right: 8mm; text-align: center; border-top: 0.5px solid #e2e8f0; padding-top: 3px; font-size: 6px; color: #94a3b8; }
</style>
</head>
<body>

<div class="header">
    <h1>Bilan de clôture — {{ $bilan['projet']['titre'] }}</h1>
    <div class="subtitle">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} — Université Joseph KI-ZERBO / CIFEU</div>
    @php $p = $bilan['projet']; @endphp
    <div class="badge">{{ $p['libelle_statut'] }}</div>
    @if($p['statut_final_label'])
    <div class="badge" style="background: {{ $p['statut_final'] === 'succes' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' }}; margin-left: 3px;">
        {{ $p['statut_final_label'] }}
    </div>
    @endif
</div>

{{-- Info projet inline --}}
<div class="section">
    <div class="info-line">
        <strong>Porteur :</strong> {{ $p['porteur'] }} &nbsp;|&nbsp;
        <strong>Début :</strong> {{ $p['date_debut'] ? \Carbon\Carbon::parse($p['date_debut'])->format('d/m/Y') : '—' }} &nbsp;|&nbsp;
        <strong>Fin prévue :</strong> {{ $p['date_fin_prevue'] ? \Carbon\Carbon::parse($p['date_fin_prevue'])->format('d/m/Y') : '—' }} &nbsp;|&nbsp;
        <strong>Clôture :</strong> {{ $p['date_fin_reelle'] ? \Carbon\Carbon::parse($p['date_fin_reelle'])->format('d/m/Y') : '—' }}
    </div>
</div>

{{-- Synthèse --}}
<div class="section">
    <div class="section-title">Synthèse financière</div>
    @php
        $a = $bilan['analyse_ecarts'];
        $barColor = $a['taux_execution'] >= 100 ? 'red' : ($a['taux_execution'] >= 90 ? 'amber' : ($a['taux_execution'] >= 70 ? 'blue' : 'green'));
        $ecartPositif = $a['ecart_budget'] >= 0;
    @endphp
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Budget prévu</div>
            <div class="stat-value blue">{{ number_format($a['budget_prevu'], 0, ',', ' ') }} F</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-label">Versements</div>
            <div class="stat-value purple">{{ number_format($a['total_versements'], 0, ',', ' ') }} F</div>
        </div>
        <div class="stat-card gray">
            <div class="stat-label">Consommé</div>
            <div class="stat-value gray">{{ number_format($a['total_consomme'], 0, ',', ' ') }} F</div>
            <div class="stat-sub">{{ $a['taux_execution'] }}% du budget</div>
        </div>
        <div class="stat-card {{ $ecartPositif ? 'green' : 'red' }}">
            <div class="stat-label">Écart</div>
            <div class="stat-value {{ $ecartPositif ? 'green' : 'red' }}">{{ $ecartPositif ? '+' : '' }}{{ number_format($a['ecart_budget'], 0, ',', ' ') }} F</div>
            <div class="stat-sub">{{ $ecartPositif ? 'Sous-consommation' : 'Dépassement' }}</div>
        </div>
    </div>

    <div class="bar-row">
        <span>Taux exécution : <strong>{{ $a['taux_execution'] }}%</strong></span>
        <div class="bar-container"><div class="bar-fill {{ $barColor }}" style="width: {{ min(100, $a['taux_execution']) }}%;"></div></div>
        @if($a['ecart_temps_label'])
        <span>&nbsp;|&nbsp; Délais : <strong style="color: {{ ($a['ecart_temps_jours'] ?? 0) > 0 ? '#991b1b' : '#065f46' }}">{{ $a['ecart_temps_label'] }}</strong></span>
        @endif
    </div>
</div>

{{-- Alert --}}
@if($a['conventions_depassent_budget_initial'])
<div class="section">
    <div class="alert">
        <div class="alert-title">⚠ Financement &gt; budget initial</div>
        <div class="alert-text">Conventions : {{ number_format($a['budget_prevu'], 0, ',', ' ') }} F &gt; budget initial {{ number_format($a['budget_initial'], 0, ',', ' ') }} F (+{{ number_format($a['budget_prevu'] - $a['budget_initial'], 0, ',', ' ') }} F).</div>
    </div>
</div>
@endif

{{-- Conventions --}}
@if(!empty($bilan['conventions']))
<div class="section">
    <div class="section-title">Conventions de financement</div>
    <table>
        <thead>
            <tr>
                <th>Bailleur</th>
                <th>Convention</th>
                <th class="text-right">Montant</th>
                <th class="text-right">Versements</th>
                <th class="text-right">Dépenses</th>
                <th class="text-right">Reliquat</th>
                <th class="text-right">Taux</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bilan['conventions'] as $c)
            @php $tauxClass = $c['taux_execution'] >= 100 ? 'red' : ($c['taux_execution'] >= 90 ? 'amber' : 'green'); @endphp
            <tr>
                <td><strong>{{ $c['bailleur_sigle'] ?? $c['bailleur'] }}</strong></td>
                <td>{{ $c['titre'] }}</td>
                <td class="text-right text-mono">{{ number_format($c['montant_fcfa'], 0, ',', ' ') }}</td>
                <td class="text-right text-mono">{{ number_format($c['total_versements'], 0, ',', ' ') }}</td>
                <td class="text-right text-mono">{{ number_format($c['total_consomme'], 0, ',', ' ') }}</td>
                <td class="text-right text-mono" style="color: {{ $c['solde_engagement'] >= 0 ? '#065f46' : '#991b1b' }};">{{ $c['solde_engagement'] >= 0 ? '+' : '' }}{{ number_format($c['solde_engagement'], 0, ',', ' ') }}</td>
                <td class="text-right"><span class="taux-badge {{ $tauxClass }}">{{ $c['taux_execution'] }}%</span></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td class="text-right text-mono">{{ number_format(array_sum(array_column($bilan['conventions'], 'montant_fcfa')), 0, ',', ' ') }}</td>
                <td class="text-right text-mono">{{ number_format(array_sum(array_column($bilan['conventions'], 'total_versements')), 0, ',', ' ') }}</td>
                <td class="text-right text-mono">{{ number_format($a['total_consomme'], 0, ',', ' ') }}</td>
                <td class="text-right text-mono">{{ number_format(array_sum(array_column($bilan['conventions'], 'solde_engagement')), 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Demandes --}}
<div class="section">
    <div class="section-title">Demandes de dépense terminées</div>
    @if(!empty($bilan['demandes']))
    <table>
        <thead>
            <tr>
                <th>Objet</th>
                <th>Rubrique</th>
                <th>Conv.</th>
                <th class="text-right">Montant</th>
                <th>Paiement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bilan['demandes'] as $d)
            <tr>
                <td>{{ $d['objet'] }}</td>
                <td>{{ $d['rubrique'] }}</td>
                <td>{{ $d['convention'] }}</td>
                <td class="text-right text-mono">{{ number_format($d['montant'], 0, ',', ' ') }}</td>
                <td>{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total demandes</td>
                <td class="text-right text-mono">{{ number_format(array_sum(array_column($bilan['demandes'], 'montant')), 0, ',', ' ') }}</td>
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
                <th>Rubrique</th>
                <th>Conv.</th>
                <th class="text-right">Montant</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bilan['paiements_directs'] as $p)
            <tr>
                <td>{{ $p['objet'] }}</td>
                <td>{{ $p['rubrique'] ?? '—' }}</td>
                <td>{{ $p['convention'] }}</td>
                <td class="text-right text-mono">{{ number_format($p['montant'], 0, ',', ' ') }}</td>
                <td>{{ $p['date_paiement'] ? \Carbon\Carbon::parse($p['date_paiement'])->format('d/m/Y') : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total paiements directs</td>
                <td class="text-right text-mono">{{ number_format(array_sum(array_column($bilan['paiements_directs'], 'montant')), 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p class="empty-notice">Aucun paiement direct enregistré.</p>
    @endif
</div>

<div class="footer">
    Document généré par le système CIFEU — Université Joseph KI-ZERBO &nbsp;|&nbsp; Page {PAGE_NUM} / {PAGE_COUNT}
</div>
</body>
</html>
