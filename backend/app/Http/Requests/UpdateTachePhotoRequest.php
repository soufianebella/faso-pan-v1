<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTachePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Tache $tache */
        $tache = $this->route('tache');

        // Seul l'agent assigné ou un gestionnaire peut mettre à jour la photo
        return $this->user()->can('updatePhoto', $tache);
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'La photo est obligatoire.',
            'photo.mimes'    => 'Format accepté : jpeg, jpg, png, webp.',
            'photo.max'      => 'La photo ne doit pas dépasser 5 Mo.',
        ];
    }
}
