<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialMediaPlatformRequest;
use App\Http\Requests\UpdateSocialMediaPlatformRequest;
use App\Models\SocialMediaPlatform;

class SocialMediaPlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $socialMediaPlatforms = SocialMediaPlatform::withTrashed()->get();

        return response()->json([
            'data' => $socialMediaPlatforms,
            'message' => 'success',
            'status' => true,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSocialMediaPlatformRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSocialMediaPlatformRequest $request)
    {
        $socialMediaPlatform =  SocialMediaPlatform::create($request->only([
            'name',
            'slug',
            'video',
            'image',
            'reels'
        ]));

        return $this->show($socialMediaPlatform, null, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SocialMediaPlatform  $socialMediaPlatform
     * @return \Illuminate\Http\Response
     */
    public function show(SocialMediaPlatform $socialMediaPlatform, $message = 'success', $code = 200)
    {
        return response()->json([
            'data' => $socialMediaPlatform,
            'message' => $message,
            'status' => true
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSocialMediaPlatformRequest  $request
     * @param  \App\Models\SocialMediaPlatform  $socialMediaPlatform
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSocialMediaPlatformRequest $request, SocialMediaPlatform $socialMediaPlatform)
    {
        return $request->all();
        $socialMediaPlatform->update($request->only([
            'name',
            'slug',
            'video',
            'image',
            'reels'
        ]));

        return $this->show($socialMediaPlatform);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialMediaPlatform  $socialMediaPlatform
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialMediaPlatform $socialMediaPlatform)
    {
        if ($socialMediaPlatform->trashed()) {
            $socialMediaPlatform->restore();
        } else {
            $socialMediaPlatform->delete();
        }

        return $this->show($socialMediaPlatform);
    }
}
