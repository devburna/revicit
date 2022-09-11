<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaystackController extends Controller
{
    public $paystackUrl, $paystackPublicKey, $paystackSecretKey;

    public function __construct()
    {
        $this->paystackUrl = env('PAYSTACK_PAYMENT_URL');
        $this->paystackPublicKey = env('PAYSTACK_PUBLIC_KEY');
        $this->paystackSecretKey = env('PAYSTACK_SECRET_KEY');
    }

    // list banks
    public function banks($country = 'nigeria')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->paystackSecretKey}",
                'Cache-Control' => 'no-cache'
            ])->get("{$this->paystackUrl}/bank", [
                'country' => $country
            ])->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== true)) {
                abort(422, $response['message']);
            }

            return $response;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    // verify the account number
    public function bankDetails(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->paystackSecretKey}",
                'Cache-Control' => 'no-cache'
            ])->get("{$this->paystackUrl}/bank/resolve", [
                'account_number' => $request->account_number,
                'bank_code' => $request->bank_code ?? $request->bank['code']
            ])->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== true)) {
                abort(422, $response['message']);
            }

            return $response;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    // create a transfer recipient
    public function createTransferRecipient($data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->paystackSecretKey}",
                'Cache-Control' => 'no-cache'
            ])->post("{$this->paystackUrl}/transferrecipient", [
                'type' => $data->type,
                'account_name' => $data->account_name,
                'account_number' => $data->account_number,
                'bank_code' => $data->bank_code,
                'currency' => $data->meta->bank->currency
            ])->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== true)) {
                abort(422, $response['message']);
            }

            return $response;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    // initiate a transfer
    public function initiateTransfer($amount, $recipient, $reason)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->paystackSecretKey}",
                'Cache-Control' => 'no-cache'
            ])->post("{$this->paystackUrl}/transfer", [
                'source' => 'balance',
                'amount' => $amount,
                'recipient' => $recipient,
                'reason' => $reason
            ])->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== true)) {
                abort(422, $response['message']);
            }

            return $response;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    // create a refund
    public function createRefund($transaction, $amount)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->paystackSecretKey}",
                'Cache-Control' => 'no-cache'
            ])->post("{$this->paystackUrl}/refund", [
                'transaction' => $transaction,
                'amount' => $amount
            ])->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== true)) {
                abort(422, $response['message']);
            }

            return $response;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    // verify payment
    public function verifyPayment($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->paystackSecretKey}",
                'Cache-Control' => 'no-cache'
            ])->get("{$this->paystackUrl}/transaction/verify/{$reference}")->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== true)) {
                abort(422, $response['message']);
            }

            return $response;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    // listen for transfer status
    public function webHook(Request $request)
    {
        try {
            // verify request origin
            if (!$request->header('x-paystack-signature')) {
                exit;
            }

            // re-verify payment
            $payment = $this->verifyPayment($request->data['reference']);

            if ($payment->data['reference'] !== $request->data['reference']) {
                exit;
            }

            // process transaction
            match ($request->event) {
                'charge.success' => (new StorefrontOrderController())->create($request),
                default => exit
            };

            http_response_code(200);
        } catch (\Throwable $th) {
            exit;
        }
    }
}
