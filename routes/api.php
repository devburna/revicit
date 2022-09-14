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

    # create
    Route::post('', [\App\Http\Controllers\WaitlistController::class, 'store']);

    # fetch
    Route::get('', [\App\Http\Controllers\WaitlistController::class, 'index']);
});

# protected routes
Route::middleware('auth:sanctum')->group(function () {

    # reset-password - reset user's password
    Route::post('reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'resetPassword'])->middleware(['ability:reset-password']);

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
        Route::get('', [\App\Http\Controllers\UserController::class, 'index']);

        # update user
        Route::patch('', [\App\Http\Controllers\UserController::class, 'update']);

        # update avatar
        Route::post('', [\App\Http\Controllers\UserController::class, 'avatar']);
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

            # details
            Route::get('', [\App\Http\Controllers\CompanyController::class, 'show'])->can('view', 'company');

            # update details
            Route::patch('', [\App\Http\Controllers\CompanyController::class, 'update'])->can('update', 'company');

            # update logo
            Route::post('', [\App\Http\Controllers\CompanyController::class, 'update'])->can('update', 'company');

            # toggle
            Route::delete('', [\App\Http\Controllers\CompanyController::class, 'destroy'])->can('delete', 'company');
        });
    });

    # company owner
    Route::middleware(['companyOwner'])->group(function () {
        # contacts
        Route::prefix('contacts')->group(function () {

            # create
            Route::post('', [\App\Http\Controllers\ContactController::class, 'store']);

            # fetch
            Route::get('', [\App\Http\Controllers\ContactController::class, 'index']);

            # contact
            Route::prefix('{contact}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\ContactController::class, 'show'])->can('view', 'contact');

                # update details
                Route::patch('', [\App\Http\Controllers\ContactController::class, 'update'])->can('update', 'contact');

                # update logo
                Route::post('', [\App\Http\Controllers\ContactController::class, 'logo'])->can('update', 'contact');

                # toggle
                Route::delete('', [\App\Http\Controllers\ContactController::class, 'destroy'])->can('delete', 'contact');
            });
        });

        # campaigns
        Route::prefix('campaigns')->group(function () {

            # create
            Route::post('', [\App\Http\Controllers\CampaignController::class, 'create']);

            # fetch
            Route::get('', [\App\Http\Controllers\CampaignController::class, 'index']);

            # campaign
            Route::prefix('{campaign}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\CampaignController::class, 'show'])->can('view', 'campaign');

                # update details
                Route::patch('', [\App\Http\Controllers\CampaignController::class, 'update'])->can('update', 'campaign');

                # toggle
                Route::delete('', [\App\Http\Controllers\CampaignController::class, 'destroy'])->can('delete', 'campaign');
            });
        });

        # social networks
        Route::prefix('social-networks')->group(function () {

            # create
            Route::post('', [\App\Http\Controllers\AyrshareProfileController::class, 'create']);

            # details
            Route::get('', [\App\Http\Controllers\AyrshareProfileController::class, 'index']);

            # social network
            Route::prefix('{ayrshareProfile}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\AyrshareProfileController::class, 'show'])->can('view', 'ayrshareProfile');

                # update details
                Route::patch('', [\App\Http\Controllers\AyrshareProfileController::class, 'update'])->can('update', 'ayrshareProfile');

                # toggle
                Route::delete('', [\App\Http\Controllers\AyrshareProfileController::class, 'destroy'])->can('delete', 'ayrshareProfile');
            });
        });

        # social network posts
        Route::prefix('social-network-posts')->group(function () {

            # details
            Route::get('', [\App\Http\Controllers\SocialNetworkPostController::class, 'index']);

            # social network
            Route::prefix('{socialNetworkPost}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\SocialNetworkPostController::class, 'show'])->can('view', 'socialNetworkPost');

                # delete
                Route::delete('', [\App\Http\Controllers\SocialNetworkPostController::class, 'destroy'])->can('delete', 'socialNetworkPost');
            });
        });

        # service baskets
        Route::prefix('service-baskets')->group(function () {

            # create
            Route::post('', [\App\Http\Controllers\ServiceBasketController::class, 'store']);

            # details
            Route::get('', [\App\Http\Controllers\ServiceBasketController::class, 'index']);

            # social network
            Route::prefix('{serviceBasket}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\ServiceBasketController::class, 'show'])->withTrashed();

                # update details
                Route::patch('', [\App\Http\Controllers\ServiceBasketController::class, 'update']);

                # toggle
                Route::delete('', [\App\Http\Controllers\ServiceBasketController::class, 'destroy'])->withTrashed();
            });
        });

        # wallet
        Route::prefix('wallet')->group(function () {

            #balance
            Route::get('', [\App\Http\Controllers\CompanyWalletController::class, 'show']);

            #deposit
            Route::post('', [\App\Http\Controllers\CompanyWalletController::class, 'create']);

            # verify payment
            Route::patch('', [\App\Http\Controllers\CompanyWalletController::class, 'webHook']);
        });

        # payments
        Route::prefix('payments')->group(function () {

            # all
            Route::get('', [\App\Http\Controllers\PaymentController::class, 'index']);

            # social network
            Route::prefix('{payment}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\PaymentController::class, 'show'])->can('view', 'payment');

                # update details
                Route::patch('', [\App\Http\Controllers\PaymentController::class, 'update'])->can('update', 'payment');

                # toggle
                Route::delete('', [\App\Http\Controllers\PaymentController::class, 'destroy'])->can('delete', 'payment');
            });
        });

        # storefronts
        Route::prefix('storefronts')->group(function () {

            # all
            Route::get('', [\App\Http\Controllers\StorefrontController::class, 'index']);

            # create
            Route::post('', [\App\Http\Controllers\StorefrontController::class, 'store']);

            # storefront
            Route::prefix('{storefront}')->group(function () {

                # details
                Route::get('', [\App\Http\Controllers\StorefrontController::class, 'show'])->can('view', 'storefront')->withTrashed();

                # update details
                Route::patch('', [\App\Http\Controllers\StorefrontController::class, 'update'])->can('update', 'storefront');

                # update logo
                Route::post('', [\App\Http\Controllers\StorefrontController::class, 'update'])->can('update', 'storefront');

                # toggle
                Route::delete('', [\App\Http\Controllers\StorefrontController::class, 'destroy'])->can('delete', 'storefront')->withTrashed();
            });
        });

        # store owner
        Route::middleware(['storeOwner'])->group(function () {

            # products
            Route::prefix('products')->group(function () {

                # all
                Route::get('', [\App\Http\Controllers\StorefrontProductController::class, 'index']);

                # create
                Route::post('', [\App\Http\Controllers\StorefrontProductController::class, 'create']);

                # product
                Route::prefix('{storefrontProduct}')->group(function () {

                    # details
                    Route::get('', [\App\Http\Controllers\StorefrontProductController::class, 'show'])->can('view', 'storefrontProduct')->withTrashed();

                    # update details
                    Route::patch('', [\App\Http\Controllers\StorefrontProductController::class, 'update'])->can('update', 'storefrontProduct');

                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontProductController::class, 'destroy'])->can('delete', 'storefrontProduct')->withTrashed();
                });
            });

            # product images
            Route::prefix('product-images')->group(function () {

                # create
                Route::post('{storefrontProduct}/new', [\App\Http\Controllers\StorefrontProductImageController::class, 'create'])->can('view', 'storefrontProduct');

                # product
                Route::prefix('{storefrontProductImage}')->group(function () {

                    # update
                    Route::post('', [\App\Http\Controllers\StorefrontProductImageController::class, 'update'])->can('update', 'storefrontProductImage');

                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontProductImageController::class, 'destroy'])->can('delete', 'storefrontProductImage')->withTrashed();
                });
            });

            # product options
            Route::prefix('product-options')->group(function () {

                # create
                Route::post('{storefrontProduct}/new', [\App\Http\Controllers\StorefrontProductOptionController::class, 'store'])->can('view', 'storefrontProduct');

                # option
                Route::prefix('{storefrontProductOption}')->group(function () {

                    # update
                    Route::patch('', [\App\Http\Controllers\StorefrontProductOptionController::class, 'update'])->can('update', 'storefrontProductOption');

                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontProductOptionController::class, 'destroy'])->can('delete', 'storefrontProductOption')->withTrashed();
                });
            });

            # product option
            Route::prefix('product-option-values')->group(function () {

                # create
                Route::post('{storefrontProductOption}/new', [\App\Http\Controllers\StorefrontProductOptionValueController::class, 'create'])->can('view', 'storefrontProductOption');

                # option
                Route::prefix('{storefrontProductOptionValue}')->group(function () {

                    # update
                    Route::patch('', [\App\Http\Controllers\StorefrontProductOptionValueController::class, 'update'])->can('update', 'storefrontProductOptionValue');

                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontProductOptionValueController::class, 'destroy'])->can('delete', 'storefrontProductOptionValue')->withTrashed();
                });
            });

            # customers
            Route::prefix('customers')->group(function () {

                # all
                Route::get('', [\App\Http\Controllers\StorefrontCustomerController::class, 'index']);

                # create
                Route::post('', [\App\Http\Controllers\StorefrontCustomerController::class, 'create']);

                # product
                Route::prefix('{storefrontCustomer}')->group(function () {

                    # details
                    Route::get('', [\App\Http\Controllers\StorefrontCustomerController::class, 'show'])->can('view', 'storefrontCustomer')->withTrashed();

                    # update details
                    Route::patch('', [\App\Http\Controllers\StorefrontCustomerController::class, 'update'])->can('update', 'storefrontCustomer');

                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontCustomerController::class, 'destroy'])->can('delete', 'storefrontCustomer')->withTrashed();
                });
            });

            # orders
            Route::prefix('orders')->group(function () {

                # all
                Route::get('', [\App\Http\Controllers\StorefrontOrderController::class, 'index']);

                # create
                Route::post('', [\App\Http\Controllers\StorefrontOrderController::class, 'create']);

                # order
                Route::prefix('{storefrontOrder}')->group(function () {

                    # details
                    Route::get('', [\App\Http\Controllers\StorefrontOrderController::class, 'show'])->can('view', 'storefrontOrder')->withTrashed();

                    # update details
                    Route::patch('', [\App\Http\Controllers\StorefrontOrderController::class, 'update'])->can('update', 'storefrontOrder');

                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontOrderController::class, 'destroy'])->can('delete', 'storefrontOrder')->withTrashed();
                });
            });

            # order history
            Route::prefix('order-history')->group(function () {
                # history
                Route::prefix('{storefrontOrderHistory}')->group(function () {
                    # toggle
                    Route::delete('', [\App\Http\Controllers\StorefrontOrderHistoryController::class, 'destroy'])->can('delete', 'storefrontOrderHistory')->withTrashed();
                });
            });
        });
    });

    # webhooks
    Route::prefix('webhooks')->group(function () {

        # all
        Route::get('', [\App\Http\Controllers\WebHookController::class, 'index']);

        # webhook
        Route::prefix('{webhook}')->group(function () {

            # details
            Route::get('', [\App\Http\Controllers\WebHookController::class, 'show']);
        });
    });
});

Route::prefix('company/{company}')->group(function () {

    # details
    Route::get('', [\App\Http\Controllers\CompanyController::class, 'show']);

    # add contact
    Route::post('', [\App\Http\Controllers\ContactController::class, 'store']);
});

# ayrshare webhook
Route::prefix('ayrshare')->group(function () {

    # social
    Route::post('social', [\App\Http\Controllers\AyrshareProfileController::class, 'webHook']);
});
