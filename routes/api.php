<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\Chat\ConversationController;
use App\Http\Controllers\Api\V1\Chat\GroupController;
use App\Http\Controllers\Api\V1\Chat\MessageController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\Payment\InvoiceController;
use App\Http\Controllers\Api\V1\Payment\OneTimePaymentController;
use App\Http\Controllers\Api\V1\Payment\PaymentMethodController;
use App\Http\Controllers\Api\V1\Payment\RefundController;
use App\Http\Controllers\Api\V1\Payment\StripePortalController;
use App\Http\Controllers\Api\V1\Payment\SubscriptionController;



// Route::post(
//     '/v1/stripe/webhook',
//     [WebhookController::class, 'handleWebhook']
// )->name('cashier.webhook');

// --- Public Routes (Authentication) ---
Route::middleware('throttle:api')->prefix('v1')->group(function () {
    // Auth related public routes
    Route::prefix('auth')->name('api.v1.auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

        Route::post('/verify', [VerificationController::class, 'verify'])->name('api.v1.auth.verify');
        Route::post('/resend-verification', [VerificationController::class, 'resendVerification'])->name('api.v1.auth.resendVerification');

        Route::post('/forgot-password', [PasswordController::class, 'forgotPassword'])->name('api.v1.auth.forgotPassword');
        Route::post('/verify-password-otp', [PasswordController::class, 'verifyResetOtp'])->name('api.v1.auth.verifyResetOtp');
        Route::post('/reset-password-with-token', [PasswordController::class, 'resetPasswordWithToken'])->name('api.v1.auth.resetPasswordWithToken');
    });

    //*** Category */
    Route::apiResource('categories', CategoryController::class);
});




// --- Protected Routes (User must be logged in) ---
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->group(function () {

    // Auth related protected routes
    Route::prefix('auth')->name('api.v1.auth.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/update-password', [PasswordController::class, 'updatePassword'])->name('updatePassword');
    });

    // Profile related protected routes
    Route::prefix('profile')->name('api.v1.profile.')->group(function () {
        Route::get('/me', [ProfileController::class, 'me'])->name('me');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
    });



    //***--- Notification Routes ---***/
    Route::prefix('notifications')->name('api.v1.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });



    // Fallback route for undefined API endpoints

    Route::fallback(function () {
        return response_error('The requested API endpoint does not exist.', [], 404);
    });
});
