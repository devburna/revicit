<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\StoreCompanyWalletRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateCompanyWalletRequest;
use App\Http\Requests\VerifyFlutterwaveTransactionRequest;
use App\Models\CompanyWallet;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CompanyWalletController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StorePaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StorePaymentRequest $request)
    {
        try {

            // generate payment link
            $request['tx_ref'] = Str::uuid();
            $request['name'] = "{$request->user()->first_name} {$request->user()->last_name}";
            $request['email'] = $request->user()->email;
            $request['phone'] = $request->user()->phone;
            $request['amount'] = $request->amount;
            $request['currency'] = $request->currency;
            $request['meta'] = [
                "consumer_id" => $request->company->wallet->id,
                "consumer_mac" => 'deposit',
            ];
            $request['redirect_url'] = url('/dashboard/wallet');

            $link = (new FlutterwaveController())->generatePaymentLink($request->all());

            // set payment link
            $request->company->wallet->payment_link = $link['data']['link'];

            return response()->json([
                'status' => true,
                'data' => [
                    'wallet' => $request->company->wallet,
                    'amount' => $request->amount,
                    'currency' => $request->currency
                ],
                'message' => 'success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCompanyWalletRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyWalletRequest $request)
    {
        CompanyWallet::create($request->only([
            'company_id',
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // add currency to wallet details
        $request->company->wallet->currency = 'NGN';

        return response()->json([
            'status' => true,
            'data' => $request->company->wallet,
            'message' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompanyWalletRequest  $request
     * @param  \App\Models\CompanyWallet  $companyWallet
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyWalletRequest $request, CompanyWallet $companyWallet)
    {
        return $this->show($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->company->wallet->trashed()) {
            $request->company->wallet->restore();
        } else {
            $request->company->wallet->delete();
        }

        return $this->show($request);
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
                    return $this->show($request);
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
