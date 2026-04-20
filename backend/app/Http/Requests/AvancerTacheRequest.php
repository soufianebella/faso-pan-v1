<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AvancerTacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation fine est gérée par la Policy dans le Controller
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tache   = $this->route('tache');
        $statut  = $tache?->statut;

        // Photo obligatoire uniquement pour la transition en_cours → realisee
        $photoRule = $statut === 'en_cours' ? 'required' : 'nullable';

        return [
            'photo' => [
                $photoRule,
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5 Mo
            ],
            'note'            => ['nullable', 'string', 'max:1000'],
            'latitude_pose'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_pose'  => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Une photo de la pose est obligatoire pour marquer la tache comme realisee.',
            'photo.image'    => 'Le fichier doit etre une image.',
            'photo.mimes'    => 'Format accepte : jpeg, jpg, png, webp.',
            'photo.max'      => 'La photo ne doit pas depasser 5 Mo.',
        ];
    }
}
