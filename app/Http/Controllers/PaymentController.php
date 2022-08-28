<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreWebHookRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $payments = $request->company->wallet->payments()->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => $payments,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePaymentRequest  $request
     */
    public function store(StorePaymentRequest $request)
    {
        return Payment::create($request->only([
            'company_wallet_id',
            'identity',
            'amount',
            'currency',
            'narration',
            'type',
            'status',
            'meta'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        return response()->json([
            'data' => $payment,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaymentRequest  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        return $this->show($payment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        if ($payment->trashed()) {
            $payment->restore();
        } else {
            $payment->delete();
        }

        return $this->show($payment);
    }

    public function webHook($data)
    {
        try {
            return DB::transaction(function () use ($data) {

                // verify transaction
                $response = (new FlutterwaveController())->verifyTransaction($data['data']['id']);

                // exchange curreny to ngn if usd
                $response['data']['exchange_rate'] = env('USD_RATE');
                $amount = match ($response['data']['currency']) {
                    'usd' => $response['data']['amount'] * $response['data']['exchange_rate'],
                    default => $response['data']['amount']
                };

                // set type
                $type = match ($response['data']['meta']['consumer_mac']) {
                    default => PaymentType::CREDIT()
                };

                // store payment
                $storePaymentRequest['company_wallet_id'] = $response['data']['meta']['consumer_id'];
                $storePaymentRequest['identity'] = $response['data']['tx_ref'];
                $storePaymentRequest['amount'] = $response['data']['amount'];
                $storePaymentRequest['currency'] = $response['data']['currency'];
                $storePaymentRequest['narration'] = $response['data']['narration'];
                $storePaymentRequest['type'] = $type;
                $storePaymentRequest['status'] = $response['data']['status'];
                $storePaymentRequest['meta'] = json_encode($response);
                $payment = $this->store(new StorePaymentRequest($storePaymentRequest));

                // check if transaction is deposit and credit user if payment status successful
                if ($response['data']['meta']['consumer_mac'] === 'deposit' && $response['data']['status'] === 'successful') {
                    $payment->wallet->credit($amount);
                }

                // notify company of payment
                $payment->wallet->company->notify(new Payment($payment));

                // store webhook
                $storeWebHookRequest['origin'] = 'flutterwave';
                $storeWebHookRequest['status'] = true;
                $storeWebHookRequest['data'] = json_encode($response);
                $storeWebHookRequest['message'] = 'success';
                (new WebHookController())->store(new StoreWebHookRequest($storeWebHookRequest));

                return response()->json([]);
            });
        } catch (\Throwable $th) {
            // store failed webhook
            $storeWebHookRequest['origin'] = 'flutterwave';
            $storeWebHookRequest['status'] = false;
            $storeWebHookRequest['data'] = json_encode($data);
            $storeWebHookRequest['message'] = $th->getMessage();
            (new WebHookController())->store(new StoreWebHookRequest($storeWebHookRequest));

            return response()->json([]);
        }
    }
}
