<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyEmailRequest;
use App\Mail\UserMailable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(VerifyEmailRequest $request)
    {
        if (!$user = User::where('email', $request->email)->first()) {
            throw ValidationException::withMessages([
                'email' => trans('passwords.user'),
            ]);
        }

        // creates token
        $user->token = $user->createToken('forgot-password', ['reset-password'])->plainTextToken;

        // email token
        Mail::to($user->email)->send(new UserMailable($user, 'forgot-password', 'Forgot Password'));

        return response()->json([
            'message' => trans('passwords.sent')
        ]);
    }
}
