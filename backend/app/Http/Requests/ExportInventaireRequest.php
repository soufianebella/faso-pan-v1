<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportInventaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('voir panneaux') ?? false;
    }

    public function rules(): array
    {
        return [
            'ville'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'quartier' => ['sometimes', 'nullable', 'string', 'max:100'],
            'statut'   => ['sometimes', 'nullable', 'string', 'in:actif,maintenance,hors_service'],
            'eclaire'  => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}
