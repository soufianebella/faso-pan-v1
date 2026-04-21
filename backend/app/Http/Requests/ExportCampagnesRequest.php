<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportCampagnesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assigner campagne') ?? false;
    }

    public function rules(): array
    {
        return [
            'annonceur' => ['sometimes', 'nullable', 'string', 'max:200'],
            'statut'    => ['sometimes', 'nullable', 'string', 'in:preparation,active,expiree,tous'],
        ];
    }
}
