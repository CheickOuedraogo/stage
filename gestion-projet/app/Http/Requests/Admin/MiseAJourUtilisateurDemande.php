<?php

namespace App\Http\Requests\Administrateur;

use App\Enums\RoleUtilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MiseAJourUtilisateurDemande extends FormRequest
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
        $userId = $this->route('user')->id_utilisateur;

        return [
            'utilisateur_nom' => ['required', 'string', 'max:255'],
            'utilisateur_email' => ['required', 'string', 'utilisateur_email', 'max:255', Rule::unique('users', 'utilisateur_email')->ignore($userId, 'id_utilisateur')],
            'utilisateur_mot_de_passe' => ['nullable', Password::min(8)],
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
            'utilisateur_role.required' => 'Le rôle est obligatoire.',
        ];
    }
}
