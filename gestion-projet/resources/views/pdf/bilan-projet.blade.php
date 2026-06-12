<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 6mm 8mm 10mm; size: A4 landscape; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7.5px; color: #1e293b; line-height: 1.3; }

    /* ── HEADER ── */
    .page-header { border-bottom: 1.5px solid #dde3ef; padding-bottom: 5px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; }
    .logo-box { width: 38px; height: 38px; border: 1.5px solid #1e3a8a; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fff; }
    .logo-text { font-size: 6px; font-weight: bold; color: #1e3a8a; text-align: center; line-height: 1.1; }
    .header-univ { flex: 0 0 auto; padding-right: 10px; border-right: 1px solid #dde3ef; }
    .univ-name { font-size: 8px; font-weight: bold; color: #1e3a8a; }
    .univ-tagline { font-size: 5.5px; color: #64748b; }
    .header-title { flex: 1; text-align: center; }
    .header-title h1 { font-size: 13px; font-weight: bold; color: #1e293b; letter-spacing: 0.5px; }
    .header-title .conv-line { font-size: 7px; color: #475569; margin-top: 2px; }
    .header-meta { flex: 0 0 auto; border: 0.8px solid #dde3ef; border-radius: 4px; padding: 4px 7px; font-size: 6.5px; color: #334155; min-width: 90px; }
    .header-meta .meta-row { display: flex; align-items: center; gap: 3px; margin-bottom: 2px; }
    .header-meta .meta-label { color: #64748b; }
    .header-meta .meta-val { font-weight: bold; }

    /* ── PROJET INFO BAR ── */
    .projet-bar { background: #f8fafc; border: 0.8px solid #e2e8f0; border-radius: 3px; padding: 4px 8px; margin-bottom: 5px; font-size: 7px; color: #475569; display: flex; gap: 16px; }
    .projet-bar strong { color: #0f172a; }

    /* ── SECTION TITLE ── */
    .section-title { font-size: 7.5px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 1.5px solid #1e3a8a; padding-bottom: 2px; margin-bottom: 5px; }

    /* ── KPI CARDS ── */
    .kpi-row { display: flex; gap: 5px; margin-bottom: 5px; }
    .kpi-card { flex: 1; border-radius: 5px; padding: 6px 8px; border: 0.8px solid; }
    .kpi-card.blue  { background: #eff6ff; border-color: #bfdbfe; }
    .kpi-card.purple{ background: #f5f3ff; border-color: #ddd6fe; }
    .kpi-card.orange{ background: #fff7ed; border-color: #fed7aa; }
    .kpi-card.green { background: #f0fdf4; border-color: #bbf7d0; }
    .kpi-label { font-size: 5.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
    .kpi-value { font-size: 10px; font-weight: bold; margin-top: 1px; }
    .kpi-value.blue   { color: #1e40af; }
    .kpi-value.purple { color: #6d28d9; }
    .kpi-value.orange { color: #ea580c; }
    .kpi-value.green  { color: #16a34a; }
    .kpi-value.red    { color: #dc2626; }

    /* Taux gauge */
    .taux-box { flex: 0 0 70px; background: #f0f6ff; border: 0.8px solid #bfdbfe; border-radius: 5px; padding: 4px; text-align: center; }
    .taux-label { font-size: 5.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 3px; }
    .gauge-svg { display: block; margin: 0 auto; }
    .taux-pct { font-size: 10px; font-weight: bold; color: #1e40af; }

    /* ── TWO-COL ── */
    .two-col { display: flex; gap: 8px; margin-bottom: 5px; }
    .two-col .col-left { flex: 1; }
    .two-col .col-right { flex: 0 0 38%; }

    /* ── TABLES ── */
    table { width: 100%; border-collapse: collapse; font-size: 7px; }
    thead tr { background: #1e3a8a; color: #fff; }
    thead th { padding: 3.5px 5px; text-align: left; font-size: 6.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.2px; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 3px 5px; border-bottom: 0.5px solid #f1f5f9; vertical-align: middle; }
    tfoot tr { background: #e2e8f0; }
    tfoot td { padding: 3.5px 5px; font-weight: bold; font-size: 7px; }
    .tr { text-align: right; }
    .tc { text-align: center; }
    .mono { font-family: DejaVu Sans Mono, monospace; }

    /* Taux badge inline */
    .tb { display: inline-block; padding: 0 5px; border-radius: 6px; font-size: 6px; font-weight: 700; }
    .tb-g { background: #dcfce7; color: #166534; }
    .tb-a { background: #fef3c7; color: #92400e; }
    .tb-r { background: #fee2e2; color: #991b1b; }

    /* Progress bar */
    .prog-wrap { background: #e2e8f0; border-radius: 3px; height: 5px; width: 55px; display: inline-block; vertical-align: middle; overflow: hidden; }
    .prog-fill { height: 100%; border-radius: 3px; background: #16a34a; }

    /* ── BOTTOM ROW ── */
    .bottom-row { display: flex; gap: 8px; margin-top: 4px; }
    .obs-box { flex: 1; border: 0.8px solid #e2e8f0; border-radius: 4px; padding: 5px 7px; }
    .obs-title { font-size: 6.5px; font-weight: bold; color: #1e3a8a; margin-bottom: 3px; }
    .obs-text { font-size: 6.5px; color: #475569; line-height: 1.4; }
    .bail-box { flex: 0 0 33%; border: 0.8px solid #e2e8f0; border-radius: 4px; padding: 5px 7px; }
    .bail-title { font-size: 6.5px; font-weight: bold; color: #1e3a8a; margin-bottom: 3px; }
    .bail-name { font-size: 7.5px; color: #334155; }

    /* ── FOOTER ── */
    .footer { position: fixed; bottom: 0; left: 8mm; right: 8mm; border-top: 0.5px solid #e2e8f0; padding-top: 2px; font-size: 5.5px; color: #94a3b8; text-align: center; }

    /* Section spacer */
    .mb { margin-bottom: 5px; }
</style>
</head>
<body>

@php
    $p   = $bilan['projet'];
    $a   = $bilan['analyse_ecarts'];
    $convs = $bilan['conventions'];
    $demandes = $bilan['demandes'];
    $directs  = $bilan['paiements_directs'];

    $taux = $a['taux_execution'];
    $tauxCap = min(100, $taux);
    // SVG gauge (semi-circle)
    $r = 22; $cx = 28; $cy = 28;
    $circum = pi() * $r;
    $filled = ($tauxCap / 100) * $circum;
    $empty  = $circum - $filled;

    $dateGeneration = \Carbon\Carbon::now()->format('d/m/Y');
    $periodeDébut   = $p['date_debut'] ? \Carbon\Carbon::parse($p['date_debut'])->format('d/m/Y') : '—';
    $periodeFinPrev = $p['date_fin_prevue'] ? \Carbon\Carbon::parse($p['date_fin_prevue'])->format('d/m/Y') : '—';
    $periodeClôture = $p['date_fin_reelle'] ? \Carbon\Carbon::parse($p['date_fin_reelle'])->format('d/m/Y') : '—';

    // Build rubrique aggregation for the expense table
    $rubriquesMap = [];
    foreach ($demandes as $d) {
        $key = $d['rubrique'] ?? 'Autres';
        if (!isset($rubriquesMap[$key])) {
            $rubriquesMap[$key] = ['libelle' => $key, 'convention' => $d['convention'], 'montant_prevu' => 0, 'depenses' => 0, 'date_paiement' => $d['date_paiement']];
        }
        $rubriquesMap[$key]['depenses'] += $d['montant'];
    }
    foreach ($directs as $d) {
        $key = $d['rubrique'] ?? 'Autres';
        if (!isset($rubriquesMap[$key])) {
            $rubriquesMap[$key] = ['libelle' => $key, 'convention' => $d['convention'], 'montant_prevu' => 0, 'depenses' => 0, 'date_paiement' => $d['date_paiement']];
        }
        $rubriquesMap[$key]['depenses'] += $d['montant'];
    }

    // Assign montant_prevu from conventions rubriques if available — use total budget proportionally
    $budgetPrevu = $a['budget_prevu'];
    $totalDepensesRubriques = array_sum(array_column($rubriquesMap, 'depenses'));

    // Observations auto
    $observations = [];
    if ($taux >= 95) $observations[] = "L'ensemble des dépenses prévues a été exécuté à {$taux}%.";
    if ($a['ecart_budget'] >= 0) $observations[] = "Le budget est entièrement consommé.";
    elseif ($a['ecart_budget'] < 0) $observations[] = "Un dépassement budgétaire de " . number_format(abs($a['ecart_budget']), 0, ',', ' ') . " F a été constaté.";
    if (($a['ecart_temps_jours'] ?? 0) > 0) $observations[] = "Retard de {$a['ecart_temps_label']}.";

    // Bailleurs list
    $bailleurs = collect($convs)->map(fn($c) => ($c['bailleur'] ?? '') . ($c['bailleur_sigle'] ? " ({$c['bailleur_sigle']})" : ''))->unique()->implode(', ');

    // Pie chart colors
    $pieColors = ['#2563eb','#7c3aed','#ea580c','#d97706','#0891b2','#16a34a','#dc2626','#9333ea'];
    $totalPie = max(1, $totalDepensesRubriques);
    $pieAngle = 0;
    $pieSlices = [];
    $legendItems = [];
    $ci = 0;
    foreach ($rubriquesMap as $rub) {
        $pct = $rub['depenses'] / $totalPie;
        $pieSlices[] = ['pct' => $pct, 'color' => $pieColors[$ci % count($pieColors)], 'label' => $rub['libelle']];
        $legendItems[] = ['label' => $rub['libelle'], 'color' => $pieColors[$ci % count($pieColors)], 'pct' => round($pct * 100, 1)];
        $ci++;
    }
@endphp

{{-- ─────────────── HEADER ─────────────── --}}
<div class="page-header">
    <div style="display:flex;align-items:center;gap:7px;flex:0 0 auto;padding-right:10px;border-right:1px solid #dde3ef;">
        <div class="logo-box">
            <div class="logo-text">UJK<br>ZERBO</div>
        </div>
        <div>
            <div class="univ-name">UNIVERSITÉ JOSEPH KI-ZERBO</div>
            <div class="univ-tagline">Excellence · Innovation · Engagement</div>
        </div>
    </div>
    <div class="header-title">
        <h1>RAPPORT FINANCIER DU PROJET</h1>
        @if(!empty($convs))
        <div class="conv-line">
            {{ collect($convs)->pluck('titre')->implode(' — ') }}
        </div>
        @endif
    </div>
    <div class="header-meta">
        <div class="meta-row">
            <span style="font-size:6px;">📅</span>
            <span class="meta-label">Période du rapport</span>
        </div>
        <div class="meta-val" style="margin-bottom:4px;">{{ $periodeDébut }} au {{ $periodeFinPrev }}</div>
        <div class="meta-row">
            <span style="font-size:6px;">🗓</span>
            <span class="meta-label">Date d'édition</span>
        </div>
        <div class="meta-val">{{ $dateGeneration }}</div>
    </div>
</div>

{{-- ─────────────── PROJET BAR ─────────────── --}}
<div class="projet-bar">
    <span>👤 <strong>Porteur :</strong> {{ $p['porteur'] }}</span>
    <span>|</span>
    <span><strong>Début :</strong> {{ $periodeDébut }}</span>
    <span>|</span>
    <span><strong>Fin prévue :</strong> {{ $periodeFinPrev }}</span>
    <span>|</span>
    <span><strong>Clôture :</strong> {{ $periodeClôture }}</span>
</div>

{{-- ─────────────── SYNTHÈSE FINANCIÈRE ─────────────── --}}
<div class="section-title">Synthèse Financière</div>
<div class="kpi-row mb">
    <div class="kpi-card blue">
        <div class="kpi-label">Budget Prévu</div>
        <div class="kpi-value blue">{{ number_format($a['budget_prevu'], 0, ',', ' ') }} F</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-label">Versements Reçus</div>
        <div class="kpi-value purple">{{ number_format($a['total_versements'], 0, ',', ' ') }} F</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-label">Dépenses Réalisées</div>
        <div class="kpi-value orange">{{ number_format($a['total_consomme'], 0, ',', ' ') }} F</div>
    </div>
    <div class="kpi-card {{ $a['ecart_budget'] >= 0 ? 'green' : 'red' }}">
        <div class="kpi-label">Écart (Solde)</div>
        <div class="kpi-value {{ $a['ecart_budget'] >= 0 ? 'green' : 'red' }}">
            {{ $a['ecart_budget'] >= 0 ? '+' : '' }}{{ number_format($a['ecart_budget'], 0, ',', ' ') }} F
        </div>
    </div>
    {{-- Gauge --}}
    <div class="taux-box">
        <div class="taux-label">Taux d'Exécution</div>
        <svg class="gauge-svg" width="56" height="32" viewBox="0 0 56 32">
            <path d="M 5 28 A 23 23 0 0 1 51 28" fill="none" stroke="#dbeafe" stroke-width="6" stroke-linecap="round"/>
            <path d="M 5 28 A 23 23 0 0 1 51 28" fill="none"
                  stroke="{{ $taux >= 100 ? '#dc2626' : ($taux >= 80 ? '#f59e0b' : '#2563eb') }}"
                  stroke-width="6" stroke-linecap="round"
                  stroke-dasharray="{{ round($tauxCap / 100 * 72.26, 2) }} 72.26"/>
            <text x="28" y="26" text-anchor="middle" font-size="8" font-weight="bold" fill="{{ $taux >= 100 ? '#dc2626' : '#1e40af' }}">{{ $taux }}%</text>
        </svg>
    </div>
</div>

{{-- ─────────────── CONVENTIONS + CAMEMBERT ─────────────── --}}
<div class="two-col mb">
    <div class="col-left">
        <div class="section-title">Conventions de Financement</div>
        <table>
            <thead>
                <tr>
                    <th>Bailleur</th>
                    <th>Convention</th>
                    <th class="tr">Montant</th>
                    <th class="tr">Versements</th>
                    <th class="tr">Dépenses</th>
                    <th class="tr">Reliquat</th>
                    <th class="tc">Taux</th>
                </tr>
            </thead>
            <tbody>
                @foreach($convs as $c)
                @php $tc = $c['taux_execution'] >= 100 ? 'tb-r' : ($c['taux_execution'] >= 80 ? 'tb-a' : 'tb-g'); @endphp
                <tr>
                    <td><strong>{{ $c['bailleur_sigle'] ?? $c['bailleur'] }}</strong></td>
                    <td>{{ $c['titre'] }}</td>
                    <td class="tr mono">{{ number_format($c['montant_fcfa'], 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format($c['total_versements'], 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format($c['total_consomme'], 0, ',', ' ') }}</td>
                    <td class="tr mono" style="color:{{ $c['solde_engagement'] >= 0 ? '#166534' : '#991b1b' }};">
                        {{ $c['solde_engagement'] >= 0 ? '' : '-' }}{{ number_format(abs($c['solde_engagement']), 0, ',', ' ') }}
                    </td>
                    <td class="tc"><span class="tb {{ $tc }}">{{ $c['taux_execution'] }}%</span></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL</td>
                    <td class="tr mono">{{ number_format(array_sum(array_column($convs, 'montant_fcfa')), 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format(array_sum(array_column($convs, 'total_versements')), 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format($a['total_consomme'], 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format(array_sum(array_column($convs, 'solde_engagement')), 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- PIE CHART --}}
    <div class="col-right">
        <div class="section-title">Répartition des Dépenses par Rubrique</div>
        @if(count($pieSlices) > 0)
        <div style="display:flex;gap:6px;align-items:flex-start;">
            <svg width="90" height="90" viewBox="-1 -1 102 102">
                @php
                    $startAngle = -90;
                    foreach ($pieSlices as $slice) {
                        $sliceDeg = $slice['pct'] * 360;
                        $endAngle = $startAngle + $sliceDeg;
                        $x1 = 50 + 50 * cos(deg2rad($startAngle));
                        $y1 = 50 + 50 * sin(deg2rad($startAngle));
                        $x2 = 50 + 50 * cos(deg2rad($endAngle));
                        $y2 = 50 + 50 * sin(deg2rad($endAngle));
                        $large = $sliceDeg > 180 ? 1 : 0;
                        // Label
                        $midAngle = $startAngle + $sliceDeg / 2;
                        $lx = 50 + 33 * cos(deg2rad($midAngle));
                        $ly = 50 + 33 * sin(deg2rad($midAngle));
                        echo "<path d=\"M50,50 L{$x1},{$y1} A50,50 0 {$large},1 {$x2},{$y2} Z\" fill=\"{$slice['color']}\"/>";
                        if ($slice['pct'] >= 0.05) {
                            $labelPct = round($slice['pct'] * 100, 1);
                            echo "<text x=\"{$lx}\" y=\"{$ly}\" text-anchor=\"middle\" dominant-baseline=\"middle\" font-size=\"6\" fill=\"white\" font-weight=\"bold\">{$labelPct}%</text>";
                        }
                        $startAngle = $endAngle;
                    }
                @endphp
                <circle cx="50" cy="50" r="18" fill="white"/>
            </svg>
            <div style="flex:1;">
                @foreach($legendItems as $li)
                <div style="display:flex;align-items:center;gap:3px;margin-bottom:3px;">
                    <div style="width:7px;height:7px;border-radius:2px;background:{{ $li['color'] }};flex-shrink:0;"></div>
                    <div style="font-size:5.5px;color:#334155;line-height:1.2;">{{ $li['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div style="font-size:6.5px;color:#94a3b8;font-style:italic;padding:6px 0;">Aucune dépense enregistrée.</div>
        @endif
    </div>
</div>

{{-- ─────────────── DÉPENSES PAR RUBRIQUE ─────────────── --}}
<div class="section-title">Dépenses par Rubrique</div>
<table class="mb">
    <thead>
        <tr>
            <th style="width:22px;">N°</th>
            <th>Objet</th>
            <th>Rubrique</th>
            <th style="width:30px;">Conv.</th>
            <th class="tr">Montant Prévu</th>
            <th class="tr">Dépenses Réalisées</th>
            <th class="tc" style="width:65px;">Taux d'Exécution</th>
            <th class="tc">Date de Paiement</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; $totalPrev = 0; $totalDep = 0; @endphp
        @foreach($demandes as $d)
        @php
            $dep = $d['montant'];
            $prev = $dep; // We use depense as prevu when no rubrique budget known
            $t = 100;
            $tc2 = 'tb-g';
            $totalDep += $dep;
            $totalPrev += $prev;
        @endphp
        <tr>
            <td class="tc">{{ $i++ }}</td>
            <td>{{ $d['objet'] }}</td>
            <td>{{ $d['rubrique'] ?? '—' }}</td>
            <td class="tc">{{ $d['convention'] }}</td>
            <td class="tr mono">{{ number_format($prev, 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format($dep, 0, ',', ' ') }}</td>
            <td class="tc">
                <span class="tb {{ $tc2 }}">{{ $t }}%</span>
                <div class="prog-wrap" style="width:40px;"><div class="prog-fill" style="width:{{ min(100,$t) }}%;"></div></div>
            </td>
            <td class="tc">{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '—' }}</td>
        </tr>
        @endforeach
        @foreach($directs as $d)
        @php
            $dep = $d['montant'];
            $totalDep += $dep;
            $totalPrev += $dep;
        @endphp
        <tr>
            <td class="tc">{{ $i++ }}</td>
            <td>{{ $d['objet'] }}</td>
            <td>{{ $d['rubrique'] ?? '—' }}</td>
            <td class="tc">{{ $d['convention'] }}</td>
            <td class="tr mono">{{ number_format($dep, 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format($dep, 0, ',', ' ') }}</td>
            <td class="tc">
                <span class="tb tb-g">100%</span>
                <div class="prog-wrap" style="width:40px;"><div class="prog-fill" style="width:100%;"></div></div>
            </td>
            <td class="tc">{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="tc">—</td>
            <td>TOTAL</td>
            <td>—</td>
            <td>—</td>
            <td class="tr mono">{{ number_format($totalPrev, 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format($totalDep, 0, ',', ' ') }}</td>
            <td class="tc">
                @php $tauxTotal = $totalPrev > 0 ? round($totalDep / $totalPrev * 100) : 0; @endphp
                <span class="tb {{ $tauxTotal >= 100 ? 'tb-r' : ($tauxTotal >= 80 ? 'tb-a' : 'tb-g') }}">{{ $tauxTotal }}%</span>
            </td>
            <td>—</td>
        </tr>
    </tfoot>
</table>

{{-- ─────────────── OBSERVATIONS + BAILLEUR ─────────────── --}}
<div class="bottom-row">
    <div class="obs-box">
        <div class="obs-title">📋 Observations</div>
        <div class="obs-text">
            @foreach($observations as $obs)
            {{ $obs }}<br>
            @endforeach
            @if(empty($observations))
            Aucune observation particulière.
            @endif
        </div>
    </div>
    <div class="bail-box">
        <div class="bail-title">🌐 Bailleur</div>
        <div class="bail-name">{{ $bailleurs ?: '—' }}</div>
    </div>
</div>

<div class="footer">
    Document généré automatiquement par le système CIFEU — DSI Université Joseph KI-ZERBO &nbsp;|&nbsp; Page {PAGE_NUM} / {PAGE_COUNT}
</div>
</body>
</html>
