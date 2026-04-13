<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePanneauRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modifier panneau') ?? false;
    }

    public function rules(): array
    {
        $panneauId = $this->route('panneau')->id;

        return [
            'reference'   => ['sometimes', 'required', 'string', 'max:50',
                               Rule::unique('panneaux', 'reference')
                                   ->ignore($panneauId)],
            'pays'        => ['nullable', 'string', 'max:100'],
            'ville'       => ['sometimes', 'required', 'string', 'max:100'],
            'quartier'    => ['nullable', 'string', 'max:150'],
            'adresse'     => ['nullable', 'string'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'eclaire'     => ['nullable', 'boolean'],
            'hauteur_mat' => ['nullable', 'numeric', 'min:0'],
            'statut'      => ['nullable',
                               Rule::in(['actif', 'maintenance', 'hors_service'])],
            // Les faces ne sont pas modifiables après création
        ];
    }
}