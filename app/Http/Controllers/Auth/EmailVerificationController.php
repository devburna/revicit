<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
        Mail::to($request->user()->email)->send(new UserMailable($request->user(), 'email-verified', 'Welcome to ' . config('app.name')));

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

        // create email verification token
        $request->user()->token = $request->user()->createToken('email-verification', ['verify-email-address'])->plainTextToken;

        // send email verification link
        Mail::to($request->user()->email)->send(new UserMailable($request->user(), 'email-verification', 'Please Verify Your Email Address'));

        return response()->json([
            'message' => trans('auth.resend_email_verification_link'),
        ]);
    }
}
