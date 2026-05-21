<?php

namespace App\Http\Requests\Daf;

use App\Enums\StatutFinalProjet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CloturerProjetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'date_fin_reelle' => ['required', 'date', 'before_or_equal:today'],
            'statut_final' => ['required', new Enum(StatutFinalProjet::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'date_fin_reelle.required' => 'La date de clôture est obligatoire.',
            'date_fin_reelle.date' => 'La date de clôture est invalide.',
            'date_fin_reelle.before_or_equal' => 'La date de clôture ne peut pas être dans le futur.',
            'statut_final.required' => 'Le statut final du projet est obligatoire.',
            'statut_final.Illuminate\Validation\Rules\Enum' => 'Le statut final doit être « succès » ou « échec ».',
        ];
    }
}
