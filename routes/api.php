<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\BannerController;
use App\Http\Controllers\Api\V1\Admin\BlogController;
use App\Http\Controllers\Api\V1\Admin\CollectionController;
use App\Http\Controllers\Api\V1\Admin\FoodController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\FaqController;
use App\Http\Controllers\Api\V1\Admin\OfferController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\VerificationController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\ContactController;

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
    //*** Collections */
    Route::apiResource('collections',CollectionController::class);
    //*** Food api */
    Route::apiResource('foods', FoodController::class);
    //*** Offers api */
    Route::apiResource('offers', OfferController::class);
    //*** Banner */
    Route::apiResource('banners', BannerController::class);
    //*** Blog */
    Route::apiResource('blogs', BlogController::class);

    //** settings public api
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('settings/{key}', [SettingController::class, 'show']);

    //faqs
    Route::apiResource('faqs', FaqController::class);

    //contact us
    Route::post('contact-us',[  ContactController::class, 'sendMessage'])->name('contact.sendMessage');
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


    //Order (checkout)
    Route::post('/checkout', [OrderController::class, 'checkout']);
    //Address Routes
    Route::apiResource('addresses', AddressController::class)->except(['show']);

    // User Orders Routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);


    //Admin Routes (Protected by role:admin middleware in RouteServiceProvider)
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        //user management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
        Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
        Route::post('/orders/{id}/refund', [AdminOrderController::class, 'refund']);
        Route::get('/orders/transaction/{transactionId}', [AdminOrderController::class, 'checkTransactionStatus']);

        //settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'update']);
    });

    // Fallback route for undefined API endpoints

    Route::fallback(function () {
        return response_error('The requested API endpoint does not exist.', [], 404);
    });

    //test fiserv api
    });

