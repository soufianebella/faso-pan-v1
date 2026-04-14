<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DisponibiliteService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampagneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assigner campagne') ?? false;
    }

    public function rules(): array
    {
        return [
            'nom'         => ['required', 'string', 'max:200'],
            'annonceur'   => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'date_debut'  => ['required', 'date', 'after_or_equal:today'],
            'date_fin'    => ['required', 'date', 'after:date_debut'],
            'affiche_path'=> ['nullable', 'string'],
            'face_ids'    => ['required', 'array', 'min:1'],
            'face_ids.*'  => [
                'required',
                'integer',
                'exists:faces,id',
                'distinct',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {

            // Ne vérifie pas les conflits si validation de base échoue
            if ($v->errors()->isNotEmpty()) return;

            $conflits = app(DisponibiliteService::class)->conflits(
                faceIds: $this->input('face_ids', []),
                debut:   $this->input('date_debut'),
                fin:     $this->input('date_fin'),
            );

            // Ajoute une erreur par face en conflit
            foreach ($conflits as $faceId) {
                $v->errors()->add(
                    "face_ids.{$faceId}",
                    "La face #{$faceId} est deja reservee sur cette periode."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'face_ids.required'      => 'Selectionnez au moins une face.',
            'face_ids.*.exists'      => 'Une face selectionnee est invalide.',
            'face_ids.*.distinct'    => 'Une face ne peut pas etre selectionnee deux fois.',
            'date_debut.after_or_equal' => 'La date de debut ne peut pas etre dans le passe.',
            'date_fin.after'         => 'La date de fin doit etre apres la date de debut.',
        ];
    }
}