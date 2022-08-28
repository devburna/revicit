<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialNetworkPostRequest;
use App\Http\Requests\UpdateSocialNetworkPostRequest;
use App\Models\SocialNetworkPost;

class SocialNetworkPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSocialNetworkPostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSocialNetworkPostRequest $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SocialNetworkPost  $socialNetworkPost
     * @return \Illuminate\Http\Response
     */
    public function edit(SocialNetworkPost $socialNetworkPost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSocialNetworkPostRequest  $request
     * @param  \App\Models\SocialNetworkPost  $socialNetworkPost
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSocialNetworkPostRequest $request, SocialNetworkPost $socialNetworkPost)
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
