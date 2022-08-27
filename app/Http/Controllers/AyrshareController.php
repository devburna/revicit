<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AyrshareController extends Controller
{

    public $ayrshareUrl, $ayrshareKey;

    public function __construct()
    {
        $this->ayrshareUrl = env('AYRSHARE_URL');
        $this->ayrshareKey = env('AYRSHARE_KEY');
    }

    /**
     *
     * @param  $data  $array
     */
    public function post($post, $platform, $media)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->ayrshareKey}"
            ])->post("{$this->ayrshareUrl}/post", [
                'post' => $post,
                'platforms' => $platform,
                'mediaUrls' => $media,
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
                'Authorization' => "Bearer {$this->ayrshareKey}"
            ])->post("{$this->ayrshareUrl}/profiles/profile", [
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
                'Authorization' => "Bearer {$this->ayrshareKey}"
            ])->post("{$this->ayrshareUrl}/profiles/generateJWT", [
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
