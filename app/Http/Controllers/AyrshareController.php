<?php

namespace App\Http\Controllers;

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

    public function post($data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->ayrshareKey}"
            ])->post("{$this->ayrshareUrl}/post", [
                'post' => $data['post'],
                'platforms' => array($data['platform']),
                'mediaUrls' => $data['media_urls'],
                'profileKeys' => array($data['profile'])
            ])->json();

            // catch error
            if ($response['status'] === 'error') {
                throw ValidationException::withMessages(['Error occured, kindly contact support for more information']);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    public function postDetails($data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->ayrshareKey}"
            ])->get("{$this->ayrshareUrl}/post/{$data['post']}", [
                'profileKey' => $data['profile']
            ])->json();

            // catch error
            if ($response['status'] === 'error') {
                throw ValidationException::withMessages(['Error occured, kindly contact support for more information']);
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
