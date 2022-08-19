<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\User;
use App\Notifications\ForgotPassword;
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
        $user->notify(new ForgotPassword($user));

        unset($user->token);

        return response()->json([
            'data' => $user,
            'message' => trans('passwords.sent'),
            'status' => true,
        ]);
    }
}
