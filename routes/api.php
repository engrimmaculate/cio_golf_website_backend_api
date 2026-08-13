<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\CommitteeController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\SponsorController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TournamentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::get('/verify-email', [AuthController::class, 'verifyEmailRedirect']);
    });

    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/upcoming', [TournamentController::class, 'upcoming']);
    Route::get('/tournaments/published', [TournamentController::class, 'published']);
    Route::get('/tournaments/overview', [TournamentController::class, 'overview']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);

    // Player routes (own profile) — must be before /players/{id}
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/players/profile', [PlayerController::class, 'myProfile']);
        Route::post('/players/profile/complete', [PlayerController::class, 'completeRegistration']);
        Route::put('/players/profile', [PlayerController::class, 'updateProfile']);
        Route::post('/players/documents', [PlayerController::class, 'uploadDocument']);
        Route::post('/players/request-payment-link', [PlayerController::class, 'requestPaymentLink']);
    });

    Route::get('/players', [PlayerController::class, 'index']);
    Route::get('/players/{id}', [PlayerController::class, 'show']);

    Route::get('/fixtures', [FixtureController::class, 'index']);

    // Fixture routes (own) — must be before /fixtures/{id}
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/fixtures/my', [FixtureController::class, 'myFixtures']);
    });

    Route::get('/fixtures/{id}', [FixtureController::class, 'show']);
    Route::get('/tournaments/{tournamentId}/fixtures', [FixtureController::class, 'index']);

    Route::get('/fixtures/{fixtureId}/scores', [ScoreController::class, 'fixtureScores']);
    Route::get('/tournaments/{tournamentId}/leaderboard', [ScoreController::class, 'leaderboard']);

    Route::get('/sponsors', [SponsorController::class, 'index']);
    Route::get('/sponsors/tier/{tier}', [SponsorController::class, 'byTier']);

    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/featured', [NewsController::class, 'featured']);
    Route::get('/news/{slug}', [NewsController::class, 'show']);

    Route::get('/team', [TeamController::class, 'index']);
    Route::get('/team/department/{department}', [TeamController::class, 'byDepartment']);

    Route::post('/contact', [ContactController::class, 'send']);

    // Public CSV template download
    Route::get('/club/csv-template', [ClubController::class, 'downloadCsvTemplate']);

    // Public payment routes
    Route::get('/payments/token/{token}', [PaymentController::class, 'initializeByToken']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // Club routes
        Route::prefix('club')->middleware('role:player')->group(function () {
            Route::get('/profile', [ClubController::class, 'profile']);
            Route::put('/profile', [ClubController::class, 'updateProfile']);
            Route::post('/players/upload', [ClubController::class, 'uploadPlayers']);
            Route::get('/players', [ClubController::class, 'listPlayers']);
        });

        // Scores
        Route::get('/scores/my', [ScoreController::class, 'myScores']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

        // Registration
        Route::post('/registration', [RegistrationController::class, 'submit']);
        Route::post('/registration/draft', [RegistrationController::class, 'saveDraft']);
        Route::get('/registration/status', [RegistrationController::class, 'status']);

        // Player payment routes
        Route::post('/payments/initialize', [PaymentController::class, 'initialize']);
        Route::get('/payments/my', [PaymentController::class, 'myPayments']);

        // Admin routes
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/stats', [AdminController::class, 'stats']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::post('/users', [AdminController::class, 'createUser']);
            Route::patch('/users/{userId}/role', [AdminController::class, 'updateRole']);
            Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
            Route::delete('/users/{userId}', [AdminController::class, 'deleteUser']);
            Route::post('/users/{userId}/verify-email', [AdminController::class, 'verifyUserEmail']);
            Route::post('/users/{userId}/unverify-email', [AdminController::class, 'unverifyUserEmail']);
            Route::post('/users/{userId}/suspend', [AdminController::class, 'suspendUser']);
            Route::post('/users/{userId}/unsuspend', [AdminController::class, 'unsuspendUser']);
            Route::post('/users/{userId}/reset-password', [AdminController::class, 'resetUserPassword']);
            Route::get('/analytics', [AdminController::class, 'analytics']);
            Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
            Route::get('/players', [AdminController::class, 'listPlayers']);
            Route::post('/players/{id}/approve', [AdminController::class, 'approvePlayer']);
            Route::post('/players/{id}/reject', [AdminController::class, 'rejectPlayer']);
            Route::post('/players/{id}/send-payment-link', [AdminController::class, 'sendPaymentLink']);
            Route::post('/players/{id}/regenerate-payment-link', [AdminController::class, 'regeneratePaymentLink']);
            Route::get('/players/{id}/payment-links', [AdminController::class, 'playerPaymentLinks']);
            Route::post('/committee', [AdminController::class, 'createCommittee']);

            // Admin payment routes
            Route::get('/payments', [PaymentController::class, 'adminPayments']);
            Route::get('/payments/stats', [PaymentController::class, 'adminStats']);
            Route::get('/webhook-logs', [PaymentController::class, 'webhookLogs']);

            // Admin Club CRUD
            Route::get('/clubs', [ClubController::class, 'all']);
            Route::post('/clubs', [ClubController::class, 'store']);
            Route::get('/clubs/{id}', [ClubController::class, 'show']);
            Route::put('/clubs/{id}', [ClubController::class, 'update']);
            Route::delete('/clubs/{id}', [ClubController::class, 'destroy']);

            // Admin Club Player CRUD
            Route::get('/clubs/{clubId}/players', [ClubController::class, 'clubPlayers']);
            Route::post('/clubs/{clubId}/players', [ClubController::class, 'storeClubPlayer']);
            Route::get('/clubs/{clubId}/players/{playerId}', [ClubController::class, 'showClubPlayer']);
            Route::put('/clubs/{clubId}/players/{playerId}', [ClubController::class, 'updateClubPlayer']);
            Route::delete('/clubs/{clubId}/players/{playerId}', [ClubController::class, 'destroyClubPlayer']);

            // Admin CRUD for news
            Route::post('/news', [NewsController::class, 'store']);
            Route::put('/news/{id}', [NewsController::class, 'update']);
            Route::delete('/news/{id}', [NewsController::class, 'destroy']);
            // Admin CRUD for tournaments
            Route::post('/tournaments', [TournamentController::class, 'store']);
            Route::put('/tournaments/{id}', [TournamentController::class, 'update']);
            Route::delete('/tournaments/{id}', [TournamentController::class, 'destroy']);
        });

        // Committee routes
        Route::prefix('committee')->middleware('role:committee,admin')->group(function () {
            Route::post('/tournaments', [CommitteeController::class, 'createTournament']);
            Route::put('/tournaments/{id}', [CommitteeController::class, 'updateTournament']);
            Route::post('/tournaments/{id}/publish', [CommitteeController::class, 'publishTournament']);

            Route::post('/fixtures', [CommitteeController::class, 'createFixture']);
            Route::delete('/fixtures/{id}', [FixtureController::class, 'destroy']);
            Route::post('/fixtures/{fixtureId}/assign', [CommitteeController::class, 'assignPlayers']);
            Route::post('/fixtures/{fixtureId}/scores', [CommitteeController::class, 'submitScores']);
            Route::post('/fixtures/{fixtureId}/scores/publish', [CommitteeController::class, 'publishScores']);

            Route::post('/notifications', [CommitteeController::class, 'sendNotification']);
            Route::post('/broadcast', [CommitteeController::class, 'broadcast']);

            Route::get('/reports/{type}', [CommitteeController::class, 'getReports']);

            // Committee Club CRUD
            Route::get('/clubs', [ClubController::class, 'all']);
            Route::post('/clubs', [ClubController::class, 'store']);
            Route::get('/clubs/{id}', [ClubController::class, 'show']);
            Route::put('/clubs/{id}', [ClubController::class, 'update']);
            Route::delete('/clubs/{id}', [ClubController::class, 'destroy']);

            // Committee Club Player CRUD
            Route::get('/clubs/{clubId}/players', [ClubController::class, 'clubPlayers']);
            Route::post('/clubs/{clubId}/players', [ClubController::class, 'storeClubPlayer']);
            Route::get('/clubs/{clubId}/players/{playerId}', [ClubController::class, 'showClubPlayer']);
            Route::put('/clubs/{clubId}/players/{playerId}', [ClubController::class, 'updateClubPlayer']);
            Route::delete('/clubs/{clubId}/players/{playerId}', [ClubController::class, 'destroyClubPlayer']);
        });
    });

    // Public payment lookup (kept last so it never shadows /payments/my)
    Route::get('/payments/{reference}', [PaymentController::class, 'getByReference']);
});
