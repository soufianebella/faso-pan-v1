<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexCampagneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\Campagne::class);
    }

    public function rules(): array
    {
        return [
            'search'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'statut'    => ['sometimes', 'nullable', 'string', 'in:preparation,active,expiree'],
            'annonceur' => ['sometimes', 'nullable', 'string', 'max:150'],
            'page'      => ['sometimes', 'integer', 'min:1'],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
