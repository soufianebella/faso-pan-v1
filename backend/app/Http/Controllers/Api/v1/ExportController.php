<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportCampagnesRequest;
use App\Http\Requests\ExportInventaireRequest;
use App\Services\ExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        protected readonly ExportService $exportService,
    ) {}

    public function inventaire(ExportInventaireRequest $request): StreamedResponse
    {
        return $this->exportService->inventaire($request->validated());
    }

    public function campagnesActives(ExportCampagnesRequest $request): StreamedResponse
    {
        return $this->exportService->campagnesActives($request->validated());
    }
}
