<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\ResearchProjectController;
use App\Http\Controllers\ResearchDataController;
use App\Http\Controllers\ResearchStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AuthenticationController::class, 'issueToken'])->middleware('throttle:5,1');
Route::post('/auth/register', [AuthenticationController::class, 'register'])->middleware('throttle:3,60');
Route::post('/auth/logout', [AuthenticationController::class, 'revokeToken'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/research/projects', [ResearchProjectController::class, 'index']);
    Route::post('/research/projects', [ResearchProjectController::class, 'store'])->middleware('throttle:30,1');
    Route::get('/research/projects/{researchProject}', [ResearchProjectController::class, 'show'])->whereNumber('researchProject');
});
Route::get('/research/status', ResearchStatusController::class);
Route::get('/research/overview', [ResearchDataController::class, 'overview']);
Route::get('/research/patients', [ResearchDataController::class, 'patients']);
Route::get('/research/patients/{patientId}', [ResearchDataController::class, 'patient'])->whereNumber('patientId');
Route::get('/research/experiments', [ResearchDataController::class, 'experimentHistory']);
Route::post('/research/experiments/synthetic-baseline', [ResearchDataController::class, 'runSyntheticBaseline'])->middleware('throttle:10,1');
Route::post('/research/agents/analyze-synthetic', [ResearchDataController::class, 'runSyntheticAgents'])->middleware('throttle:10,1');
Route::post('/research/digital-twin/{patientId}/materialize', [ResearchDataController::class, 'materializeSyntheticTwin'])
    ->whereNumber('patientId')->middleware('throttle:10,1');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
