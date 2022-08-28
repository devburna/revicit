<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class FlutterwaveController extends Controller
{
    private $flutterwaveUrl, $flutterwaveSecretKey, $flutterwaveSecretHash;

    public function __construct()
    {
        $this->flutterwaveSecretKey = env('FLW_SECRET_KEY');
        $this->flutterwaveUrl = env('FLW_URL');
        $this->flutterwaveSecretHash = env('FLW_SECRET_HASH');
    }

    // generate payment link
    public function paymentLink($data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->flutterwaveSecretKey}"
            ])->post("{$this->flutterwaveUrl}/payments", [
                'tx_ref' => Str::uuid(),
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'redirect_url' => url('/dashboard'),
                'meta' => [
                    'consumer_id' => $data['consumer_id'],
                    'consumer_mac' => $data['consumer_mac']
                ],
                'customer' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phonenumber' => $data['phone']
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

    // webhook
    public function verifyTransaction($data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->flutterwaveSecretKey}"
            ])->get("{$this->flutterwaveUrl}/transactions/{$data}/verify")->json();

            // catch error
            if (!$response['status'] === 'success') {
                throw ValidationException::withMessages([$response['message']]);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    public function webHook(Request $request)
    {
        // verify hash
        if (!$request->header('verify-hash') || !$request->header('verify-hash') === $this->flutterwaveSecretHash) {
            abort(401, 'Unauthorized');
        }

        // process charge completed event
        if (array_key_exists('event', $request->all()) && $request->event === 'charge.completed') {
            return (new PaymentController())->webHook($request->all());
        }
    }
}
