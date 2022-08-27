<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\Request;

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
        $payments = $request->company->wallet
            ->payments()->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => $payments,
            'message' => 'success',
            'status' => true,
        ]);
        return $this->show($request->company->socialNetwork);
    }

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
            $request['name'] = "{$request->user()->first_name} {$request->user()->last_name}";
            $request['email_address'] = $request->user()->email_address;
            $request['phone_number'] = $request->user()->phone_number;
            $request['consumer_id'] = $request->company->wallet->id;
            $request['consumer_mac'] = 'deposit';

            return $link = (new FlutterwaveController())->paymentLink($request->all());

            return response()->json([
                'status' => true,
                'data' => [
                    'link' => $link['data']['link']
                ],
                'message' => 'Use the link to complete your payment'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePaymentRequest  $request
     * @return \Illuminate\Http\Response
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
        //
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
        //
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
}
