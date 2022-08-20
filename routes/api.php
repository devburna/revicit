<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

# register - new user registration
Route::post('register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);

# login - login user account
Route::post('login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);

# forgot-password - request password reset link
Route::post('forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'forgotPassword']);

# waitlist - new waitlist
Route::prefix('waitlist')->group(function () {
    Route::post('', [\App\Http\Controllers\WaitlistController::class, 'store']);
});

# protected routes
Route::middleware('auth:sanctum')->group(function () {

    # reset-password - reset user's password
    Route::post('reset-password', [\App\Http\Controllers\auth\ResetPasswordController::class, 'resetPassword'])->middleware(['ability:reset-password']);

    # verify-email - verify user's email address
    Route::prefix('verify-email')->group(function () {

        # resend email verification link
        Route::get('', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resendEmailVerificationLink']);

        # verify user's email address
        Route::post('', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verifyEmail'])->middleware(['ability:verify-email-address']);
    });

    # verify-phone - verify user's phone number
    Route::prefix('verify-phone')->group(function () {

        # resend phone verification link
        Route::post('', [\App\Http\Controllers\Auth\PhoneVerificationController::class, 'verifyPhone']);

        # verify user's phone number
        Route::get('', [\App\Http\Controllers\Auth\PhoneVerificationController::class, 'resendPhoneVerificationCode']);
    });

    # user - current user profile
    Route::prefix('user')->group(function () {

        # profile
        Route::get('', [\App\Http\Controllers\Auth\UserController::class, 'index']);

        # update user
        Route::patch('', [\App\Http\Controllers\Auth\UserController::class, 'update']);

        # update avatar
        Route::post('', [\App\Http\Controllers\Auth\UserController::class, 'avatar']);
    });

    # logout - logout current token
    Route::delete('logout', [\App\Http\Controllers\Auth\UserController::class, 'logout']);

    # referrals - current user referrals
    Route::prefix('referrals')->group(function () {

        # fetch
        Route::get('', [\App\Http\Controllers\ReferralController::class, 'index']);
    });

    # companies
    Route::prefix('companies')->group(function () {

        # create
        Route::post('', [\App\Http\Controllers\CompanyController::class, 'store']);

        # fetch
        Route::get('', [\App\Http\Controllers\CompanyController::class, 'index']);

        # company
        Route::prefix('{company}')->group(function () {

            # contacts
            Route::prefix('contacts')->group(function () {
                # create
                Route::post('', [\App\Http\Controllers\ContactController::class, 'store'])->can('view', 'company');

                # fetch
                Route::get('', [\App\Http\Controllers\ContactController::class, 'index'])->can('view', 'company');
            });

            # campaigns
            Route::prefix('campaigns')->group(function () {
                # create
                Route::post('', [\App\Http\Controllers\CampaignController::class, 'store'])->can('view', 'company');

                # fetch
                Route::get('', [\App\Http\Controllers\CampaignController::class, 'index'])->can('view', 'company');
            });

            # details
            Route::get('', [\App\Http\Controllers\CompanyController::class, 'show'])->can('view', 'company');

            # update details
            Route::patch('', [\App\Http\Controllers\CompanyController::class, 'update'])->can('update', 'company');

            # update logo
            Route::post('', [\App\Http\Controllers\CompanyController::class, 'logo'])->can('update', 'company');

            # toggle
            Route::delete('', [\App\Http\Controllers\CompanyController::class, 'destroy'])->can('delete', 'company')->withTrashed();
        });
    });

    # contacts
    Route::prefix('contacts')->group(function () {

        # fetch
        Route::get('', [\App\Http\Controllers\ContactController::class, 'index']);

        Route::prefix('{contact}')->group(function () {

            # details
            Route::get('', [\App\Http\Controllers\ContactController::class, 'show'])->can('view', 'contact');

            # update details
            Route::patch('', [\App\Http\Controllers\ContactController::class, 'update'])->can('update', 'contact');

            # update logo
            Route::post('', [\App\Http\Controllers\ContactController::class, 'logo'])->can('update', 'contact');

            # toggle
            Route::delete('', [\App\Http\Controllers\ContactController::class, 'destroy'])->can('delete', 'contact')->withTrashed();
        });
    });

    # campaigns
    Route::prefix('campaigns')->group(function () {

        # create
        Route::post('', [\App\Http\Controllers\CampaignController::class, 'store']);

        # fetch
        Route::get('', [\App\Http\Controllers\CampaignController::class, 'index']);

        # campaigns
        Route::prefix('{campaign}')->group(function () {

            # details
            Route::get('', [\App\Http\Controllers\CampaignController::class, 'show'])->can('view', 'campaign');

            # update details
            Route::patch('', [\App\Http\Controllers\CampaignController::class, 'update'])->can('update', 'campaign');

            # update logo
            Route::post('', [\App\Http\Controllers\CampaignController::class, 'logo'])->can('update', 'campaign');

            # toggle
            Route::delete('', [\App\Http\Controllers\CampaignController::class, 'destroy'])->can('delete', 'campaign')->withTrashed();
        });
    });
});
