<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AyrshareController extends Controller
{

    /**
     *
     * @param  $post  $array
     */
    public function post($post)
    {
        try {
            // $images_videos_only = ['facebook', 'instgram', 'linkedin', 'telegram', 'GMB', 'pinterest', 'twitter'];
            $videos_only = ['youtube', 'tiktok', 'linkedin', 'telegram', 'GMB', 'pinterest', 'twitter'];

            if (in_array($post['platform'], $videos_only)) {
                $media_urls = $post['video_urls'];
            } else {
                $media_urls = array_merge($post['image_urls'], $post['video_urls']);
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('AYRSHARE_KEY')
            ])->post(env('AYRSHARE_URL') . '/post', [
                'post' => $post['content'],
                'platforms' => array($post['platform']),
                'mediaUrls' => $media_urls,
            ])->json();

            // catch error
            if ($response['status'] === 'error') {
                throw ValidationException::withMessages([$response['message']]);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    public function createProfile($title)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('AYRSHARE_KEY')
            ])->post(env('AYRSHARE_URL') . '/profiles/profile', [
                'title' => $title,
            ])->json();

            // catch error
            if ($response['status'] === 'error') {
                throw ValidationException::withMessages([$response['message']]);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    public function generateToken($key)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . env('AYRSHARE_KEY')
            ])->post(env('AYRSHARE_URL') . '/profiles/generateJWT', [
                'privateKey' => env('AYRSHARE_PRIVATE_KEY'),
                'domain' => env('AYRSHARE_DOMAIN_ID'),
                'profileKey' => $key,
            ])->json();


            // catch error
            if ($response['status'] === 'error') {
                throw ValidationException::withMessages([$response['message']]);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }
}
