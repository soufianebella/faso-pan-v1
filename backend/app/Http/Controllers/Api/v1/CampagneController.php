<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexCampagneRequest;
use App\Http\Requests\StoreCampagneRequest;
use App\Http\Resources\CampagneResource;
use App\Models\Campagne;
use App\Services\CampagneService;
use App\Services\DisponibiliteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampagneController extends Controller
{
    public function __construct(
        protected readonly CampagneService      $campagneService,
        protected readonly DisponibiliteService $disponibilite,
    ) {}

    public function index(IndexCampagneRequest $request): AnonymousResourceCollection
    {
        $campagnes = $this->campagneService->lister($request->validated());

        return CampagneResource::collection($campagnes);
    }

    public function store(StoreCampagneRequest $request): JsonResponse
    {
        $campagne = $this->campagneService->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json(
            new CampagneResource($campagne),
            201
        );
    }

    public function show(Campagne $campagne): CampagneResource
    {
        $this->authorize('view', $campagne);

        return new CampagneResource(
            $campagne->load([
                'affectations.face.panneau',
                'affectations.tache',
            ])
        );
    }

    public function destroy(Campagne $campagne): JsonResponse
    {
        $this->authorize('delete', $campagne);

        $this->campagneService->cloturer($campagne);

        return response()->json([
            'message' => 'Campagne cloturee avec succes.',
        ]);
    }

    /**
     * Retourne les faces disponibles sur une période.
     * Appelé par le frontend avant la création d'une campagne.
     */
    public function facesDisponibles(Request $request): JsonResponse
    {
        $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['required', 'date', 'after:date_debut'],
        ]);

        $faces = $this->disponibilite->facesDisponibles(
            $request->input('date_debut'),
            $request->input('date_fin'),
        );

        return response()->json(['data' => $faces]);
    }
}
