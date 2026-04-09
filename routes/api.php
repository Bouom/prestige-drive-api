<?php

use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\CompanyVerificationController;
use App\Http\Controllers\Api\Admin\ContentManagementController;
use App\Http\Controllers\Api\Admin\DriverVerificationController;
use App\Http\Controllers\Api\Admin\PricingRuleController;
use App\Http\Controllers\Api\Admin\PrixController;
use App\Http\Controllers\Api\Admin\RetourChauffeurController;
use App\Http\Controllers\Api\Admin\StatisticsController;
use App\Http\Controllers\Api\Admin\SystemSettingsController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\AppSettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DriverProfileController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleBrandController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleModelController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Registration & Login
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Protected Routes (Authentication Required)
    Route::middleware(['auth:api', 'verified'])->group(function () {
        Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.update-profile');
        Route::put('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        Route::delete('/account', [AuthController::class, 'deleteAccount'])->name('auth.delete-account');

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
    });
});

// Email Verification Routes
Route::prefix('verification')->group(function () {
    Route::post('/verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/resend', [VerificationController::class, 'resend'])->name('verification.resend');
    Route::post('/status', [VerificationController::class, 'checkVerificationStatus'])->name('verification.status');
});

// Password Reset Routes
Route::prefix('password')->group(function () {
    Route::post('/request-reset', [PasswordResetController::class, 'requestReset'])->name('password.request-reset');
    Route::post('/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify-code');
    Route::post('/resend-code', [PasswordResetController::class, 'resendCode'])->name('password.resend-code');
    Route::post('/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
});

// Google OAuth Routes
Route::prefix('google')->group(function () {
    Route::get('/authorize', [GoogleAuthController::class, 'authorizeGoogle'])->name('google.authorize');
    Route::get('/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
    Route::post('/token', [GoogleAuthController::class, 'token'])->name('google.token');
});

// ========================================================================
// PUBLIC ROUTES (No Authentication Required)
// ========================================================================

// Public Content
Route::get('/pages', [ContentManagementController::class, 'indexPages']);
Route::get('/news', [ContentManagementController::class, 'indexNews']);
Route::get('/banners', [ContentManagementController::class, 'indexBanners']);
Route::get('/partners', [ContentManagementController::class, 'indexPartners']);
Route::get('/faqs', [ContentManagementController::class, 'indexFaqs']);

// Public Lookups
Route::get('/vehicle-brands', [VehicleBrandController::class, 'index']);
Route::get('/vehicle-models', [VehicleModelController::class, 'index']);
Route::get('/trip-types', [VehicleBrandController::class, 'tripTypes']);

// Guest Price Quote (no auth required)
Route::post('/rides/quote', [RideController::class, 'quote']);
Route::put('/rides/quote/{id}', [RideController::class, 'updateQuote']);

// ========================================================================
// AUTHENTICATED ROUTES
// ========================================================================

Route::middleware('auth:api')->group(function () {

    // --------------------------------------------------------------------
    // RIDES
    // --------------------------------------------------------------------
    Route::prefix('rides')->group(function () {
        Route::get('/my-quotes', [RideController::class, 'myQuotes']);
        Route::get('/', [RideController::class, 'index']);
        Route::get('/{uuid}', [RideController::class, 'show']);
        Route::post('/', [RideController::class, 'store']);
        Route::put('/{uuid}', [RideController::class, 'update']);
        Route::delete('/{uuid}', [RideController::class, 'destroy']);

        // Ride Actions
        Route::post('/{uuid}/cancel', [RideController::class, 'cancel']);
        Route::post('/{uuid}/start', [RideController::class, 'start']); // Driver only
        Route::post('/{uuid}/complete', [RideController::class, 'complete']); // Driver only
        Route::post('/{uuid}/assign', [RideController::class, 'assignDriver']); // Admin only
    });

    // --------------------------------------------------------------------
    // USERS & PROFILES
    // --------------------------------------------------------------------
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Admin only
        Route::get('/{uuid}', [UserController::class, 'show']);
        Route::put('/{uuid}', [UserController::class, 'update']);
        Route::post('/{uuid}/avatar', [UserController::class, 'uploadAvatar']);
        Route::put('/{uuid}/password', [UserController::class, 'updatePassword']);
    });

    // --------------------------------------------------------------------
    // DRIVER PROFILES
    // --------------------------------------------------------------------
    Route::prefix('drivers')->group(function () {
        Route::get('/', [DriverProfileController::class, 'index']);
        Route::get('/{uuid}', [DriverProfileController::class, 'show']);
        Route::post('/', [DriverProfileController::class, 'store']); // Register as driver
        Route::put('/{uuid}', [DriverProfileController::class, 'update']);
        Route::post('/{uuid}/documents', [DriverProfileController::class, 'uploadDocument']);
        Route::patch('/{uuid}/availability', [DriverProfileController::class, 'updateAvailability']);
    });

    // --------------------------------------------------------------------
    // COMPANIES
    // --------------------------------------------------------------------
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/{uuid}', [CompanyController::class, 'show']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::put('/{uuid}', [CompanyController::class, 'update']);
        Route::delete('/{uuid}', [CompanyController::class, 'destroy']);
        Route::post('/{uuid}/documents', [CompanyController::class, 'uploadDocument']);
    });

    // --------------------------------------------------------------------
    // PAYMENTS
    // --------------------------------------------------------------------
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/history', [PaymentController::class, 'history']);
        Route::get('/verify-checkout', [PaymentController::class, 'verifyCheckoutSession']);
        Route::post('/intent', [PaymentController::class, 'createIntent']);
        Route::post('/confirm', [PaymentController::class, 'confirm']);
        Route::post('/checkout-session', [PaymentController::class, 'createCheckoutSession']);
        Route::get('/{uuid}', [PaymentController::class, 'show']);
    });

    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index']);
        Route::post('/', [PaymentMethodController::class, 'store']);
        Route::put('/{id}', [PaymentMethodController::class, 'update']);
        Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
        Route::post('/{id}/set-default', [PaymentMethodController::class, 'setDefault']);
    });

    Route::prefix('refunds')->group(function () {
        Route::get('/', [RefundController::class, 'index']);
        Route::get('/{id}', [RefundController::class, 'show']);
        Route::post('/', [RefundController::class, 'store']); // Admin only
    });

    // --------------------------------------------------------------------
    // REVIEWS
    // --------------------------------------------------------------------
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::get('/{id}', [ReviewController::class, 'show']);
        Route::post('/', [ReviewController::class, 'store']); // After completed ride
        Route::post('/{id}/respond', [ReviewController::class, 'respond']); // Driver response
    });

    // --------------------------------------------------------------------
    // VEHICLES
    // --------------------------------------------------------------------
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index']);
        Route::get('/{uuid}', [VehicleController::class, 'show']);
        Route::post('/', [VehicleController::class, 'store']); // Company/Admin only
        Route::put('/{uuid}', [VehicleController::class, 'update']);
        Route::delete('/{uuid}', [VehicleController::class, 'destroy']);
    });

    // --------------------------------------------------------------------
    // NOTIFICATIONS
    // --------------------------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // ====================================================================
    // ADMIN ROUTES (Requires Admin Permission)
    // ====================================================================

    Route::prefix('admin')->middleware('admin')->group(function () {

        // Driver Verification
        Route::prefix('driver-verification')->group(function () {
            Route::get('/', [DriverVerificationController::class, 'index']);
            Route::get('/{uuid}', [DriverVerificationController::class, 'show']);
            Route::post('/{uuid}/approve', [DriverVerificationController::class, 'approve']);
            Route::post('/{uuid}/reject', [DriverVerificationController::class, 'reject']);
        });

        // Company Verification
        Route::prefix('company-verification')->group(function () {
            Route::get('/', [CompanyVerificationController::class, 'index']);
            Route::get('/{uuid}', [CompanyVerificationController::class, 'show']);
            Route::post('/{uuid}/approve', [CompanyVerificationController::class, 'approve']);
            Route::post('/{uuid}/reject', [CompanyVerificationController::class, 'reject']);
        });

        // Pricing Rules Management
        Route::prefix('pricing-rules')->group(function () {
            Route::get('/', [PricingRuleController::class, 'index']);
            Route::get('/{id}', [PricingRuleController::class, 'show']);
            Route::post('/', [PricingRuleController::class, 'store']);
            Route::put('/{id}', [PricingRuleController::class, 'update']);
            Route::delete('/{id}', [PricingRuleController::class, 'destroy']);
        });

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserManagementController::class, 'index']);
            Route::get('/{uuid}', [UserManagementController::class, 'show']);
            Route::put('/{uuid}', [UserManagementController::class, 'update']);
            Route::post('/{uuid}/activate', [UserManagementController::class, 'activate']);
            Route::post('/{uuid}/deactivate', [UserManagementController::class, 'deactivate']);
            Route::post('/{uuid}/verify-email', [UserManagementController::class, 'verifyEmail']);
            Route::post('/{uuid}/reset-password', [UserManagementController::class, 'resetPassword']);
            Route::delete('/{uuid}', [UserManagementController::class, 'destroy']);
            Route::get('/{uuid}/activity', [UserManagementController::class, 'activity']);
            Route::post('/{id}/restore', [UserManagementController::class, 'restore']);
            Route::delete('/{id}/force-delete', [UserManagementController::class, 'forceDelete']);
        });

        // Statistics & Reports
        Route::prefix('statistics')->group(function () {
            Route::get('/dashboard', [StatisticsController::class, 'index']);
            Route::get('/revenue', [StatisticsController::class, 'revenue']);
            Route::get('/rides', [StatisticsController::class, 'rides']);
            Route::get('/drivers', [StatisticsController::class, 'drivers']);
            Route::get('/users', [StatisticsController::class, 'users']);
            Route::get('/companies', [StatisticsController::class, 'companies']);
        });

        // Audit Logs
        Route::prefix('audit-logs')->group(function () {
            Route::get('/', [AuditLogController::class, 'index']);
            Route::get('/summary', [AuditLogController::class, 'summary']);
            Route::get('/resource', [AuditLogController::class, 'forResource']);
            Route::get('/user/{userId}', [AuditLogController::class, 'forUser']);
            Route::post('/cleanup', [AuditLogController::class, 'cleanup']);
            Route::get('/{auditLog}', [AuditLogController::class, 'show']);
        });

        // App Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [AppSettingController::class, 'index']);
            Route::put('/', [AppSettingController::class, 'update']);
            Route::get('/groups', [SystemSettingsController::class, 'groups']);
            Route::get('/public', [SystemSettingsController::class, 'public']);
            Route::post('/bulk-update', [SystemSettingsController::class, 'bulkUpdate']);
            Route::post('/initialize', [SystemSettingsController::class, 'initializeDefaults']);
            Route::get('/groups/{group}', [SystemSettingsController::class, 'byGroup']);
            Route::post('/', [SystemSettingsController::class, 'store']);
            Route::get('/{key}', [SystemSettingsController::class, 'show']);
            Route::put('/{key}', [SystemSettingsController::class, 'update']);
            Route::delete('/{key}', [SystemSettingsController::class, 'destroy']);
        });

        // Prix (client billing rate per km)
        Route::prefix('prix')->group(function () {
            Route::get('/', [PrixController::class, 'index']);
            Route::post('/', [PrixController::class, 'store']);
            Route::post('/{id}/activate', [PrixController::class, 'activate']);
            Route::delete('/{id}', [PrixController::class, 'destroy']);
        });

        // Retour Chauffeur (driver return flat fee)
        Route::prefix('retour-chauffeur')->group(function () {
            Route::get('/', [RetourChauffeurController::class, 'index']);
            Route::post('/', [RetourChauffeurController::class, 'store']);
            Route::post('/{id}/activate', [RetourChauffeurController::class, 'activate']);
            Route::delete('/{id}', [RetourChauffeurController::class, 'destroy']);
        });

        // Content Management (Admin CRUD)
        Route::prefix('content')->group(function () {
            // Pages
            Route::get('/pages', [ContentManagementController::class, 'indexPages']);
            Route::post('/pages', [ContentManagementController::class, 'storePage']);
            Route::put('/pages/{page}', [ContentManagementController::class, 'updatePage']);
            Route::delete('/pages/{page}', [ContentManagementController::class, 'destroyPage']);

            // News Articles
            Route::get('/news', [ContentManagementController::class, 'indexNews']);
            Route::post('/news', [ContentManagementController::class, 'storeNews']);
            Route::put('/news/{article}', [ContentManagementController::class, 'updateNews']);
            Route::delete('/news/{article}', [ContentManagementController::class, 'destroyNews']);

            // Banners
            Route::get('/banners', [ContentManagementController::class, 'indexBanners']);
            Route::post('/banners', [ContentManagementController::class, 'storeBanner']);
            Route::put('/banners/{banner}', [ContentManagementController::class, 'updateBanner']);
            Route::delete('/banners/{banner}', [ContentManagementController::class, 'destroyBanner']);

            // Partners
            Route::get('/partners', [ContentManagementController::class, 'indexPartners']);
            Route::post('/partners', [ContentManagementController::class, 'storePartner']);
            Route::put('/partners/{partner}', [ContentManagementController::class, 'updatePartner']);
            Route::delete('/partners/{partner}', [ContentManagementController::class, 'destroyPartner']);

            // FAQs
            Route::get('/faqs', [ContentManagementController::class, 'indexFaqs']);
            Route::post('/faqs', [ContentManagementController::class, 'storeFaq']);
            Route::put('/faqs/{faq}', [ContentManagementController::class, 'updateFaq']);
            Route::delete('/faqs/{faq}', [ContentManagementController::class, 'destroyFaq']);
        });
    });
});
