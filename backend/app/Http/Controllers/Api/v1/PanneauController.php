<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePanneauRequest;
use App\Http\Requests\UpdatePanneauRequest;
use App\Http\Resources\PanneauResource;
use App\Models\Panneau;
use App\Services\PanneauService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PanneauController extends Controller
{
    public function __construct(
        protected readonly PanneauService $panneauService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Panneau::class);

        $panneaux = $this->panneauService->filtrer(
            $request->only(['ville', 'statut', 'eclaire', 'search'])
        );

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
            $panneau->load('faces')
        );
    }

    public function update(UpdatePanneauRequest $request, Panneau $panneau): PanneauResource
    {
        $this->authorize('update', $panneau);

        $panneau = $this->panneauService->update(
            $panneau,
            $request->validated()
        );

        return new PanneauResource($panneau);
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
}