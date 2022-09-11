<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreWebHookRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\WebHook;
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
}
