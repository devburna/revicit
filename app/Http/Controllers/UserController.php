<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->referredBy) {
            $user->referredBy->profile;
        }

        return response()->json([
            'data' => $request->user(),
            'message' => 'success',
            'status' => true,
        ]);
    }

    public function update(Request $request)
    {
        // validates username if updating
        if ($request->has('username')) {
            $request->validate([
                'username' => 'required|string|max:50|unique:users,username,' . $request->user()->id,
            ]);
        }

        // validates email if updating
        if ($request->has('email')) {
            $request->validate([
                'email' => 'required|email|unique:users,email,' . $request->user()->id,
            ]);
            $request['email_verified_at'] = null;
        }

        // validates phone if updating
        if ($request->has('phone')) {
            $request->validate([
                'phone' => 'required|unique:users,phone,' . $request->user()->id,
            ]);
            $request['phone_verified_at'] = null;
        }

        // validates password if updating
        if ($request->has('password')) {
            $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) use ($request) {
                    if (!Hash::check($value, $request->user()->password)) {
                        return $fail(__(trans('passwords.failed')));
                    }
                }],
                'password' => 'required|confirmed',
            ]);
            $request['password'] = Hash::make($request->password);
        }

        $request->user()->update($request->only([
            'first_name',
            'last_name',
            'username',
            'email',
            'email_verified_at',
            'phone',
            'phone_verified_at',
            'password',
        ]));

        return $this->index($request);
    }

    public function avatar(ImageUploadRequest $request)
    {
        // upload to cloudinary
        $request['avatar'] = (new UploadApi())->upload($request->image->path(), [
            'folder' => config('app.name') . '/users/',
            'public_id' => $request->user()->id,
            'overwrite' => true,
            'resource_type' => 'image'
        ])['secure_url'];

        $request->user()->update($request->only(['avatar']));

        return $this->index($request);
    }

    public function logout(Request $request)
    {
        // delete token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => null,
            'message' => 'success',
            'status' => true,
        ]);
    }
}
