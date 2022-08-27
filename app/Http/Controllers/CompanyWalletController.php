<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyWalletRequest;
use App\Http\Requests\UpdateCompanyWalletRequest;
use App\Models\CompanyWallet;

class CompanyWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCompanyWalletRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompanyWalletRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyWallet  $companyWallet
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyWallet $companyWallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyWallet  $companyWallet
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyWallet $companyWallet)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyWallet  $companyWallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyWallet $companyWallet)
    {
        //
    }
}
