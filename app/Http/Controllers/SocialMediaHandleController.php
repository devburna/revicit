<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialMediaHandleRequest;
use App\Http\Requests\UpdateSocialMediaHandleRequest;
use App\Models\SocialMediaHandle;
use Illuminate\Http\Request;

class SocialMediaHandleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $socialMediaHandles = SocialMediaHandle::where('company_id', $request->company_id)->paginate(20);

        return response()->json([
            'data' => $socialMediaHandles,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreSocialMediaHandleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreSocialMediaHandleRequest $request)
    {
        // create profile key
        $key = (new AyrshareController())->createProfile(ucfirst($request->user()->username) . 'Profile');

        return $key;

        // create profile token with key
        $token = (new AyrshareController())->createProfile($key['Profile_key']);

        // store social media handle
        $socialMediaHandle = $this->store($request);

        // returns social media handle details
        $this->show($socialMediaHandle, null, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSocialMediaHandleRequest  $request
     */
    public function store(StoreSocialMediaHandleRequest $request)
    {
        return SocialMediaHandle::create($request->only([
            'company_id',
            'key',
            'token',
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SocialMediaHandle  $socialMediaHandle
     * @return \Illuminate\Http\Response
     */
    public function show(SocialMediaHandle $socialMediaHandle, $message = 'success', $code = 200)
    {
        return response()->json([
            'data' => $socialMediaHandle,
            'message' => $message,
            'status' => true
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSocialMediaHandleRequest  $request
     * @param  \App\Models\SocialMediaHandle  $socialMediaHandle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSocialMediaHandleRequest $request, SocialMediaHandle $socialMediaHandle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialMediaHandle  $socialMediaHandle
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialMediaHandle $socialMediaHandle)
    {
        //
    }
}
