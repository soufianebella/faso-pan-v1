<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TacheResource;
use App\Http\Resources\UserResource;
use App\Models\Tache;
use App\Models\User;
use App\Services\TacheService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TacheController extends Controller
{
    public function __construct(
        protected readonly TacheService $tacheService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Tache::class);

        $taches = $this->tacheService->lister(
            $request->user(),
            $request->only(['statut'])
        );

        return TacheResource::collection($taches);
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

    public function avancer(Request $request, Tache $tache): TacheResource
    {
        $this->authorize('update', $tache);

        $tache = $this->tacheService->avancerStatut(
            $tache,
            $request->user()
        );

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