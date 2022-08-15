<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function verifyPhone(Request $request)
    {
        // validate code
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        // checks if phone has been verified
        if ($request->user()->phone_verified_at) {
            throw ValidationException::withMessages([
                'code' => trans('auth.phone_verified')
            ]);
        }

        $value = Cache::get($request->user()->username . 'phone-verification-code');

        if (!$value || $value != $request->code) {
            throw ValidationException::withMessages([
                'code' => trans('auth.phone_failed')
            ]);
        }

        // verify current user's phone
        $request->user()->update([
            'phone_verified_at' => now()
        ]);

        return response()->json([
            'message' => trans('auth.phone_success'),
        ]);
    }

    public function resendPhoneVerificationCode(Request $request)
    {
        // checks if phone has been verified
        if ($request->user()->phone_verified_at) {
            throw ValidationException::withMessages([
                'phone' => trans('auth.phone_verified')
            ]);
        }

        try {

            // generate and save code
            $code = rand(000000, 999999);
            $key = $request->user()->username . '-phone-verification-code';
            Cache::put($key,  $code, now()->addMinutes(10));
            $request->user()->code = $code;

            // send code to user via sms
            $request->user()->notify(new VerifyPhone($request->user()));

            return response()->json([
                'message' => trans('auth.resend_phone_verification_code'),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error occured, please contact support.',
            ], 422);
        }
    }
}
