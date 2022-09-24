<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontOrderDeliveryAgentRequest;
use App\Http\Requests\UpdateStorefrontOrderDeliveryAgentRequest;
use App\Models\StorefrontOrder;
use App\Models\StorefrontOrderDeliveryAgent;

class StorefrontOrderDeliveryAgentController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreStorefrontOrderDeliveryAgentRequest  $request
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Http\Response
     */
    public function create(StoreStorefrontOrderDeliveryAgentRequest $request, StorefrontOrder $storefrontOrder)
    {
        if ($storefrontOrder->shipping) {
            return $this->show($storefrontOrder->shipping);
        }

        // set storefront order id
        $request['storefront_order_id'] = $storefrontOrder->id;

        $request['company'] = $request->company_name;

        // create order delivery agent
        $storefrontOrderDeliveryAgent = $this->store($request);

        return $this->show($storefrontOrderDeliveryAgent, 'success', 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontOrderDeliveryAgentRequest  $request
     */
    public function store(StoreStorefrontOrderDeliveryAgentRequest $request)
    {
        return StorefrontOrderDeliveryAgent::create($request->only([
            'storefront_order_id',
            'first_name',
            'last_name',
            'phone',
            'company',
            'tracking_link',
            'default'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontOrderDeliveryAgent  $storefrontOrderDeliveryAgent
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontOrderDeliveryAgent $storefrontOrderDeliveryAgent, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $storefrontOrderDeliveryAgent,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontOrderDeliveryAgentRequest  $request
     * @param  \App\Models\StorefrontOrderDeliveryAgent  $storefrontOrderDeliveryAgent
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontOrderDeliveryAgentRequest $request, StorefrontOrderDeliveryAgent $storefrontOrderDeliveryAgent)
    {
        // update
        $storefrontOrderDeliveryAgent->update($request->only([
            'first_name',
            'last_name',
            'phone',
            'company',
            'tracking_link',
            'default'
        ]));

        return $this->show($storefrontOrderDeliveryAgent);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontOrderDeliveryAgent  $storefrontOrderDeliveryAgent
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontOrderDeliveryAgent $storefrontOrderDeliveryAgent)
    {
        if ($storefrontOrderDeliveryAgent->trashed()) {
            $storefrontOrderDeliveryAgent->restore();
        } else {
            $storefrontOrderDeliveryAgent->delete();
        }

        return $this->show($storefrontOrderDeliveryAgent);
    }
}
