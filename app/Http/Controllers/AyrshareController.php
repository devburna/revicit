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

        if (in_array($post['platform'], $videos_only)) {
            $media_urls = $post['video_urls'];
        } else {
            $media_urls = array_merge($post['image_urls'], $post['video_urls']);
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . env('AYRSHARE_KEY')
        ])->post(env('AYRSHARE_URL') . '/post', [
            'post' => $post['content'],
            'platforms' => array($post['platform']),
            'mediaUrls' => $media_urls,
        ])->json();
    }

    public function createProfile($title)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . env('AYRSHARE_KEY')
        ])->post(env('AYRSHARE_URL') . '/profiles/profile', [
            'title' => $title,
        ])->json();
    }

    public function generateToken($key)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . env('AYRSHARE_KEY')
        ])->post(env('AYRSHARE_URL') . '/profiles/generateJWT', [
            'privateKey' => env('AYRSHARE_PRIVATE_KEY'), // '-----BEGIN RSA PRIVATE KEY...', // required
            'domain' => env('AYRSHARE_DOMAIN_ID'), // requires
            'profileKey' => $key,
        ])->json();
    }
}
