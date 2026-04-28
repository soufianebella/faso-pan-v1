<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangerStatutPanneauRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modifier panneau') ?? false;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::in(['actif', 'maintenance', 'hors_service'])],
            'motif'  => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motif.min' => 'La justification doit contenir au moins 10 caractères.',
        ];
    }
}
