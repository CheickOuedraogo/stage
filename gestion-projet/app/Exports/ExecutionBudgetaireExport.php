<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExecutionBudgetaireExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /** @param array<int, array<string, mixed>> $rubriques */
    public function __construct(private readonly array $rubriques, private readonly string $projetTitre) {}

    public function title(): string
    {
        return 'Exécution budgétaire';
    }

    public function collection(): Collection
    {
        return collect($this->rubriques);
    }

    /** @return string[] */
    public function headings(): array
    {
        return [
            'Projet',
            'Rubrique',
            'Convention / Bailleur',
            'Montant prévu (FCFA)',
            'Montant dépensé (FCFA)',
            'Disponible (FCFA)',
            'Taux d\'exécution (%)',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $taux = $row['montant_prevu'] > 0 ? round(($row['consomme'] / $row['montant_prevu']) * 100, 1) : 0;

        return [
            $this->projetTitre,
            $row['libelle'],
            $row['convention'],
            $row['montant_prevu'],
            $row['consomme'],
            max(0, $row['montant_prevu'] - $row['consomme']),
            $taux,
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']],
        ]);
    }
}
