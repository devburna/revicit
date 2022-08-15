<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Referral;
use App\Models\User;
use App\Models\Waitlist;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

            //update waitlist to active if exists
            if ($waitlist = Waitlist::where('email', $user->email)->first()) {
                $waitlist->update([
                    'active' => true
                ]);
            }

            // create email verification token
            $user->token = $user->createToken('email-verification', ['verify-email-address'])->plainTextToken;

            $user->notify(new VerifyEmail($user));

            return response()->json([
                'message' => trans('auth.register'),
            ], 201);
        });
    }
}
