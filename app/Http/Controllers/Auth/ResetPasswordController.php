<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function resetPassword(ResetPasswordRequest $request)
    {
        //  update user's password
        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        // send email notification
        $request->user()->notify(new ResetPassword($request->user()));

        // delete token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => null,
            'message' => trans('passwords.reset'),
            'status' => true,
        ]);
    }
}
