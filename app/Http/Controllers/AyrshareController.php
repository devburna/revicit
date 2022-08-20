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
        // $images_videos_only = ['facebook', 'instgram', 'linkedin', 'telegram', 'GMB', 'pinterest', 'twitter'];
        $videos_only = ['youtube', 'tiktok', 'linkedin', 'telegram', 'GMB', 'pinterest', 'twitter'];

        if (in_array($post['platforms'], $videos_only)) {
            $media_urls = $post['video_urls'];
        } else {
            $media_urls = array_merge($post['image_urls'], $post['video_urls']);
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . env('AYRSHARE_KEY')
        ])->post(env('AYRSHARE_URL') . '/post', [
            'post' => $post['content'],
            'platforms' => array($post['platforms']),
            'mediaUrls' => $media_urls,
        ])->json();
    }
}
