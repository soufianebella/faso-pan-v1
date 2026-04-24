<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignerTacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tache = $this->route('tache');

        return $this->user()?->can('assigner', $tache) ?? false;
    }

    public function rules(): array
    {
        return [
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'L\'agent sélectionné est invalide.',
        ];
    }
}
