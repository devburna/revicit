<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyWalletRequest;
use App\Http\Requests\UpdateCompanyWalletRequest;
use App\Models\CompanyWallet;
use Illuminate\Http\Request;

class CompanyWalletController extends Controller
{
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
