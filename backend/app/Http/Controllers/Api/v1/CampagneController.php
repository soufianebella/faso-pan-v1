<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexCampagneRequest;
use App\Http\Requests\IndexFacesDisponiblesRequest;
use App\Http\Requests\StoreCampagneRequest;
use App\Http\Resources\CampagneResource;
use App\Models\Campagne;
use App\Services\CampagneService;
use App\Services\DisponibiliteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampagneController extends Controller
{
    public function __construct(
        protected readonly CampagneService      $campagneService,
        protected readonly DisponibiliteService $disponibilite,
    ) {}

    public function index(IndexCampagneRequest $request): AnonymousResourceCollection
    {
        // Garantit que les statuts sont à jour avant toute lecture,
        // même si le scheduler n'a pas encore tourné.
        $this->campagneService->syncStatuts();

        $campagnes = $this->campagneService->lister($request->validated());

        // Compteurs globaux (1 requête GROUP BY, indépendants du filtre actif)
        $rawCounts = Campagne::selectRaw('statut, COUNT(*) as n')
            ->groupBy('statut')
            ->pluck('n', 'statut');

        return CampagneResource::collection($campagnes)
            ->additional([
                'counts' => [
                    'total'       => (int) $rawCounts->sum(),
                    'active'      => (int) ($rawCounts['active']      ?? 0),
                    'preparation' => (int) ($rawCounts['preparation'] ?? 0),
                    'expiree'     => (int) ($rawCounts['expiree']     ?? 0),
                ],
            ]);
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
     * Suppression definitive (soft delete) d'une campagne expiree.
     * Policy::forceDelete() verifie que statut === 'expiree'.
     */
    public function forceDestroy(Campagne $campagne): JsonResponse
    {
        $this->authorize('forceDelete', $campagne);

        $this->campagneService->supprimer($campagne);

        return response()->json([
            'message' => 'Campagne supprimee definitivement.',
        ]);
    }

    /**
     * Retourne les faces disponibles sur une période.
     * Appelé par le frontend avant la création d'une campagne.
     */
    public function facesDisponibles(IndexFacesDisponiblesRequest $request): JsonResponse
    {
        $faces = $this->disponibilite->facesDisponibles(
            $request->validated('date_debut'),
            $request->validated('date_fin'),
        );

        return response()->json(['data' => $faces]);
    }
}
