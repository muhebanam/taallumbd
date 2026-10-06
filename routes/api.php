<?php

use App\Http\Controllers\Api\V1 as ApiV1;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Version 1 - /api/v1)
|--------------------------------------------------------------------------
|
| Taallum BD Mobile App & Third-Party REST API with Laravel Sanctum.
|
*/

Route::middleware(['throttle:api'])->group(function () {

    /* ─── 1. Authentication (Public) ─── */
    Route::prefix('auth')->group(function () {
        Route::post('/register', [ApiV1\AuthController::class, 'register'])->middleware('throttle:api_auth');
        Route::post('/login', [ApiV1\AuthController::class, 'login'])->middleware('throttle:api_auth');
        Route::post('/forgot-password', [ApiV1\AuthController::class, 'forgotPassword'])->middleware('throttle:api_auth');
        Route::post('/reset-password', [ApiV1\AuthController::class, 'resetPassword'])->middleware('throttle:api_auth');
    });

    /* ─── 2. Unified Search ─── */
    Route::get('/search', [ApiV1\UnifiedSearchApiController::class, 'index']);

    /* ─── 3. Courses & Curriculum (Public / Preview) ─── */
    Route::get('/courses', [ApiV1\CourseController::class, 'index']);
    Route::get('/courses/{course}', [ApiV1\CourseController::class, 'show']);
    Route::get('/courses/{course}/curriculum', [ApiV1\CurriculumController::class, 'curriculum']);
    Route::get('/lessons/{lesson}', [ApiV1\CurriculumController::class, 'lesson']);

    /* ─── 3. Teachers & Scholars Directory ─── */
    Route::get('/teachers', [ApiV1\TeacherController::class, 'index']);
    Route::get('/teachers/{teacher}', [ApiV1\TeacherController::class, 'show']);

    /* ─── 4. Public Certificate Verification ─── */
    Route::get('/certificates/verify/{code}', [ApiV1\CertificateController::class, 'verify']);

    /* ─── 5. Quran Explorer ─── */
    Route::get('/quran/surahs', [ApiV1\QuranApiController::class, 'surahs']);
    Route::get('/quran/surahs/{number}', [ApiV1\QuranApiController::class, 'surah']);

    /* ─── 6. Hadith Library ─── */
    Route::get('/hadith/books', [ApiV1\HadithApiController::class, 'books']);
    Route::get('/hadith/books/{slug}/chapters', [ApiV1\HadithApiController::class, 'chapters']);
    Route::get('/hadith/books/{slug}/hadiths', [ApiV1\HadithApiController::class, 'hadiths']);
    Route::get('/hadith/{id}', [ApiV1\HadithApiController::class, 'show']);

    /* ─── 7. Fatawa ─── */
    Route::get('/fatawa', [ApiV1\FatwaApiController::class, 'index']);
    Route::get('/fatawa/{fatwa}', [ApiV1\FatwaApiController::class, 'show']);
    Route::post('/fatawa/ask', [ApiV1\FatwaApiController::class, 'ask']);

    /* ─── 8. Subscriptions (Public Plans) ─── */
    Route::get('/subscriptions/plans', [ApiV1\SubscriptionApiController::class, 'plans']);

    /* ─── Authenticated Routes (Sanctum) ─── */
    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth management
        Route::get('/auth/me', [ApiV1\AuthController::class, 'me']);
        Route::post('/auth/logout', [ApiV1\AuthController::class, 'logout']);
        Route::post('/auth/email/resend', [ApiV1\AuthController::class, 'resendVerificationEmail']);

        // Learning progress
        Route::post('/lessons/{lesson}/progress', [ApiV1\ProgressController::class, 'updateLessonProgress']);
        Route::get('/courses/{course}/progress', [ApiV1\ProgressController::class, 'getCourseProgress']);

        // Quizzes
        Route::get('/quizzes/{quiz}', [ApiV1\QuizController::class, 'show']);
        Route::post('/quizzes/{quiz}/submit', [ApiV1\QuizController::class, 'submit']);
        Route::get('/quizzes/{quiz}/attempts', [ApiV1\QuizController::class, 'attempts']);

        // Payments & Checkout
        Route::post('/payments/initiate', [ApiV1\PaymentController::class, 'initiate']);
        Route::post('/payments/manual', [ApiV1\PaymentController::class, 'submitManual']);
        Route::get('/payments/status/{orderId}', [ApiV1\PaymentController::class, 'status']);

        // Teacher interactions
        Route::post('/teachers/{teacher}/follow', [ApiV1\TeacherController::class, 'toggleFollow']);

        // Student certificates
        Route::get('/certificates', [ApiV1\CertificateController::class, 'index']);

        // Quran memorization progress
        Route::post('/quran/progress', [ApiV1\QuranApiController::class, 'updateProgress']);

        // Notifications & Device Tokens
        Route::get('/notifications', [ApiV1\NotificationApiController::class, 'index']);
        Route::post('/notifications/{id}/read', [ApiV1\NotificationApiController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [ApiV1\NotificationApiController::class, 'markAllAsRead']);
        Route::post('/devices/register', [ApiV1\NotificationApiController::class, 'registerDevice']);

        // Subscription status
        Route::get('/subscriptions/status', [ApiV1\SubscriptionApiController::class, 'status']);
    });
});
