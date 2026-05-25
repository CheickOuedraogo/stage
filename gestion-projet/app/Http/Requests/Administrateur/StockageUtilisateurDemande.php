<?php

namespace App\Http\Requests\Administrateur;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StockageUtilisateurDemande extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->estAdministrateur();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'utilisateur_nom' => ['required', 'string', 'max:255'],
            'utilisateur_email' => ['required', 'string', 'email', 'max:255', 'unique:utilisateurs,utilisateur_email'],
            'utilisateur_mot_de_passe' => ['required', Password::min(8)],
            'role_key' => ['required', Rule::enum(RoleUtilisateur::class)],
            'utilisateur_telephone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'utilisateur_nom.required' => 'Le nom est obligatoire.',
            'utilisateur_email.required' => "L'adresse e-mail est obligatoire.",
            'utilisateur_email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'utilisateur_mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            'role_key.required' => 'Le rôle est obligatoire.',
        ];
    }
}
