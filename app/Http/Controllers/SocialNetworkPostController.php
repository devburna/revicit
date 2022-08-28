<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocialNetworkPostRequest;
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
        if (!$request->company->socialNetwork) {
            return response()->json([
                'data' => null,
                'message' => 'No social network has been linked to this account.',
                'status' => false,
            ]);
        }

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
     * @param  Illuminate\Http\Request  $request
     * @param  \App\Models\SocialNetworkPost  $socialNetworkPost
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, SocialNetworkPost $socialNetworkPost)
    {
        try {
            // get post analytics
            $post = match ($request->action) {
                'analytics' => (new AyrshareController())->postAnalytics($socialNetworkPost->identity, $socialNetworkPost->platform),
                'comments' => (new AyrshareController())->postComments($socialNetworkPost->identity, $socialNetworkPost->company->socialNetwork->identity),
                default => (new AyrshareController())->postDetails($socialNetworkPost->identity, $socialNetworkPost->company->socialNetwork->identity),
            };

            $post['id'] = $socialNetworkPost->id;

            return response()->json([
                'data' => $post,
                'message' => 'success',
                'status' => true,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => $th->getMessage(),
                'status' => false,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SocialNetworkPost  $socialNetworkPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialNetworkPost $socialNetworkPost)
    {
        try {
            // delete post
            (new AyrshareController())->postDetails($socialNetworkPost->identity, $socialNetworkPost->company->socialNetwork->identity);

            if ($socialNetworkPost->trashed()) {
                $socialNetworkPost->restore();
            } else {
                $socialNetworkPost->delete();
            }

            unset($socialNetworkPost->company);

            return response()->json([
                'data' => $socialNetworkPost,
                'message' => 'success',
                'status' => true,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => $th->getMessage(),
                'status' => false,
            ]);
        }
    }
}
