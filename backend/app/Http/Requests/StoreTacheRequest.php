<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('taches.manage');
    }

    public function rules(): array
    {
        return [
            'affectation_id' => [
                'required',
                'integer',
                'exists:affectations,id',
                // Anti-doublon : une affectation = une seule tache
                'unique:taches,affectation_id',
            ],
            'agent_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'affectation_id.required' => "L'affectation est obligatoire.",
            'affectation_id.exists'   => "Cette affectation n'existe pas.",
            'affectation_id.unique'   => "Une tache existe deja pour cette affectation.",
            'agent_id.exists'         => "Cet agent n'existe pas.",
        ];
    }
}
