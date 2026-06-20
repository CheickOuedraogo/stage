<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 8mm 10mm 12mm; size: A4 landscape; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7.5px; color: #1e293b; line-height: 1.35; background: #fff; }

    /* ── HEADER BANNER ── */
    .header-banner {
        background: #1e3a8a;
        border-radius: 5px;
        padding: 7px 10px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .logo-circle {
        width: 34px; height: 34px;
        border: 2px solid rgba(255,255,255,0.4);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        background: rgba(255,255,255,0.1);
    }
    .logo-circle span { font-size: 5.5px; font-weight: bold; color: #fff; text-align: center; line-height: 1.1; }
    .header-org { flex: 0 0 auto; padding-right: 10px; border-right: 1px solid rgba(255,255,255,0.25); }
    .org-name { font-size: 7.5px; font-weight: bold; color: #fff; }
    .org-tag  { font-size: 5.5px; color: rgba(255,255,255,0.65); margin-top: 1px; }
    .header-center { flex: 1; text-align: center; }
    .header-center h1 { font-size: 12px; font-weight: bold; color: #fff; letter-spacing: 0.8px; text-transform: uppercase; }
    .header-center .sub { font-size: 6.5px; color: rgba(255,255,255,0.75); margin-top: 2px; }
    .header-meta {
        flex: 0 0 auto;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 4px;
        padding: 5px 8px;
        font-size: 6px;
        color: rgba(255,255,255,0.85);
        min-width: 95px;
    }
    .header-meta .ml { color: rgba(255,255,255,0.55); margin-bottom: 1px; }
    .header-meta .mv { font-weight: bold; color: #fff; margin-bottom: 4px; }

    /* ── INFO BAR ── */
    .info-bar {
        background: #f8fafc;
        border: 0.8px solid #e2e8f0;
        border-left: 3px solid #1e3a8a;
        border-radius: 3px;
        padding: 4px 8px;
        margin-bottom: 6px;
        display: flex;
        gap: 18px;
        font-size: 7px;
        color: #475569;
    }
    .info-bar strong { color: #0f172a; }
    .info-bar .sep { color: #cbd5e1; }

    /* ── SECTION TITLE ── */
    .sec { font-size: 7px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px;
           border-bottom: 1.5px solid #1e3a8a; padding-bottom: 2px; margin-bottom: 5px; }

    /* ── KPI CARDS ── */
    .kpi-row { display: flex; gap: 5px; margin-bottom: 6px; }
    .kpi { flex: 1; border-radius: 5px; padding: 6px 8px; }
    .kpi.blue   { background: #eff6ff; border: 0.8px solid #93c5fd; }
    .kpi.violet { background: #f5f3ff; border: 0.8px solid #c4b5fd; }
    .kpi.amber  { background: #fffbeb; border: 0.8px solid #fcd34d; }
    .kpi.green  { background: #f0fdf4; border: 0.8px solid #86efac; }
    .kpi.red    { background: #fff1f2; border: 0.8px solid #fca5a5; }
    .kpi-lbl { font-size: 5.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .kpi-val { font-size: 10px; font-weight: bold; }
    .kpi-val.blue   { color: #1d4ed8; }
    .kpi-val.violet { color: #6d28d9; }
    .kpi-val.amber  { color: #b45309; }
    .kpi-val.green  { color: #15803d; }
    .kpi-val.red    { color: #b91c1c; }

    /* Gauge */
    .gauge-box {
        flex: 0 0 72px;
        background: #eff6ff;
        border: 0.8px solid #93c5fd;
        border-radius: 5px;
        padding: 4px;
        text-align: center;
    }
    .gauge-lbl { font-size: 5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }

    /* ── TWO-COL ── */
    .two-col { display: flex; gap: 8px; margin-bottom: 6px; }
    .col-l { flex: 1; min-width: 0; }
    .col-r { flex: 0 0 36%; }

    /* ── TABLES ── */
    table { width: 100%; border-collapse: collapse; font-size: 7px; }
    thead tr { background: #1e3a8a; color: #fff; }
    thead th { padding: 3.5px 5px; text-align: left; font-size: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.2px; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr:hover { background: #eff6ff; }
    tbody td { padding: 3px 5px; border-bottom: 0.5px solid #f1f5f9; vertical-align: middle; }
    tfoot tr { background: #1e3a8a; }
    tfoot td { padding: 3.5px 5px; font-weight: bold; font-size: 7px; color: #fff; }
    .tr { text-align: right; }
    .tc { text-align: center; }
    .mono { font-family: DejaVu Sans Mono, monospace; }

    /* Badge statut */
    .badge { display: inline-block; padding: 1px 5px; border-radius: 6px; font-size: 6px; font-weight: 700; }
    .badge-g { background: #dcfce7; color: #166534; }
    .badge-a { background: #fef3c7; color: #92400e; }
    .badge-r { background: #fee2e2; color: #991b1b; }

    /* Progress bar */
    .prog { background: #e2e8f0; border-radius: 3px; height: 4px; width: 45px; display: inline-block; vertical-align: middle; overflow: hidden; margin-left: 3px; }
    .prog-fill { height: 100%; border-radius: 3px; }
    .prog-fill.g { background: #22c55e; }
    .prog-fill.a { background: #f59e0b; }
    .prog-fill.r { background: #ef4444; }

    /* ── BOTTOM BOXES ── */
    .bottom { display: flex; gap: 8px; margin-top: 5px; }
    .obs-box { flex: 1; border: 0.8px solid #e2e8f0; border-left: 3px solid #1e3a8a; border-radius: 3px; padding: 5px 7px; }
    .obs-box .obs-t { font-size: 6.5px; font-weight: bold; color: #1e3a8a; margin-bottom: 3px; }
    .obs-box .obs-c { font-size: 6.5px; color: #475569; line-height: 1.5; }
    .bail-box { flex: 0 0 30%; border: 0.8px solid #e2e8f0; border-left: 3px solid #7c3aed; border-radius: 3px; padding: 5px 7px; }
    .bail-box .bail-t { font-size: 6.5px; font-weight: bold; color: #7c3aed; margin-bottom: 3px; }
    .bail-box .bail-c { font-size: 7px; color: #334155; line-height: 1.4; }

    /* ── FOOTER ── */
    .footer {
        position: fixed; bottom: 0; left: 10mm; right: 10mm;
        border-top: 0.5px solid #e2e8f0;
        padding-top: 2px;
        font-size: 5.5px;
        color: #94a3b8;
        display: flex;
        justify-content: space-between;
    }

    .mb4 { margin-bottom: 4px; }
    .mb6 { margin-bottom: 6px; }
</style>
</head>
<body>

@php
    $p       = $bilan['projet'];
    $a       = $bilan['analyse_ecarts'];
    $convs   = $bilan['conventions'];
    $demandes = $bilan['demandes'];
    $directs  = $bilan['paiements_directs'];

    $taux    = $a['taux_execution'];
    $tauxCap = min(100, $taux);

    $dateGen        = \Carbon\Carbon::now()->format('d/m/Y à H\hi');
    $periodeDébut   = $p['date_debut']      ? \Carbon\Carbon::parse($p['date_debut'])->format('d/m/Y')      : '—';
    $periodeFinPrev = $p['date_fin_prevue'] ? \Carbon\Carbon::parse($p['date_fin_prevue'])->format('d/m/Y') : '—';
    $periodeClôture = $p['date_fin_reelle'] ? \Carbon\Carbon::parse($p['date_fin_reelle'])->format('d/m/Y') : 'En cours';

    // Bailleurs
    $bailleurs = collect($convs)
        ->map(fn($c) => ($c['bailleur_sigle'] ?? $c['bailleur']) . ($c['bailleur_sigle'] ? " — {$c['bailleur']}" : ''))
        ->unique()->implode("\n");

    // Observations automatiques
    $observations = [];
    if ($taux >= 95)  $observations[] = "L'ensemble des dépenses prévues a été exécuté à {$taux}%.";
    if ($a['ecart_budget'] >= 0) $observations[] = "Un reliquat de " . number_format($a['ecart_budget'], 0, ',', ' ') . " F CFA reste disponible.";
    else $observations[] = "Dépassement budgétaire de " . number_format(abs($a['ecart_budget']), 0, ',', ' ') . " F CFA constaté.";
    if (($a['ecart_temps_jours'] ?? 0) > 0) $observations[] = "Retard constaté : {$a['ecart_temps_label']}.";
    elseif (($a['ecart_temps_jours'] ?? 0) < 0) $observations[] = "Projet clôturé en avance : {$a['ecart_temps_label']}.";

    // Couleur gauge
    $gaugeColor = $taux >= 100 ? '#dc2626' : ($taux >= 80 ? '#f59e0b' : '#2563eb');

    // Pie chart
    $pieColors = ['#2563eb','#7c3aed','#ea580c','#d97706','#0891b2','#16a34a','#dc2626','#9333ea'];
    $totalDep = 0;
    foreach ($demandes as $d) $totalDep += $d['montant'];
    foreach ($directs  as $d) $totalDep += $d['montant'];

    // Agrégation rubriques pour pie
    $rubMap = [];
    foreach ($demandes as $d) {
        $k = $d['rubrique'] ?? 'Autres';
        $rubMap[$k] = ($rubMap[$k] ?? 0) + $d['montant'];
    }
    foreach ($directs as $d) {
        $k = $d['rubrique'] ?? 'Autres';
        $rubMap[$k] = ($rubMap[$k] ?? 0) + $d['montant'];
    }
    $pieSlices = [];
    $ci = 0;
    foreach ($rubMap as $lib => $val) {
        $pieSlices[] = ['label' => $lib, 'val' => $val, 'pct' => $totalDep > 0 ? $val / $totalDep : 0, 'color' => $pieColors[$ci % count($pieColors)]];
        $ci++;
    }
@endphp

{{-- ═══════════════ HEADER ═══════════════ --}}
<div class="header-banner">
    <div class="logo-circle"><span>UJK<br>ZERBO</span></div>
    <div class="header-org">
        <div class="org-name">UNIVERSITÉ JOSEPH KI-ZERBO</div>
        <div class="org-tag">Excellence · Innovation · Engagement</div>
    </div>
    <div class="header-center">
        <h1>Rapport Financier de Projet</h1>
        <div class="sub">{{ $p['titre'] }}</div>
    </div>
    <div class="header-meta">
        <div class="ml">Période couverte</div>
        <div class="mv">{{ $periodeDébut }} – {{ $periodeFinPrev }}</div>
        <div class="ml">Date de clôture</div>
        <div class="mv">{{ $periodeClôture }}</div>
        <div class="ml">Édité le</div>
        <div class="mv">{{ $dateGen }}</div>
    </div>
</div>

{{-- ═══════════════ INFO BAR ═══════════════ --}}
<div class="info-bar">
    <span><strong>Porteur :</strong> {{ $p['porteur'] }}</span>
    <span class="sep">|</span>
    <span><strong>Début :</strong> {{ $periodeDébut }}</span>
    <span class="sep">|</span>
    <span><strong>Fin prévue :</strong> {{ $periodeFinPrev }}</span>
    <span class="sep">|</span>
    <span><strong>Clôture réelle :</strong> {{ $periodeClôture }}</span>
    @if($a['ecart_temps_label'])
    <span class="sep">|</span>
    <span><strong>Délais :</strong> {{ $a['ecart_temps_label'] }}</span>
    @endif
</div>

{{-- ═══════════════ KPI ═══════════════ --}}
<div class="sec">Synthèse Financière</div>
<div class="kpi-row mb6">
    <div class="kpi blue">
        <div class="kpi-lbl">Budget Prévu</div>
        <div class="kpi-val blue">{{ number_format($a['budget_prevu'], 0, ',', ' ') }} F</div>
    </div>
    <div class="kpi violet">
        <div class="kpi-lbl">Versements Reçus</div>
        <div class="kpi-val violet">{{ number_format($a['total_versements'], 0, ',', ' ') }} F</div>
    </div>
    <div class="kpi amber">
        <div class="kpi-lbl">Dépenses Réalisées</div>
        <div class="kpi-val amber">{{ number_format($a['total_consomme'], 0, ',', ' ') }} F</div>
    </div>
    <div class="kpi {{ $a['solde_caisse_global'] >= 0 ? 'green' : 'red' }}">
        <div class="kpi-lbl">Solde Caisse</div>
        <div class="kpi-val {{ $a['solde_caisse_global'] >= 0 ? 'green' : 'red' }}">
            {{ $a['solde_caisse_global'] >= 0 ? '+' : '' }}{{ number_format($a['solde_caisse_global'], 0, ',', ' ') }} F
        </div>
    </div>
    <div class="kpi {{ $a['ecart_budget'] >= 0 ? 'green' : 'red' }}">
        <div class="kpi-lbl">Écart Budgétaire</div>
        <div class="kpi-val {{ $a['ecart_budget'] >= 0 ? 'green' : 'red' }}">
            {{ $a['ecart_budget'] >= 0 ? '+' : '' }}{{ number_format($a['ecart_budget'], 0, ',', ' ') }} F
        </div>
    </div>
    {{-- Gauge taux d'exécution --}}
    <div class="gauge-box">
        <div class="gauge-lbl">Taux d'Exécution</div>
        <svg width="60" height="36" viewBox="0 0 60 36" style="display:block;margin:0 auto 1px;">
            <path d="M6 30 A24 24 0 0 1 54 30" fill="none" stroke="#dbeafe" stroke-width="7" stroke-linecap="round"/>
            <path d="M6 30 A24 24 0 0 1 54 30" fill="none"
                  stroke="{{ $gaugeColor }}" stroke-width="7" stroke-linecap="round"
                  stroke-dasharray="{{ round($tauxCap / 100 * 75.4, 2) }} 75.4"/>
            <text x="30" y="29" text-anchor="middle" font-size="9" font-weight="bold" fill="{{ $gaugeColor }}">{{ $taux }}%</text>
        </svg>
        <div style="font-size:5px;color:#64748b;">
            {{ $taux >= 100 ? 'Dépassement' : ($taux >= 80 ? 'Bon avancement' : 'En cours') }}
        </div>
    </div>
</div>

{{-- ═══════════════ CONVENTIONS + PIE ═══════════════ --}}
<div class="two-col">
    <div class="col-l">
        <div class="sec">Conventions de Financement</div>
        <table>
            <thead>
                <tr>
                    <th>Bailleur</th>
                    <th>Convention</th>
                    <th class="tr">Montant (F)</th>
                    <th class="tr">Versé (F)</th>
                    <th class="tr">Dépensé (F)</th>
                    <th class="tr">Solde (F)</th>
                    <th class="tc">Taux</th>
                </tr>
            </thead>
            <tbody>
                @foreach($convs as $c)
                @php
                    $bc = $c['taux_execution'] >= 100 ? 'badge-r' : ($c['taux_execution'] >= 80 ? 'badge-a' : 'badge-g');
                    $pc = $c['taux_execution'] >= 100 ? 'r' : ($c['taux_execution'] >= 80 ? 'a' : 'g');
                @endphp
                <tr>
                    <td><strong>{{ $c['bailleur_sigle'] ?? $c['bailleur'] }}</strong></td>
                    <td style="max-width:90px;overflow:hidden;">{{ $c['titre'] }}</td>
                    <td class="tr mono">{{ number_format($c['montant_fcfa'], 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format($c['total_versements'], 0, ',', ' ') }}</td>
                    <td class="tr mono">{{ number_format($c['total_consomme'], 0, ',', ' ') }}</td>
                    <td class="tr mono" style="color:{{ $c['solde_engagement'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        {{ $c['solde_engagement'] >= 0 ? '+' : '' }}{{ number_format($c['solde_engagement'], 0, ',', ' ') }}
                    </td>
                    <td class="tc">
                        <span class="badge {{ $bc }}">{{ $c['taux_execution'] }}%</span>
                        <div class="prog"><div class="prog-fill {{ $pc }}" style="width:{{ min(100,$c['taux_execution']) }}%;"></div></div>
                    </td>
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

    <div class="col-r">
        <div class="sec">Répartition par Rubrique</div>
        @if(count($pieSlices) > 0)
        <div style="display:flex;gap:6px;align-items:flex-start;">
            <svg width="90" height="90" viewBox="-1 -1 102 102">
                @php
                    $startAngle = -90;
                    foreach ($pieSlices as $slice) {
                        $deg = $slice['pct'] * 360;
                        $endAngle = $startAngle + $deg;
                        $x1 = 50 + 50 * cos(deg2rad($startAngle));
                        $y1 = 50 + 50 * sin(deg2rad($startAngle));
                        $x2 = 50 + 50 * cos(deg2rad($endAngle));
                        $y2 = 50 + 50 * sin(deg2rad($endAngle));
                        $large = $deg > 180 ? 1 : 0;
                        $mx = 50 + 34 * cos(deg2rad($startAngle + $deg / 2));
                        $my = 50 + 34 * sin(deg2rad($startAngle + $deg / 2));
                        echo "<path d=\"M50,50 L{$x1},{$y1} A50,50 0 {$large},1 {$x2},{$y2} Z\" fill=\"{$slice['color']}\"/>";
                        if ($slice['pct'] >= 0.06) {
                            $lbl = round($slice['pct']*100,1).'%';
                            echo "<text x=\"{$mx}\" y=\"{$my}\" text-anchor=\"middle\" dominant-baseline=\"middle\" font-size=\"6\" fill=\"white\" font-weight=\"bold\">{$lbl}</text>";
                        }
                        $startAngle = $endAngle;
                    }
                @endphp
                <circle cx="50" cy="50" r="19" fill="white"/>
            </svg>
            <div style="flex:1;padding-top:3px;">
                @foreach($pieSlices as $sl)
                <div style="display:flex;align-items:center;gap:3px;margin-bottom:3px;">
                    <div style="width:7px;height:7px;border-radius:2px;background:{{ $sl['color'] }};flex-shrink:0;"></div>
                    <div style="font-size:5.5px;color:#334155;line-height:1.2;">{{ $sl['label'] }}</div>
                    <div style="font-size:5.5px;color:#64748b;margin-left:auto;">{{ round($sl['pct']*100,1) }}%</div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div style="font-size:6.5px;color:#94a3b8;font-style:italic;padding:8px 0;">Aucune dépense enregistrée.</div>
        @endif
    </div>
</div>

{{-- ═══════════════ DÉPENSES DÉTAILLÉES ═══════════════ --}}
<div class="sec">Détail des Dépenses</div>
<table class="mb4">
    <thead>
        <tr>
            <th style="width:20px;" class="tc">N°</th>
            <th>Objet / Description</th>
            <th>Rubrique</th>
            <th style="width:28px;" class="tc">Conv.</th>
            <th class="tr">Montant (F)</th>
            <th class="tc">Date paiement</th>
            <th class="tc">Type</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; $tot = 0; @endphp
        @foreach($demandes as $d)
        @php $tot += $d['montant']; @endphp
        <tr>
            <td class="tc" style="color:#64748b;">{{ $i++ }}</td>
            <td>{{ $d['objet'] }}</td>
            <td>{{ $d['rubrique'] ?? '—' }}</td>
            <td class="tc" style="font-size:6px;color:#475569;">{{ $d['convention'] }}</td>
            <td class="tr mono">{{ number_format($d['montant'], 0, ',', ' ') }}</td>
            <td class="tc">{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '—' }}</td>
            <td class="tc"><span class="badge badge-g">Demande</span></td>
        </tr>
        @endforeach
        @foreach($directs as $d)
        @php $tot += $d['montant']; @endphp
        <tr>
            <td class="tc" style="color:#64748b;">{{ $i++ }}</td>
            <td>{{ $d['objet'] }}</td>
            <td>{{ $d['rubrique'] ?? '—' }}</td>
            <td class="tc" style="font-size:6px;color:#475569;">{{ $d['convention'] }}</td>
            <td class="tr mono">{{ number_format($d['montant'], 0, ',', ' ') }}</td>
            <td class="tc">{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '—' }}</td>
            <td class="tc"><span class="badge badge-a">Direct</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="tc">—</td>
            <td colspan="3"><strong>TOTAL GÉNÉRAL</strong></td>
            <td class="tr mono">{{ number_format($tot, 0, ',', ' ') }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

{{-- ═══════════════ OBSERVATIONS + BAILLEUR ═══════════════ --}}
<div class="bottom">
    <div class="obs-box">
        <div class="obs-t">Observations</div>
        <div class="obs-c">
            @forelse($observations as $obs)
            • {{ $obs }}<br>
            @empty
            Aucune observation particulière.
            @endforelse
        </div>
    </div>
    <div class="bail-box">
        <div class="bail-t">Bailleur(s) de fonds</div>
        <div class="bail-c" style="white-space:pre-line;">{{ $bailleurs ?: '—' }}</div>
    </div>
</div>

<div class="footer">
    <span>Document généré par CIFEU — DSI Université Joseph KI-ZERBO</span>
    <span>{{ $dateGen }}</span>
</div>

</body>
</html>
