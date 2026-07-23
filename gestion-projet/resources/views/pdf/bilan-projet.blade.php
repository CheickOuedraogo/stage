<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 8mm 10mm 12mm; size: A4 landscape; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1e293b; line-height: 1.4; background: #fff; }
    * { margin: 0; padding: 0; }

    table { border-collapse: collapse; }
    .w100 { width: 100%; }

    h2 { font-size: 16px; color: #111827; }
    h3 { font-size: 11px; color: #111827; font-weight: bold; }
    .subtitle { font-size: 10px; color: #6b7280; }
    .text-muted { color: #6b7280; }
    .text-bold { font-weight: bold; }
    .mono { font-family: DejaVu Sans Mono, monospace; }
    .tr { text-align: right; }
    .tc { text-align: center; }

    .card-outer { border: 1px solid #e5e7eb; margin-bottom: 10px; }
    .card-head { background: #f9fafb; padding: 7px 12px; border-bottom: 1px solid #e5e7eb; }
    .card-head h3 { margin: 0; }

    th { padding: 7px 12px; text-align: left; font-size: 8px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
    td { padding: 6px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .tfoot td { background: #f9fafb; border-top: 2px solid #e5e7eb; padding: 7px 12px; font-weight: bold; }
</style>
</head>
<body>

@php
    $p        = $bilan['projet'];
    $a        = $bilan['analyse_ecarts'];
    $convs    = $bilan['conventions'];
    $demandes = $bilan['demandes'];
    $directs  = $bilan['paiements_directs'];

    $taux    = $a['taux_execution'];
    $tauxCap = min(100, $taux);
    $dateGen = \Carbon\Carbon::now()->format('d/m/Y \a H\hi');

    $isSuccess  = ($p['statut_final'] ?? '') === 'succes';
    $badgeBg    = $isSuccess ? '#ecfdf5' : '#fef2f2';
    $badgeFg    = $isSuccess ? '#065f46' : '#991b1b';
    $dotColor   = $isSuccess ? '#10b981' : '#ef4444';
    $badgeLabel = $p['statut_final_label'] ?? ($p['libelle_statut'] ?? 'En cours');

    $barColor = $taux >= 100 ? '#ef4444' : ($taux >= 90 ? '#f59e0b' : ($taux >= 70 ? '#3b82f6' : '#10b981'));
    $ecartPositif = $a['ecart_budget'] >= 0;
@endphp

{{-- ═══════════════ EN-TETE ═══════════════ --}}
<table class="w100" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
<tr>
    <td style="padding:0;">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:0; vertical-align:middle;">
                <h2 style="margin:0;">Bilan de cloture</h2>
                <p class="subtitle" style="margin-top:2px;">{{ $p['titre'] }}</p>
            </td>
            <td style="padding:0; vertical-align:middle; text-align:right;">
                <span style="background:{{ $badgeBg }}; color:{{ $badgeFg }}; padding:3px 10px; border-radius:10px; font-size:9px; font-weight:600;">
                    <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:{{ $dotColor }}; margin-right:4px; vertical-align:middle;"></span>
                    {{ $badgeLabel }}
                </span>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{-- ═══════════════ INFOS PROJET ═══════════════ --}}
<table class="w100 card-outer" cellpadding="0" cellspacing="0">
<tr>
    <td class="card-head">
        <h3 style="margin:0;">Informations du projet</h3>
    </td>
</tr>
<tr>
    <td style="padding:10px 12px;">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:25%; padding:0 8px 0 0; vertical-align:top; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280;">Porteur</span>
                <span style="display:block; font-size:10px; font-weight:bold; color:#111827;">{{ $p['porteur'] }}</span>
            </td>
            <td style="width:25%; padding:0 8px; vertical-align:top; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280;">Date de debut</span>
                <span style="display:block; font-size:10px; font-weight:bold; color:#111827;">{{ $p['date_debut'] ? \Carbon\Carbon::parse($p['date_debut'])->format('d/m/Y') : '---' }}</span>
            </td>
            <td style="width:25%; padding:0 8px; vertical-align:top; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280;">Date de fin prevue</span>
                <span style="display:block; font-size:10px; font-weight:bold; color:#111827;">{{ $p['date_fin_prevue'] ? \Carbon\Carbon::parse($p['date_fin_prevue'])->format('d/m/Y') : '---' }}</span>
            </td>
            <td style="width:25%; padding:0 0 0 8px; vertical-align:top; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280;">Date de cloture</span>
                <span style="display:block; font-size:10px; font-weight:bold; color:#111827;">{{ $p['date_fin_reelle'] ? \Carbon\Carbon::parse($p['date_fin_reelle'])->format('d/m/Y') : '---' }}</span>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{-- ═══════════════ ALERTE ═══════════════ --}}
@if($a['conventions_depassent_budget_initial'] ?? false)
<table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #fde68a; background:#fffbeb; margin-bottom:10px;">
<tr>
    <td style="padding:8px 12px; vertical-align:top; width:20px; color:#d97706; font-weight:bold; font-size:14px; border:none;">!</td>
    <td style="padding:8px 12px 8px 0; border:none;">
        <span style="display:block; font-size:11px; font-weight:bold; color:#92400e;">Financement superieur au budget initial</span>
        <span style="display:block; font-size:9px; color:#b45309; margin-top:3px;">
            Le total des conventions ({{ number_format($a['budget_prevu'], 0, ',', ' ') }} F)
            depasse le budget initial estime ({{ number_format($a['budget_initial'], 0, ',', ' ') }} F).
            Ecart : +{{ number_format($a['budget_prevu'] - $a['budget_initial'], 0, ',', ' ') }} F.
        </span>
    </td>
</tr>
</table>
@endif

{{-- ═══════════════ KPI CARDS ═══════════════ --}}
<table class="w100" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
    <td style="width:25%; padding:0 4px 0 0; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:8px 10px; background:#eff6ff; width:24px; vertical-align:top; border:none;">
                <span style="display:block; width:14px; height:14px; background:#3b82f6; color:#fff; text-align:center; line-height:14px; font-size:9px; font-weight:bold; border-radius:3px;">$</span>
            </td>
            <td style="padding:8px 10px; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280; margin-bottom:2px;">Budget prevu</span>
                <span style="display:block; font-size:12px; font-weight:bold; color:#111827; font-family:DejaVu Sans Mono, monospace;">{{ number_format($a['budget_prevu'], 0, ',', ' ') }} F</span>
            </td>
        </tr>
        </table>
    </td>
    <td style="width:25%; padding:0 4px; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:8px 10px; background:#f5f3ff; width:24px; vertical-align:top; border:none;">
                <span style="display:block; width:14px; height:14px; background:#7c3aed; color:#fff; text-align:center; line-height:14px; font-size:9px; font-weight:bold; border-radius:3px;">E</span>
            </td>
            <td style="padding:8px 10px; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280; margin-bottom:2px;">Versements recus</span>
                <span style="display:block; font-size:12px; font-weight:bold; color:#111827; font-family:DejaVu Sans Mono, monospace;">{{ number_format($a['total_versements'], 0, ',', ' ') }} F</span>
            </td>
        </tr>
        </table>
    </td>
    <td style="width:25%; padding:0 4px; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:8px 10px; background:#f9fafb; width:24px; vertical-align:top; border:none;">
                <span style="display:block; width:14px; height:14px; background:#6b7280; color:#fff; text-align:center; line-height:14px; font-size:9px; font-weight:bold; border-radius:3px;">S</span>
            </td>
            <td style="padding:8px 10px; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280; margin-bottom:2px;">Total consomme</span>
                <span style="display:block; font-size:12px; font-weight:bold; color:#111827; font-family:DejaVu Sans Mono, monospace;">{{ number_format($a['total_consomme'], 0, ',', ' ') }} F</span>
                <span style="display:block; font-size:8px; color:#6b7280; margin-top:1px;">{{ $taux }}% du budget</span>
            </td>
        </tr>
        </table>
    </td>
    <td style="width:25%; padding:0 0 0 4px; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:8px 10px; background:{{ $ecartPositif ? '#f0fdf4' : '#fef2f2' }}; width:24px; vertical-align:top; border:none;">
                <span style="display:block; width:14px; height:14px; background:{{ $ecartPositif ? '#16a34a' : '#dc2626' }}; color:#fff; text-align:center; line-height:14px; font-size:9px; font-weight:bold; border-radius:3px;">{{ $ecartPositif ? '-' : '+' }}</span>
            </td>
            <td style="padding:8px 10px; border:none;">
                <span style="display:block; font-size:8px; color:#6b7280; margin-bottom:2px;">Ecart budgetaire</span>
                <span style="display:block; font-size:12px; font-weight:bold; color:{{ $ecartPositif ? '#16a34a' : '#dc2626' }}; font-family:DejaVu Sans Mono, monospace;">{{ $ecartPositif ? '+' : '' }}{{ number_format($a['ecart_budget'], 0, ',', ' ') }} F</span>
                <span style="display:block; font-size:8px; color:#6b7280; margin-top:1px;">{{ $ecartPositif ? 'Sous-consommation' : 'Depassement' }}</span>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{-- ═══════════════ BARRE D'EXECUTION ═══════════════ --}}
<table class="w100 card-outer" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
    <td style="padding:8px 12px; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:0 0 6px 0; border:none; font-size:10px; font-weight:bold; color:#374151;">Taux d'execution global</td>
            <td style="padding:0 0 6px 0; border:none; text-align:right; font-size:12px; font-weight:bold; color:#111827;">{{ $taux }}%</td>
        </tr>
        </table>
        <table class="w100" cellpadding="0" cellspacing="0" style="background:#f3f4f6; border-radius:4px;">
        <tr>
            <td style="padding:0; background:{{ $barColor }}; width:{{ $tauxCap }}%; height:8px; border:none; border-radius:4px;"></td>
        </tr>
        </table>
        <table class="w100" cellpadding="0" cellspacing="0" style="margin-top:3px;">
        <tr>
            <td style="padding:0; border:none; font-size:8px; color:#9ca3af;">0%</td>
            <td style="padding:0; border:none; font-size:8px; color:#9ca3af; text-align:center;">50%</td>
            <td style="padding:0; border:none; font-size:8px; color:#9ca3af; text-align:right;">100%</td>
        </tr>
        </table>
    </td>
</tr>
</table>

{{-- ═══════════════ DELAIS ═══════════════ --}}
@if($a['ecart_temps_label'] ?? null)
<table class="w100 card-outer" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
<tr>
    <td style="padding:8px 12px; border:none; font-size:10px;">
        <span style="color:{{ ($a['ecart_temps_jours'] ?? 0) > 0 ? '#ef4444' : '#10b981' }}; font-weight:bold;">Delais :</span>
        <span style="color:{{ ($a['ecart_temps_jours'] ?? 0) > 0 ? '#dc2626' : '#16a34a' }}; font-weight:600;">
            {{ $a['ecart_temps_label'] }}
        </span>
    </td>
</tr>
</table>
@endif

{{-- ═══════════════ CONVENTIONS ═══════════════ --}}
@if(count($convs) > 0)
<table class="w100 card-outer" cellpadding="0" cellspacing="0">
<tr>
    <td class="card-head"><h3 style="margin:0;">Conventions de financement</h3></td>
</tr>
<tr>
    <td style="padding:0; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <th>Bailleur</th>
            <th>Convention</th>
            <th class="tr">Montant prevu</th>
            <th class="tr">Versements</th>
            <th class="tr">Depenses</th>
            <th class="tr">Reliquat</th>
            <th class="tr">Taux</th>
        </tr>
        @foreach($convs as $c)
        @php
            $bc = $c['taux_execution'] >= 100 ? '#fef2f2,#991b1b' : ($c['taux_execution'] >= 90 ? '#fffbeb,#92400e' : '#ecfdf5,#065f46');
            [$bcBg, $bcFg] = explode(',', $bc);
        @endphp
        <tr>
            <td style="font-weight:bold;">{{ $c['bailleur_sigle'] ?? $c['bailleur'] }}</td>
            <td class="text-muted">{{ $c['titre'] }}</td>
            <td class="tr mono">{{ number_format($c['montant_fcfa'], 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format($c['total_versements'], 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format($c['total_consomme'], 0, ',', ' ') }}</td>
            <td class="tr mono" style="font-weight:bold; color:{{ $c['solde_engagement'] >= 0 ? '#16a34a' : '#dc2626' }};">
                {{ $c['solde_engagement'] >= 0 ? '+' : '' }}{{ number_format($c['solde_engagement'], 0, ',', ' ') }}
            </td>
            <td class="tr">
                <span style="background:{{ $bcBg }}; color:{{ $bcFg }}; padding:2px 8px; border-radius:10px; font-size:8px; font-weight:600;">{{ $c['taux_execution'] }}%</span>
            </td>
        </tr>
        @endforeach
        <tr class="tfoot">
            <td colspan="2">Total</td>
            <td class="tr mono">{{ number_format(array_sum(array_column($convs, 'montant_fcfa')), 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format(array_sum(array_column($convs, 'total_versements')), 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format($a['total_consomme'], 0, ',', ' ') }}</td>
            <td class="tr mono">{{ number_format(array_sum(array_column($convs, 'solde_engagement')), 0, ',', ' ') }}</td>
            <td></td>
        </tr>
        </table>
    </td>
</tr>
</table>
@endif

{{-- ═══════════════ DEMANDES ═══════════════ --}}
<table class="w100 card-outer" cellpadding="0" cellspacing="0">
<tr>
    <td class="card-head">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:0; border:none;"><h3 style="margin:0;">Demandes de depense terminees</h3></td>
            <td style="padding:0; border:none; text-align:right; font-size:9px; color:#6b7280;">{{ count($demandes) }}</td>
        </tr>
        </table>
    </td>
</tr>
<tr>
    <td style="padding:0; border:none;">
        @if(count($demandes) > 0)
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <th>Objet</th>
            <th>Rubrique</th>
            <th>Convention</th>
            <th class="tr">Montant</th>
            <th>Paiement</th>
        </tr>
        @foreach($demandes as $d)
        <tr>
            <td>{{ $d['objet'] }}</td>
            <td class="text-muted">{{ $d['rubrique'] ?? '---' }}</td>
            <td class="text-muted">{{ $d['convention'] }}</td>
            <td class="tr mono">{{ number_format($d['montant'], 0, ',', ' ') }}</td>
            <td class="text-muted">{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '---' }}</td>
        </tr>
        @endforeach
        <tr class="tfoot">
            <td colspan="3">Total demandes</td>
            <td class="tr mono">{{ number_format(array_sum(array_column($demandes, 'montant')), 0, ',', ' ') }}</td>
            <td></td>
        </tr>
        </table>
        @else
        <p style="padding:16px; text-align:center; color:#9ca3af; font-style:italic;">Aucune demande de depense terminee.</p>
        @endif
    </td>
</tr>
</table>

{{-- ═══════════════ PAIEMENTS DIRECTS ═══════════════ --}}
<table class="w100 card-outer" cellpadding="0" cellspacing="0">
<tr>
    <td class="card-head">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:0; border:none;"><h3 style="margin:0;">Paiements directs</h3></td>
            <td style="padding:0; border:none; text-align:right; font-size:9px; color:#6b7280;">{{ count($directs) }}</td>
        </tr>
        </table>
    </td>
</tr>
<tr>
    <td style="padding:0; border:none;">
        @if(count($directs) > 0)
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <th>Objet</th>
            <th>Rubrique</th>
            <th>Convention</th>
            <th class="tr">Montant</th>
            <th>Date</th>
        </tr>
        @foreach($directs as $d)
        <tr>
            <td>{{ $d['objet'] }}</td>
            <td class="text-muted">{{ $d['rubrique'] ?? '---' }}</td>
            <td class="text-muted">{{ $d['convention'] }}</td>
            <td class="tr mono">{{ number_format($d['montant'], 0, ',', ' ') }}</td>
            <td class="text-muted">{{ $d['date_paiement'] ? \Carbon\Carbon::parse($d['date_paiement'])->format('d/m/Y') : '---' }}</td>
        </tr>
        @endforeach
        <tr class="tfoot">
            <td colspan="3">Total paiements directs</td>
            <td class="tr mono">{{ number_format(array_sum(array_column($directs, 'montant')), 0, ',', ' ') }}</td>
            <td></td>
        </tr>
        </table>
        @else
        <p style="padding:16px; text-align:center; color:#9ca3af; font-style:italic;">Aucun paiement direct enregistre.</p>
        @endif
    </td>
</tr>
</table>

{{-- ═══════════════ FOOTER ═══════════════ --}}
<table class="w100" cellpadding="0" cellspacing="0" style="margin-top:8px;">
<tr>
    <td style="border-top:1px solid #e5e7eb; padding-top:6px; border:none;">
        <table class="w100" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:0; border:none; font-size:8px; color:#9ca3af;">Document genere par CIFEU - DSI Universite Joseph KI-ZERBO</td>
            <td style="padding:0; border:none; font-size:8px; color:#9ca3af; text-align:right;">{{ $dateGen }}</td>
        </tr>
        </table>
    </td>
</tr>
</table>

</body>
</html>
