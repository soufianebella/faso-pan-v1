<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected readonly NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService
            ->nonLues($request->user());

        return response()->json([
            'data'  => NotificationResource::collection($notifications),
            'total' => $notifications->count(),
        ]);
    }

    public function marquerLue(
        Request      $request,
        Notification $notification
    ): JsonResponse {
        // Vérifie que la notification appartient à l'user
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Interdit.'], 403);
        }

        $this->notificationService->marquerCommeLue($notification);

        return response()->json(['message' => 'Notification lue.']);
    }

    public function marquerToutesLues(Request $request): JsonResponse
    {
        $this->notificationService
            ->marquerToutesCommeLues($request->user());

        return response()->json(['message' => 'Toutes les notifications lues.']);
    }

    public function compter(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationService
                ->compterNonLues($request->user()),
        ]);
    }
}