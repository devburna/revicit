<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyEmail;
use App\Notifications\Welcome;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verifyEmail(Request $request)
    {
        // checks if email has been verified
        if ($request->user()->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => trans('auth.email_verified')
            ]);
        }

        // verify current user's email
        $request->user()->update([
            'email_verified_at' => now()
        ]);

        // send email notification
        $request->user()->notify(new Welcome($request->user()));

        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => trans('auth.email_success'),
        ]);
    }

    public function resendEmailVerificationLink(Request $request)
    {
        // checks if email has been verified
        if ($request->user()->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => trans('auth.email_verified')
            ]);
        }

        $user = $request->user();

        // create email verification token
        $user->token = $user->createToken('email-verification', ['verify-email-address'])->plainTextToken;

        // send email verification link
        $user->notify(new VerifyEmail($user));

        return response()->json([
            'data' => $request->user(),
            'message' => trans('auth.resend_email_verification_link'),
            'status' => true
        ]);
    }
}
