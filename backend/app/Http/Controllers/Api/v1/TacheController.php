<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvancerTacheRequest;
use App\Http\Requests\IndexTacheRequest;
use App\Http\Requests\StoreTacheRequest;
use App\Http\Requests\UpdateTachePhotoRequest;
use App\Http\Resources\TacheResource;
use App\Http\Resources\UserResource;
use App\Models\Tache;
use App\Models\User;
use App\Services\TacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TacheController extends Controller
{
    public function __construct(
        protected readonly TacheService $tacheService
    ) {}

    public function index(IndexTacheRequest $request): AnonymousResourceCollection
    {
        $taches = $this->tacheService->lister(
            $request->user(),
            $request->validated()
        );

        return TacheResource::collection($taches);
    }

    public function store(StoreTacheRequest $request): TacheResource
    {
        $tache = $this->tacheService->creer($request->validated());

        return new TacheResource($tache);
    }

    /**
     * Affectations sans tache pour le formulaire de création.
     */
    public function affectationsDisponibles(): JsonResponse
    {
        $this->authorize('create', Tache::class);

        $affectations = $this->tacheService->affectationsDisponibles();

        return response()->json([
            'data' => $affectations->map(fn ($a) => [
                'id'       => $a->id,
                'label'    => ($a->face->panneau->reference ?? 'N/A')
                    . ' — Face ' . ($a->face->numero ?? '?')
                    . ' · ' . ($a->campagne->nom ?? 'N/A'),
                'campagne' => [
                    'id'       => $a->campagne?->id,
                    'nom'      => $a->campagne?->nom,
                    'annonceur'=> $a->campagne?->annonceur,
                ],
                'face'     => [
                    'id'        => $a->face?->id,
                    'numero'    => $a->face?->numero,
                    'panneau'   => [
                        'reference' => $a->face?->panneau?->reference,
                        'ville'     => $a->face?->panneau?->ville,
                    ],
                ],
                'date_debut' => $a->date_debut?->format('d/m/Y'),
                'date_fin'   => $a->date_fin?->format('d/m/Y'),
            ]),
        ]);
    }

    public function show(Tache $tache): TacheResource
    {
        $this->authorize('view', $tache);

        return new TacheResource(
            $tache->load([
                'agent',
                'validePar',
                'affectation.face.panneau',
                'affectation.campagne',
            ])
        );
    }

    public function avancer(AvancerTacheRequest $request, Tache $tache): TacheResource
    {
        $this->authorize('update', $tache);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo');
        }

        $tache = $this->tacheService->avancerStatut(
            $tache,
            $request->user(),
            $data
        );

        return new TacheResource($tache);
    }

    public function updatePhoto(UpdateTachePhotoRequest $request, Tache $tache): TacheResource
    {
        $this->authorize('updatePhoto', $tache);

        $tache = $this->tacheService->updatePhoto($tache, $request->file('photo'));

        return new TacheResource($tache);
    }

    public function assigner(Request $request, Tache $tache): TacheResource
    {
        $this->authorize('assigner', $tache);

        $request->validate([
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $tache = $this->tacheService->assigner(
            $tache,
            $request->input('agent_id')
        );

        return new TacheResource($tache);
    }

    /**
     * Liste des agents disponibles pour l'assignation
     */
    public function agents(): AnonymousResourceCollection
    {
        $agents = User::role('agent_terrain')
            ->where('actif', true)
            ->select(['id', 'name', 'email'])
            ->get();

        return UserResource::collection($agents);
    }
}