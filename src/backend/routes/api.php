<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\InteractionController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\SequenceController;
use App\Http\Controllers\Api\TranscriptionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// OAuth callbacks (must be outside auth middleware - called by external providers)
Route::get('/integrations/hubspot/callback', [IntegrationController::class, 'hubSpotCallback']);
Route::get('/integrations/gmail/callback', [IntegrationController::class, 'gmailCallback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/response-times', [DashboardController::class, 'responseTimes']);

    // Leads
    Route::apiResource('leads', LeadController::class);
    Route::get('/leads/{lead}/history', [LeadController::class, 'history']);
    Route::get('/leads/{lead}/similar', [LeadController::class, 'similar']);

    // Interactions
    Route::apiResource('interactions', InteractionController::class);

    // Sequences
    Route::apiResource('sequences', SequenceController::class);
    Route::post('/sequences/{sequence}/enroll/{lead}', [SequenceController::class, 'enroll']);

    // Offers
    Route::apiResource('offers', OfferController::class);
    Route::post('/offers/{offer}/send', [OfferController::class, 'send']);
    Route::post('/offers/generate', [OfferController::class, 'generate']);

    // Integrations
    Route::get('/integrations', [IntegrationController::class, 'index']);
    Route::post('/integrations/hubspot/connect', [IntegrationController::class, 'connectHubSpot']);
    Route::post('/integrations/gmail/connect', [IntegrationController::class, 'connectGmail']);
    Route::delete('/integrations/{integration}', [IntegrationController::class, 'destroy']);
    Route::post('/integrations/{integration}/sync', [IntegrationController::class, 'sync']);

    // Transcriptions
    Route::apiResource('transcriptions', TranscriptionController::class);
    Route::post('/transcriptions/voice-input', [TranscriptionController::class, 'voiceInput']);

    // Reminders
    Route::apiResource('reminders', ReminderController::class);
    Route::post('/reminders/{reminder}/complete', [ReminderController::class, 'complete']);

    // Nurturing recommendations
    Route::get('/nurturing/recommendations/{lead}', [LeadController::class, 'recommendations']);
});
