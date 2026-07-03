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
    });

    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/upcoming', [TournamentController::class, 'upcoming']);
    Route::get('/tournaments/published', [TournamentController::class, 'published']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);

    Route::get('/fixtures', [FixtureController::class, 'index']);
    Route::get('/fixtures/{id}', [FixtureController::class, 'show']);
    Route::get('/tournaments/{tournamentId}/fixtures', [FixtureController::class, 'index']);

    Route::get('/players', [PlayerController::class, 'index']);
    Route::get('/players/{id}', [PlayerController::class, 'show']);

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

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // Player routes (own profile)
        Route::get('/players/profile', [PlayerController::class, 'myProfile']);
        Route::post('/players/profile/complete', [PlayerController::class, 'completeRegistration']);
        Route::put('/players/profile', [PlayerController::class, 'updateProfile']);
        Route::post('/players/documents', [PlayerController::class, 'uploadDocument']);

        // Club routes
        Route::prefix('club')->middleware('role:player')->group(function () {
            Route::get('/profile', [ClubController::class, 'profile']);
            Route::put('/profile', [ClubController::class, 'updateProfile']);
            Route::post('/players/upload', [ClubController::class, 'uploadPlayers']);
            Route::get('/players', [ClubController::class, 'listPlayers']);
        });

        // Fixtures
        Route::get('/fixtures/my', [FixtureController::class, 'myFixtures']);

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

        // Admin routes
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/stats', [AdminController::class, 'stats']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::patch('/users/{userId}/role', [AdminController::class, 'updateRole']);
            Route::get('/analytics', [AdminController::class, 'analytics']);
            Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
            Route::get('/players', [AdminController::class, 'listPlayers']);
            Route::post('/players/{id}/approve', [AdminController::class, 'approvePlayer']);
            Route::post('/players/{id}/reject', [AdminController::class, 'rejectPlayer']);
            Route::post('/committee', [AdminController::class, 'createCommittee']);
        });

        // Committee routes
        Route::prefix('committee')->middleware('role:committee,admin')->group(function () {
            Route::post('/tournaments', [CommitteeController::class, 'createTournament']);
            Route::put('/tournaments/{id}', [CommitteeController::class, 'updateTournament']);
            Route::post('/tournaments/{id}/publish', [CommitteeController::class, 'publishTournament']);

            Route::post('/fixtures', [CommitteeController::class, 'createFixture']);
            Route::post('/fixtures/{fixtureId}/assign', [CommitteeController::class, 'assignPlayers']);
            Route::post('/fixtures/{fixtureId}/scores', [CommitteeController::class, 'submitScores']);
            Route::post('/fixtures/{fixtureId}/scores/publish', [CommitteeController::class, 'publishScores']);

            Route::post('/notifications', [CommitteeController::class, 'sendNotification']);
            Route::post('/broadcast', [CommitteeController::class, 'broadcast']);

            Route::get('/reports/{type}', [CommitteeController::class, 'getReports']);
        });
    });
});
