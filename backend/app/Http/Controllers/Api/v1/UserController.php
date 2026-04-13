<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        protected readonly UserService $userService
    ) {}

    public function index(): AnonymousResourceCollection
    {   $this->authorize('viewAny', User::class);
        $users = User::with('roles')
            ->latest()
            ->paginate(15);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = $data['role'];

        $user = $this->userService->create($data, $role);

        return response()->json(new UserResource($user), 201);
    }

    public function show(User $user): UserResource
    {    $this->authorize('view', $user);
        return new UserResource($user->load('roles'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {  $this->authorize('update', $user);
        $data = $request->validated();
        $role = $data['role'] ?? null;

        $updatedUser = $this->userService->update($user, $data, $role);

        return new UserResource($updatedUser);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {   $this->authorize('delete', $user);
    
        try {
            $this->userService->deactivate($user, $request->user());

            return response()->json([
                'message' => 'Utilisateur désactivé avec succès.',
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}