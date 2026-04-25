<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClotureProjetExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $projet
     * @param  array<string, mixed>  $analyse
     * @param  array<int, array<string, mixed>>  $conventions
     */
    public function __construct(
        private readonly array $projet,
        private readonly array $analyse,
        private readonly array $conventions,
    ) {}

    public function title(): string
    {
        return 'Clôture projet';
    }

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        $rows = [
            ['RAPPORT DE CLÔTURE DE PROJET', '', '', ''],
            ['Titre', $this->projet['titre'], '', ''],
            ['Porteur', $this->projet['porteur'], '', ''],
            ['Statut', $this->projet['status_label'], '', ''],
            ['Date début', $this->projet['date_debut'] ?? '—', '', ''],
            ['Date fin prévue', $this->projet['date_fin_prevue'] ?? '—', '', ''],
            ['Date de clôture réelle', $this->projet['date_fin_reelle'] ?? 'Non clôturé', '', ''],
            ['', '', '', ''],
            ['ANALYSE DES ÉCARTS', '', '', ''],
            ['Budget prévu (FCFA)', $this->analyse['budget_prevu'], '', ''],
            ['Total versements (FCFA)', $this->analyse['total_versements'], '', ''],
            ['Total dépensé (FCFA)', $this->analyse['total_depenses'], '', ''],
            ['Écart budgétaire (FCFA)', $this->analyse['ecart_budget'], '', ''],
            ["Taux d'exécution (%)", $this->analyse['taux_execution'], '', ''],
            ['Écart temporel', $this->analyse['ecart_temps_label'] ?? '—', '', ''],
            ['', '', '', ''],
        ];

        if (! empty($this->conventions)) {
            $rows[] = ['CONVENTIONS', '', '', ''];
            $rows[] = ['Bailleur', 'Forme', 'Montant prévu (FCFA)', 'Versements reçus (FCFA)'];
            foreach ($this->conventions as $c) {
                $rows[] = [$c['bailleur'], $c['forme_label'], $c['montant_fcfa'], $c['total_versements']];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF065F46']],
        ]);
        $sheet->getStyle('A9')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF065F46']],
        ]);
    }
}
