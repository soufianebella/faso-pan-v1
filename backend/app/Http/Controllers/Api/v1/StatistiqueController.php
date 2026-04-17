<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Panneau;
use App\Services\StatistiquesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatistiqueController extends Controller
{
    public function __construct(
        protected readonly StatistiquesService $statistiquesService
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Panneau::class);

        return response()->json([
            'data' => $this->statistiquesService->dashboard(),
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $this->authorize('viewAny', Panneau::class);

        return response()->json([
            'data' => $this->statistiquesService->dashboard(),
        ]);
    }

    public function statistiques(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Panneau::class);

        $periode = $request->input('periode', 'ce_mois');

        return response()->json([
            'data' => $this->statistiquesService->statistiques($periode),
        ]);
    }
}