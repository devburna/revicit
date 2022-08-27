<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FlutterwaveController extends Controller
{
    private $flutterwaveUrl, $flutterwaveSecretKey;

    public function __construct()
    {
        $this->flutterwaveSecretKey = env('FLW_SECRET_KEY');
        $this->flutterwaveUrl = env('FLW_URL');
    }

    // generate payment link
    public function paymentLink($data)
    {
        try {
            return $response = Http::post("{$this->flutterwaveUrl}/payments")->json();
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }
}
