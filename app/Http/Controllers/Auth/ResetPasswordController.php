<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\UserMailable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function resetPassword(ResetPasswordRequest $request)
    {
        //  update user's password
        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        // send email notification
        Mail::to($request->user()->email)->send(new UserMailable($request->user(), 'password-reset', 'Password Reset'));

        // delete token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => trans('passwords.reset')
        ]);
    }
}
