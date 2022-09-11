<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontCustomerRequest;
use App\Http\Requests\UpdateStorefrontCustomerRequest;
use App\Models\StorefrontCustomer;
use Illuminate\Http\Request;

class StorefrontCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storefrontCustomers = $request->storefront->customers()->withTrashed()->paginate(20);

        return response()->json([
            'data' => $storefrontCustomers,
            'message' => 'success',
            'status' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontCustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreStorefrontCustomerRequest $request)
    {
        // create customer
        $storefrontCustomer = $this->store($request);
        return $this->show($storefrontCustomer, 'message', 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontCustomerRequest  $request
     */
    public function store(StoreStorefrontCustomerRequest $request)
    {
        return StorefrontCustomer::create($request->only([
            'storefront_id',
            'first_name',
            'last_name',
            'email',
            'phone'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontCustomer  $storefrontCustomer
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontCustomer $storefrontCustomer, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $storefrontCustomer,
            'message' => $message
        ], $code);
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
        // update
        $storefrontCustomer->update($request->only([
            'first_name',
            'last_name',
            'email',
            'phone'
        ]));

        return $this->show($storefrontCustomer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontCustomer  $storefrontCustomer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontCustomer $storefrontCustomer)
    {
        if ($storefrontCustomer->trashed()) {
            $storefrontCustomer->restore();
        } else {
            $storefrontCustomer->delete();
        }

        return $this->show($storefrontCustomer);
    }
}
