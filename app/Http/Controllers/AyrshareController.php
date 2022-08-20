<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class AyrshareController extends Controller
{
    /**
     *
     * @param  $post  $array
     */
    public function post($post)
    {
        $response = Http::post(env('AYRSHARE_URL') . '/post', [
            'post' => $post['content'],
            'platforms' => $post['platforms'],
            'mediaUrls' => $post['media']
        ])->response->json();

        return $response;
    }
}
