<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\PanneauController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - FASO PAN 
|--------------------------------------------------------------------------
*/

// --- Route de Santé (Health Check) ---
Route::get('/test', function () {
    $dbConnected = false;
    try {
        DB::connection()->getPdo();
        $dbConnected = true;
    } catch (\Exception $e) {
        $dbConnected = false;
    }
    return response()->json([
        'status'          => $dbConnected ? 'success' : 'error',
        'message'         => 'API FASO PAN opérationnelle',
        'laravel_version' => app()->version(),
        'php_version'     => PHP_VERSION,
        'db_connected'    => $dbConnected,
    ], $dbConnected ? 200 : 500);
});

Route::prefix('v1')->group(function () {

    // --- Routes Publiques ---
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('v1.login');

    // --- Routes Protégées (Auth obligatoire) ---
    Route::middleware('auth:sanctum')->group(function () {

        // Actions communes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me'])->name('v1.me');

        // --- ZONE ADMIN & GESTIONNAIRE UNIQUEMENT ---
        // C'est ici que l'Agent sera bloqué (Erreur 403)
        Route::middleware('role:super_admin|gestionnaire')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::apiResource('panneaux', PanneauController::class);
        });

        // --- ROUTES DE TEST RBAC ---

        // Uniquement super_admin
        Route::middleware('role:super_admin')
            ->get('/admin/test', function (Request $request) {
                return response()->json([
                    'message' => 'Accès autorisé - Niveau Super Admin',
                    'role'    => $request->user()->getRoleNames()->first(),
                ]);
            });

        // Super_admin OU gestionnaire
        Route::middleware('role:super_admin|gestionnaire')
            ->get('/gestionnaire/test', function (Request $request) {
                return response()->json([
                    'message' => 'Accès autorisé - Niveau Gestionnaire',
                    'role'    => $request->user()->getRoleNames()->first(),
                ]);
            });

        // Tout utilisateur connecté (Agent inclus)
        Route::get('/agent/test', function (Request $request) {
            return response()->json([
                'message' => 'Accès autorisé - Niveau Agent',
                'role'    => $request->user()->getRoleNames()->first(),
            ]);
        });
    });
});
