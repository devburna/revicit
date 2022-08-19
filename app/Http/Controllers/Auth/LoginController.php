<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        // validate user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // create login token
        $token = $user->createToken($request->server('HTTP_USER_AGENT'))->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token
            ],
            'message' => 'success',
            'status' => true,
        ]);
    }
}
