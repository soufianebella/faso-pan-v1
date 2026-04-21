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
            'date_fin'   => ['required', 'date', 'after:date_debut'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_fin.after' => 'La date de fin doit être après la date de début.',
        ];
    }
}
