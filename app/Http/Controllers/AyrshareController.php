<?php

namespace App\Http\Controllers;

use App\Enums\SocialPlatforms;
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

        $status = [];

        foreach ($post['platforms'] as $platform) {
            try {
                if (in_array($platform, $videos_only)) {
                    $media_urls = $post['video_urls'];
                } else {
                    $media_urls = array_merge($post['image_urls'], $post['video_urls']);
                }

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer " . env('AYRSHARE_KEY')
                ])->post(env('AYRSHARE_URL') . '/post', [
                    'post' => $post['content'],
                    'platforms' => array($platform),
                    'mediaUrls' => $media_urls,
                ])->json();

                array_push($status, [
                    'palatform' => $platform,
                    'response' => $response
                ]);
            } catch (\Throwable $th) {
                array_push($status, [
                    'palatform' => $platform,
                    'response' => $th
                ]);

                continue;
            }
        }

        return $status;
    }
}
