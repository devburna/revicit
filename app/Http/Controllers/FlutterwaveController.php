<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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
            $response = Http::post("{$this->flutterwaveUrl}/payments", [
                'tx_ref' => Str::uuid(),
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'redirect_url' => null,
                'meta' => [
                    'consumer_id' =>  $data['consumer_id'],
                    'consumer_mac' =>  $data['consumer_mac']
                ],
                'customer' => [
                    'name' => $data['name'],
                    'email' => $data['email_address'],
                    'phonenumber' => $data['phone_number']
                ],
                'customizations' => [
                    'title' => config('app.name'),
                    'logo' => asset('img/logo.png')
                ],
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
