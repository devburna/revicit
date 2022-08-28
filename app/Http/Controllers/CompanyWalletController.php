<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyWalletRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateCompanyWalletRequest;
use App\Models\CompanyWallet;
use Illuminate\Http\Request;

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
            $request['name'] = "{$request->user()->first_name} {$request->user()->last_name}";
            $request['email'] = $request->user()->email;
            $request['phone'] = $request->user()->phone;
            $request['consumer_id'] = $request->company->wallet->id;
            $request['consumer_mac'] = 'deposit';

            $link = (new FlutterwaveController())->paymentLink($request->all());

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
