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
            'utilisateur_email' => ['required', 'string', 'utilisateur_email', 'max:255', 'unique:users,email'],
            'utilisateur_mot_de_passe' => ['required', Password::min(8)],
            'utilisateur_role' => ['required', Rule::enum(RoleUtilisateur::class)],
            'utilisateur_telephone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => "L'adresse e-mail est obligatoire.",
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'utilisateur_role.required' => 'Le rôle est obligatoire.',
        ];
    }
}
