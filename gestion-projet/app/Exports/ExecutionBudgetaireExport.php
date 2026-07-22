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

class ExecutionBudgetaireExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    /** @param array<int, array<string, mixed>> $rubriques */
    public function __construct(private readonly array $rubriques, private readonly string $projetTitre) {}

    public function title(): string
    {
        return 'Exécution budgétaire';
    }

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        $rows = [];

        // ── TITRE ──
        $rows[] = ["RAPPORT D'EXÉCUTION BUDGÉTAIRE", '', '', '', '', ''];
        $rows[] = [$this->projetTitre, '', '', '', '', ''];
        $rows[] = [''];

        // ── KPI HEADERS ──
        $rows[] = ['Montant Prévu', 'Montant Dépensé', 'Montant Disponible', "Taux d'Exécution", '', ''];

        $totalPrevu = array_sum(array_column($this->rubriques, 'montant_prevu'));
        $totalConsomme = array_sum(array_column($this->rubriques, 'consomme'));
        $totalDisponible = max(0, $totalPrevu - $totalConsomme);
        $tauxExecution = $totalPrevu > 0 ? ($totalConsomme / $totalPrevu) : 0;

        $rows[] = [
            $totalPrevu,
            $totalConsomme,
            $totalDisponible,
            $tauxExecution,
            '', '',
        ];
        $rows[] = [''];

        // ── TABLE ──
        $rows[] = ['DÉTAIL DES RUBRIQUES BUDGÉTAIRES', '', '', '', '', ''];
        $rows[] = ['Rubrique', 'Convention / Bailleur', 'Montant Prévu (F)', 'Montant Dépensé (F)', 'Disponible (F)', 'Taux'];

        foreach ($this->rubriques as $r) {
            $prev = $r['montant_prevu'];
            $cons = $r['consomme'];
            $disp = max(0, $prev - $cons);
            $taux = $prev > 0 ? ($cons / $prev) : 0;

            $rows[] = [
                $r['libelle'],
                $r['convention'],
                $prev,
                $cons,
                $disp,
                $taux,
            ];
        }

        // ── TOTAL ──
        $rows[] = [
            'TOTAL',
            '',
            $totalPrevu,
            $totalConsomme,
            $totalDisponible,
            $tauxExecution,
        ];

        return $rows;
    }

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

        // Row 1 — Titre principal
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => $bleu]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bleuClair]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Row 2 — Titre projet
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 4 — KPI Headers, Row 5 — KPI Values
        $kpiHRow = 4;
        $kpiValRow = 5;
        $kpiStyles = [
            "A{$kpiHRow}" => ['bg' => $bleuClair,   'fg' => $bleu,   'vbg' => $bleuClair,   'vfg' => $bleu],
            "B{$kpiHRow}" => ['bg' => $orangeClair,  'fg' => $orange, 'vbg' => $orangeClair,  'vfg' => $orange],
            "C{$kpiHRow}" => ['bg' => $vertClair,    'fg' => $vert,   'vbg' => $vertClair,    'vfg' => $vert],
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
        $sheet->getStyle("D{$kpiHRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => $violet]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $violetClair]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("D{$kpiValRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => $violet]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $violetClair]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'numberFormat' => ['formatCode' => '0.0%'],
        ]);
        $sheet->getRowDimension($kpiHRow)->setRowHeight(18);
        $sheet->getRowDimension($kpiValRow)->setRowHeight(24);

        // Table title & header row
        $tableTitleRow = 7;
        $tableHeadRow = 8;
        $sheet->mergeCells("A{$tableTitleRow}:F{$tableTitleRow}");
        $this->sectionTitle($sheet, "A{$tableTitleRow}:F{$tableTitleRow}", 'DÉTAIL DES RUBRIQUES BUDGÉTAIRES', $bleu);
        $this->tableHeader($sheet, "A{$tableHeadRow}:F{$tableHeadRow}", $bleu, $blanc);

        // Data rows formatting
        $dataStart = 9;
        $dataEnd = $dataStart + count($this->rubriques) - 1;
        $totalRow = $dataEnd + 1;

        for ($r = $dataStart; $r <= $dataEnd; $r++) {
            $sheet->getStyle("C{$r}:E{$r}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("C{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:F{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
        }

        // Total row styling
        $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gris]],
        ]);
        $sheet->getStyle("C{$totalRow}:E{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("C{$totalRow}:F{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(15);
    }

    private function sectionTitle(Worksheet $sheet, string $range, string $text, string $color): void
    {
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
