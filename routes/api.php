<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EntrepriseController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/entreprises', [EntrepriseController::class, 'index']);
    Route::get('/entreprises/{entreprise}', [EntrepriseController::class, 'show']);
    Route::patch('/entreprises/{entreprise}', [EntrepriseController::class, 'update']);
});
