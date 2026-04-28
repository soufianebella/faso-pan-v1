<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangerStatutPanneauRequest;
use App\Http\Requests\IndexPanneauRequest;
use App\Http\Requests\StorePanneauRequest;
use App\Http\Requests\UpdatePanneauRequest;
use App\Http\Resources\PanneauResource;
use App\Models\Panneau;
use App\Services\PanneauService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PanneauController extends Controller
{
    public function __construct(
        protected readonly PanneauService $panneauService
    ) {}

    public function index(IndexPanneauRequest $request): AnonymousResourceCollection
    {
        $panneaux = $this->panneauService->filtrer($request->validated());

        return PanneauResource::collection($panneaux);
    }

    public function store(StorePanneauRequest $request): JsonResponse
    {
        $panneau = $this->panneauService->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json(new PanneauResource($panneau), 201);
    }

    public function show(Panneau $panneau): PanneauResource
    {
        $this->authorize('view', $panneau);

        return new PanneauResource(
            $panneau->load([
                'faces.affectationActive.campagne',
                'createur',
            ])
        );
    }

    public function update(UpdatePanneauRequest $request, Panneau $panneau): JsonResponse
    {
        $this->authorize('update', $panneau);

        $panneau = $this->panneauService->update(
            $panneau,
            $request->validated()
        );

        return response()->json(new PanneauResource($panneau));
    }

    public function destroy(Panneau $panneau): JsonResponse
    {
        $this->authorize('delete', $panneau);

        try {
            $this->panneauService->archive($panneau);

            return response()->json([
                'message' => 'Panneau archivé avec succès.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Photos de terrain liées aux faces du panneau.
     * GET /api/v1/panneaux/{panneau}/photos
     */
    public function photos(Panneau $panneau): JsonResponse
    {
        $this->authorize('view', $panneau);

        return response()->json([
            'data' => $this->panneauService->photos($panneau),
        ]);
    }

    /**
     * Change le statut d'un panneau avec justification tracée.
     * PATCH /api/v1/panneaux/{panneau}/statut
     */
    public function changerStatut(
        ChangerStatutPanneauRequest $request,
        Panneau                     $panneau
    ): JsonResponse {
        $panneau = $this->panneauService->changerStatut(
            $panneau,
            $request->validated('statut'),
            $request->validated('motif'),
            $request->user()->id,
        );

        return response()->json(new PanneauResource($panneau));
    }

    /**
     * Timeline unifiée des événements d'un panneau.
     * GET /api/v1/panneaux/{panneau}/historique
     */
    public function historique(Panneau $panneau): JsonResponse
    {
        $this->authorize('view', $panneau);

        $events = $this->panneauService->historique($panneau);

        return response()->json(['data' => $events]);
    }
}
