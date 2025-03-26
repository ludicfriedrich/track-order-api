<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Route d'inscription
Route::post('/register', [AuthController::class, 'register']);
// Route de connexion
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Route pour récupérer l'utilisateur connecté
    Route::get('/user', [AuthController::class, 'getUser']);

    // Route de déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
});
