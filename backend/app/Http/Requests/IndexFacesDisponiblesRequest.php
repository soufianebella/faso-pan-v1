<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexFacesDisponiblesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assigner campagne') ?? false;
    }

    public function rules(): array
    {
        return [
            'date_debut' => ['required', 'date'],
            // after_or_equal : une campagne d'un seul jour est valide
            'date_fin'   => ['required', 'date', 'after_or_equal:date_debut'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_fin.after_or_equal' => 'La date de fin ne peut pas être avant la date de début.',
        ];
    }
}
