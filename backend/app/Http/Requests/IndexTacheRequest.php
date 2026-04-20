<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexTacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\Tache::class);
    }

    public function rules(): array
    {
        return [
            'statut'      => ['sometimes', 'nullable', 'string', 'in:en_attente,en_cours,realisee,validee'],
            'agent_id'    => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'campagne_id' => ['sometimes', 'nullable', 'integer', 'exists:campagnes,id'],
            'page'        => ['sometimes', 'integer', 'min:1'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
