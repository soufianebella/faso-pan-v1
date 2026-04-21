<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected readonly UserService $userService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email|max:150',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $data['email'])->first();

       
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        // Étape 4 — Vérifier que le compte est actif
        if (! $user->peutSeConnecter()) {
            return response()->json([
                'message' => 'Compte désactivé. Contactez un administrateur.',
            ], 403);
        }

        // Étape 5 — Créer le token
        //  Révocation des anciens tokens = rotation de sécurité
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        //  Révoque UNIQUEMENT le token courant
        //    pas tous les tokens (utile si multi-device en V2)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(
            new UserResource($request->user())
        );
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'max:100'],
            'email'    => ['sometimes', 'required', 'email', 'max:150',
                           \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed',
                           \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        return response()->json(
            new UserResource($this->userService->updateProfile($user, $data))
        );
    }
}
