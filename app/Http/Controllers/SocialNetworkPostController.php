<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialNetworkPostRequest;
use App\Http\Requests\UpdateSocialNetworkPostRequest;
use App\Models\SocialNetworkPost;
use Illuminate\Http\Request;

class SocialNetworkPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $socialNetworkPosts = $request->company->socialNetworkPosts()->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => $socialNetworkPosts,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSocialNetworkPostRequest  $request
     */
    public function store(StoreSocialNetworkPostRequest $request)
    {
        return SocialNetworkPost::create($request->only([
            'company_id',
            'identity',
            'reference',
            'post',
            'platform',
            'meta'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SocialNetworkPost  $socialNetworkPost
     * @return \Illuminate\Http\Response
     */
    public function show(SocialNetworkPost $socialNetworkPost)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialNetworkPost  $socialNetworkPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialNetworkPost $socialNetworkPost)
    {
        //
    }
}
