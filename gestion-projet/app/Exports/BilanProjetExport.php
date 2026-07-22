<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BilanProjetExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    /** @param array<string, mixed> $bilan */
    public function __construct(private readonly array $bilan) {}

    public function title(): string
    {
        return 'Rapport Financier';
    }

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        $p = $this->bilan['projet'];
        $a = $this->bilan['analyse_ecarts'];
        $convs = $this->bilan['conventions'];
        $demandes = $this->bilan['demandes'];
        $directs = $this->bilan['paiements_directs'];

        $rows = [];

        // ── TITRE ──
        $rows[] = ['RAPPORT FINANCIER DU PROJET', '', '', '', '', '', '', ''];
        $rows[] = [($p['titre'] ?? ''), '', '', '', '', '', '', ''];
        $rows[] = [''];

        // ── INFOS PROJET ──
        $rows[] = ['Porteur', $p['porteur'] ?? '—', '', 'Début', $p['date_debut'] ?? '—', '', 'Clôture', $p['date_fin_reelle'] ?? '—'];
        $rows[] = [''];

        // ── SYNTHÈSE ──
        $rows[] = ['SYNTHÈSE FINANCIÈRE', '', '', '', '', '', '', ''];
        $rows[] = ['Budget Prévu', 'Versements Reçus', 'Dépenses Réalisées', 'Écart (Solde)', "Taux d'Exécution", '', '', ''];
        $rows[] = [
            $a['budget_prevu'],
            $a['total_versements'],
            $a['total_consomme'],
            $a['ecart_budget'],
            ($a['taux_execution'] / 100),
            '', '', '',
        ];
        $rows[] = [''];

        // ── CONVENTIONS ──
        $rows[] = ['CONVENTIONS DE FINANCEMENT', '', '', '', '', '', '', ''];
        $rows[] = ['Bailleur', 'Convention', 'Montant (F)', 'Versements (F)', 'Dépenses (F)', 'Reliquat (F)', 'Taux', ''];
        foreach ($convs as $c) {
            $rows[] = [
                ($c['bailleur_sigle'] ?? $c['bailleur']),
                $c['titre'],
                $c['montant_fcfa'],
                $c['total_versements'],
                $c['total_consomme'],
                $c['solde_engagement'],
                ($c['taux_execution'] / 100),
                '',
            ];
        }
        $rows[] = [
            'TOTAL', '',
            array_sum(array_column($convs, 'montant_fcfa')),
            array_sum(array_column($convs, 'total_versements')),
            $a['total_consomme'],
            array_sum(array_column($convs, 'solde_engagement')),
            '', '',
        ];
        $rows[] = [''];

        // ── DÉPENSES PAR RUBRIQUE ──
        $rows[] = ['DÉPENSES PAR RUBRIQUE', '', '', '', '', '', '', ''];
        $rows[] = ['N°', 'Objet', 'Rubrique', 'Conv.', 'Montant Prévu (F)', 'Dépenses Réalisées (F)', "Taux d'Exécution", 'Date de Paiement'];

        $i = 1;
        $totalPrev = 0;
        $totalDep = 0;
        foreach ($demandes as $d) {
            $totalPrev += $d['montant_prevu_rubrique'];
            $totalDep += $d['montant'];
            $taux = $d['montant_prevu_rubrique'] > 0 ? ($d['montant'] / $d['montant_prevu_rubrique']) : 0;
            $rows[] = [
                $i++,
                $d['objet'],
                $d['rubrique'] ?? '—',
                $d['convention'],
                $d['montant_prevu_rubrique'],
                $d['montant'],
                min(1.0, $taux),
                $d['date_paiement'] ?? '—',
            ];
        }
        foreach ($directs as $d) {
            $totalPrev += $d['montant'];
            $totalDep += $d['montant'];
            $rows[] = [
                $i++,
                $d['objet'],
                $d['rubrique'] ?? '—',
                $d['convention'],
                $d['montant'],
                $d['montant'],
                1.0,
                $d['date_paiement'] ?? '—',
            ];
        }
        $rows[] = [
            '—', 'TOTAL', '—', '—',
            $totalPrev, $totalDep,
            $totalPrev > 0 ? ($totalDep / $totalPrev) : 0,
            '—',
        ];

        return $rows;
    }

    /** @return array<string, \Closure> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->styleSheet($event->sheet->getDelegate());
            },
        ];
    }

    private function styleSheet(Worksheet $sheet): void
    {
        $p = $this->bilan['projet'];
        $convs = $this->bilan['conventions'];
        $demandes = $this->bilan['demandes'];
        $directs = $this->bilan['paiements_directs'];

        $bleu = 'FF1E3A8A';
        $bleuClair = 'FFEFF6FF';
        $violet = 'FF7C3AED';
        $violetClair = 'FFF5F3FF';
        $orange = 'FFEA580C';
        $orangeClair = 'FFFFF7ED';
        $vert = 'FF16A34A';
        $vertClair = 'FFF0FDF4';
        $gris = 'FFE2E8F0';
        $blanc = 'FFFFFFFF';
        $rouge = 'FFDC2626';

        // Row 1 — Titre principal
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'RAPPORT FINANCIER DU PROJET');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => $bleu]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bleuClair]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Row 2 — Titre projet
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 4 — Infos projet
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
        ]);
        foreach (['A4', 'D4', 'G4'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => $bleu]]]);
        }

        // Row 6 — Titre synthèse
        $synthRow = 6;
        $sheet->mergeCells("A{$synthRow}:H{$synthRow}");
        $this->sectionTitle($sheet, "A{$synthRow}:H{$synthRow}", 'SYNTHÈSE FINANCIÈRE', $bleu);

        // Row 7 — Headers KPI
        $kpiHRow = 7;
        $kpiValRow = 8;
        $kpiStyles = [
            "A{$kpiHRow}" => ['bg' => $bleuClair,   'fg' => $bleu,   'vbg' => $bleuClair,   'vfg' => $bleu],
            "B{$kpiHRow}" => ['bg' => $violetClair,  'fg' => $violet, 'vbg' => $violetClair,  'vfg' => $violet],
            "C{$kpiHRow}" => ['bg' => $orangeClair,  'fg' => $orange, 'vbg' => $orangeClair,  'vfg' => $orange],
            "D{$kpiHRow}" => ['bg' => $vertClair,    'fg' => $vert,   'vbg' => $vertClair,    'vfg' => $vert],
        ];
        foreach ($kpiStyles as $cell => $s) {
            $valCell = str_replace((string) $kpiHRow, (string) $kpiValRow, $cell);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => $s['fg']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $s['bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getStyle($valCell)->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => $s['vfg']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $s['vbg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'numberFormat' => ['formatCode' => '#,##0" F"'],
            ]);
        }
        // Taux d'exécution
        $sheet->getStyle("E{$kpiHRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => $bleu]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bleuClair]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("E{$kpiValRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => $bleu]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bleuClair]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'numberFormat' => ['formatCode' => '0"%"'],
        ]);
        $sheet->getRowDimension($kpiHRow)->setRowHeight(18);
        $sheet->getRowDimension($kpiValRow)->setRowHeight(24);

        // Convention section — determine row
        $convTitleRow = 10;
        $convHeadRow = 11;
        $convDataStart = 12;
        $convDataEnd = $convDataStart + count($convs) - 1;
        $convTotalRow = $convDataEnd + 1;

        $sheet->mergeCells("A{$convTitleRow}:H{$convTitleRow}");
        $this->sectionTitle($sheet, "A{$convTitleRow}:H{$convTitleRow}", 'CONVENTIONS DE FINANCEMENT', $bleu);

        $this->tableHeader($sheet, "A{$convHeadRow}:H{$convHeadRow}", $bleu, $blanc);

        for ($r = $convDataStart; $r <= $convDataEnd; $r++) {
            $sheet->getStyle("C{$r}:F{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('0.0"%"');
            $sheet->getStyle("C{$r}:G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:H{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
        }

        // Convention total row
        $sheet->getStyle("A{$convTotalRow}:H{$convTotalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gris]],
        ]);
        $sheet->getStyle("C{$convTotalRow}:F{$convTotalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("C{$convTotalRow}:G{$convTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Rubrique section
        $totalLines = count($demandes) + count($directs);
        $rubTitleRow = $convTotalRow + 2;
        $rubHeadRow = $rubTitleRow + 1;
        $rubDataStart = $rubHeadRow + 1;
        $rubDataEnd = $rubDataStart + $totalLines - 1;
        $rubTotalRow = $rubDataEnd + 1;

        $sheet->mergeCells("A{$rubTitleRow}:H{$rubTitleRow}");
        $this->sectionTitle($sheet, "A{$rubTitleRow}:H{$rubTitleRow}", 'DÉPENSES PAR RUBRIQUE', $bleu);

        $this->tableHeader($sheet, "A{$rubHeadRow}:H{$rubHeadRow}", $bleu, $blanc);

        for ($r = $rubDataStart; $r <= $rubDataEnd; $r++) {
            $sheet->getStyle("E{$r}:F{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('0"%"');
            $sheet->getStyle("A{$r}:H{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("E{$r}:G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Color taux cell green
            $sheet->getStyle("G{$r}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $vert]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $vertClair]],
            ]);

            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:D{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
        }

        // Rubrique total
        $sheet->getStyle("A{$rubTotalRow}:H{$rubTotalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gris]],
        ]);
        $sheet->getStyle("E{$rubTotalRow}:F{$rubTotalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G{$rubTotalRow}")->getNumberFormat()->setFormatCode('0"%"');
        $sheet->getStyle("E{$rubTotalRow}:G{$rubTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(14);
    }

    private function sectionTitle(Worksheet $sheet, string $range, string $text, string $color): void
    {
        [$start] = explode(':', $range);
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => $color]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => $color]]],
        ]);
    }

    private function tableHeader(Worksheet $sheet, string $range, string $bgColor, string $fgColor): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $fgColor], 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension((int) preg_replace('/[^0-9]/', '', explode(':', $range)[0]))->setRowHeight(16);
    }
}
