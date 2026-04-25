<?php

namespace App\Http\Requests\Daf;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'date_fin_reelle.required' => 'La date de clôture est obligatoire.',
            'date_fin_reelle.date' => 'La date de clôture est invalide.',
            'date_fin_reelle.before_or_equal' => 'La date de clôture ne peut pas être dans le futur.',
        ];
    }
}
