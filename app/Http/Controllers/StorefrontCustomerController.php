<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontCustomerRequest;
use App\Http\Requests\UpdateStorefrontCustomerRequest;
use App\Models\StorefrontCustomer;

class StorefrontCustomerController extends Controller
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
     * @param  \App\Http\Requests\StoreStorefrontCustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStorefrontCustomerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontCustomer  $storefrontCustomer
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontCustomer $storefrontCustomer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StorefrontCustomer  $storefrontCustomer
     * @return \Illuminate\Http\Response
     */
    public function edit(StorefrontCustomer $storefrontCustomer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontCustomerRequest  $request
     * @param  \App\Models\StorefrontCustomer  $storefrontCustomer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontCustomerRequest $request, StorefrontCustomer $storefrontCustomer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontCustomer  $storefrontCustomer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontCustomer $storefrontCustomer)
    {
        //
    }
}
