<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampagneController;
use App\Http\Controllers\Api\V1\PanneauController;
use App\Http\Controllers\Api\V1\TacheController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ── Health Check 
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
        'message'         => 'API FASO PAN operationnelle',
        'laravel_version' => app()->version(),
        'php_version'     => PHP_VERSION,
        'db_connected'    => $dbConnected,
    ], $dbConnected ? 200 : 500);
});

Route::prefix('v1')->group(function () {

    // ── Route publique 
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    // ── Routes authentifiées 
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me'])->name('v1.me');
        Route::put('/me',      [AuthController::class, 'updateProfile']);

        // ── Super Admin seulement 
        Route::middleware('role:super_admin')->group(function () {
            Route::apiResource('users', UserController::class);
        });

        // ── Admin + Gestionnaire 
        Route::middleware('role:super_admin|gestionnaire')->group(function () {

            Route::apiResource('panneaux', PanneauController::class)
                ->parameters(['panneaux' => 'panneau']);

            Route::apiResource('campagnes', CampagneController::class)
                ->parameters(['campagnes' => 'campagne']);

            Route::get(
                '/faces/disponibles',
                [CampagneController::class, 'facesDisponibles']
            );

            Route::patch(
                '/taches/{tache}/assigner',
                [TacheController::class, 'assigner']
            );

            Route::get(
                '/agents',
                [TacheController::class, 'agents']
            );
        });

        // ── Tâches — tous les rôles authentifiés 
        Route::get(
            '/taches',
            [TacheController::class, 'index']
        );
        Route::get(
            '/taches/{tache}',
            [TacheController::class, 'show']
        );
        Route::patch(
            '/taches/{tache}/avancer',
            [TacheController::class, 'avancer']
        );
    });
});
