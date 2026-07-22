<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'utilisateur_nom' => ['required', 'string', 'max:255'],
            'utilisateur_telephone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'utilisateur_nom.required' => 'Le nom est obligatoire.',
            'avatar.image' => 'Le fichier doit être une image.',
            'avatar.max' => "L'image ne doit pas dépasser 2 Mo.",
        ];
    }
}
