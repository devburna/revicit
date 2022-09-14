<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyWalletRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateCompanyWalletRequest;
use App\Models\CompanyWallet;
use Illuminate\Http\Request;
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
}
