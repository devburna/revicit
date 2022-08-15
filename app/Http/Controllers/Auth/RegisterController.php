<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\UserMailable;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {

            // validates referral code if any
            $referral = User::where('referral_code', $request->referral_code)->first();

            if ($request->has('referral_code') && !isNull($request->referral_code)) {
                if (!$referral) {
                    throw ValidationException::withMessages([
                        'referral_code' => trans('auth.referral_code')
                    ]);
                }
            }

            // generate referral_code
            $request['referral_code'] = substr(Str::words(str_shuffle(Str::random(40) . rand(000, 999)), 1, null), -6);

            // secure user password
            $request['password'] = Hash::make($request->password);

            // create new user
            $user = User::create($request->only([
                'first_name',
                'last_name',
                'username',
                'email',
                'phone',
                'referral_code',
                'password',
            ]));

            // store referral
            if ($referral) {
                Referral::create([
                    'user_id' => $referral->id,
                    'referred_user_id' => $user->id,
                ]);
            }

            // create email verification token
            $user->token = $user->createToken('email-verification', ['verify-email-address'])->plainTextToken;

            // send email verification link
            Mail::to($user->email)->send(new UserMailable($user, 'email-verification', 'Please Verify Your Email Address'));

            return response()->json([
                'message' => trans('auth.register'),
            ], 201);
        });
    }
}
