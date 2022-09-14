<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\StorePaymentRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Http\Requests\VerifyFlutterwaveTransactionRequest;
use App\Models\CompanyWallet;

class FlutterwaveController extends Controller
{
    private $flutterwaveUrl, $flutterwaveSecretKey;

    public function __construct()
    {
        $this->flutterwaveSecretKey = env('FLUTTERWAVE_SECRET_KEY');
        $this->flutterwaveUrl = env('FLUTTERWAVE_URL');
    }

    // generate payment link
    public function generatePaymentLink($data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->flutterwaveSecretKey}"
            ])->post("{$this->flutterwaveUrl}/payments", [
                'tx_ref' => Str::uuid(),
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'redirect_url' => $data['redirect_url'],
                'meta' => $data['meta'],
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
            if (!array_key_exists('status', $response) || ($response['status'] !== 'success')) {
                throw ValidationException::withMessages([$response['message']]);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    // verify transaction
    public function verifyTransaction($data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->flutterwaveSecretKey}"
            ])->get("{$this->flutterwaveUrl}/transactions/{$data}/verify")->json();

            // catch error
            if (!array_key_exists('status', $response) || ($response['status'] !== 'success')) {
                throw ValidationException::withMessages([$response['message']]);
            }

            return $response;
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    public function webHook(VerifyFlutterwaveTransactionRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // verify transaction
                $transaction = (new FlutterwaveController())->verifyTransaction($request->transaction_id);

                // find wallet
                if (!$wallet = CompanyWallet::find($transaction['data']['meta']['consumer_id'])) {
                    throw ValidationException::withMessages(['Error occured, kindly reach out to support ASAP!']);
                }

                // checks duplicate entry
                if (Payment::where('identity', $transaction['data']['tx_ref'])->first()) {
                    throw ValidationException::withMessages(['Duplicate transaction found.']);
                }

                // get transaction status
                $status = match ($transaction['data']['status']) {
                    'success' => PaymentStatus::SUCCESSFUL(),
                    'successful' => PaymentStatus::SUCCESSFUL(),
                    'new' => PaymentStatus::SUCCESSFUL(),
                    'pending' => PaymentStatus::SUCCESSFUL(),
                    default => PaymentStatus::FAILED()
                };

                // set amount
                $amount = match ($transaction['data']['currency']) {
                    'USD' => $transaction['data']['amount'] * env('USD_RATE'),
                    default => $transaction['data']['amount']
                };

                // verify it's deposit
                match ($transaction['data']['meta']['consumer_mac']) {
                    'deposit' => 'deposit',
                    default => throw ValidationException::withMessages(['Error occured, kindly reach out to support ASAP!'])
                };

                // store payment
                $storePaymentRequest = (new StorePaymentRequest($transaction));
                $storePaymentRequest['company_wallet_id'] = $wallet->id;
                $storePaymentRequest['identity'] = $transaction['data']['tx_ref'];
                $storePaymentRequest['amount'] = $transaction['data']['amount'];
                $storePaymentRequest['currency'] = $transaction['data']['currency'];
                $storePaymentRequest['narration'] = $transaction['data']['narration'];
                $storePaymentRequest['type'] = PaymentType::CREDIT();
                $storePaymentRequest['status'] = $status;
                $storePaymentRequest['meta'] = json_encode($transaction);
                $storedTransaction = (new PaymentController())->store($storePaymentRequest);

                // credit wallet if success
                if ($storedTransaction->status->is(PaymentStatus::SUCCESSFUL()) && $wallet->credit($amount)) {
                    return (new CompanyWalletController())->show($request);
                } else {
                    throw ValidationException::withMessages(['Error occured, kindly reach out to support ASAP!']);
                }
            });
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $th->getMessage(),
            ], 422);
        }
    }
}
