<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePanneauRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('creer panneau') ?? false;
    }

    public function rules(): array
    {
        return [
            'reference'   => ['required', 'string', 'max:50',
                               Rule::unique('panneaux', 'reference')],
            'pays'        => ['nullable', 'string', 'max:100'],
            'ville'       => ['required', 'string', 'max:100'],
            'quartier'    => ['nullable', 'string', 'max:150'],
            'adresse'     => ['nullable', 'string'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'eclaire'     => ['nullable', 'boolean'],
            'hauteur_mat' => ['nullable', 'numeric', 'min:0'],
            'statut'      => ['nullable',
                               Rule::in(['actif', 'maintenance', 'hors_service'])],

            'faces'           => ['required', 'array', 'min:1', 'max:10'],
            'faces.*.numero'  => ['required', 'integer', 'min:1'],
            'faces.*.largeur' => ['required', 'numeric', 'min:0.5'],
            'faces.*.hauteur' => ['required', 'numeric', 'min:0.5'],
        ];
    }

    /**
     * Personnalise les noms des champs pour les messages d'erreur
     * Notamment pour les champs de faces, afin d'avoir des messages plus clairs
     */

    public function attributes(): array
    {
        return [
            'reference'       => 'référence du panneau',
            'faces.*.numero'  => 'numéro de la face',
            'faces.*.largeur' => 'largeur de la face',
            'faces.*.hauteur' => 'hauteur de la face',
        ];
    }
}