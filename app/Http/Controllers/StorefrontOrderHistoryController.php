<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontOrderHistoryRequest;
use App\Http\Requests\UpdateStorefrontOrderHistoryRequest;
use App\Models\StorefrontOrderHistory;

class StorefrontOrderHistoryController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontOrderHistoryRequest  $request
     */
    public function store(StoreStorefrontOrderHistoryRequest $request)
    {
        return StorefrontOrderHistory::create($request->only([
            'storefront_order_id',
            'status',
            'comment',
            'meta'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontOrderHistory  $storefrontOrderHistory
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontOrderHistory $storefrontOrderHistory, $message = 'success', $code = 200)
    {
        // add order to data
        $storefrontOrderHistory->order;

        return response()->json([
            'status' => true,
            'data' => $storefrontOrderHistory,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontOrderHistoryRequest  $request
     * @param  \App\Models\StorefrontOrderHistory  $storefrontOrderHistory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontOrderHistoryRequest $request, StorefrontOrderHistory $storefrontOrderHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontOrderHistory  $storefrontOrderHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontOrderHistory $storefrontOrderHistory)
    {
        //
    }
}
