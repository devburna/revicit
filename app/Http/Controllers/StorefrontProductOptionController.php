<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontProductOptionRequest;
use App\Http\Requests\UpdateStorefrontProductOptionRequest;
use App\Models\StorefrontProduct;
use App\Models\StorefrontProductOption;

class StorefrontProductOptionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductOptionRequest  $request
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStorefrontProductOptionRequest $request, StorefrontProduct $storefrontProduct)
    {
        // set product id
        $request['storefront_product_id'] = $storefrontProduct->id;

        // create option
        $storefrontProductOption = StorefrontProductOption::create($request->only([
            'storefront_product_id',
            'label',
            'description',
            'type',
            'min',
            'max',
            'required'
        ]));

        return $this->show($storefrontProductOption);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontProductOption  $storefrontProductOption
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontProductOption $storefrontProductOption, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $storefrontProductOption,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontProductOptionRequest  $request
     * @param  \App\Models\StorefrontProductOption  $storefrontProductOption
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontProductOptionRequest $request, StorefrontProductOption $storefrontProductOption)
    {
        // update
        $storefrontProductOption->update($request->only([
            'label',
            'description',
            'type',
            'min',
            'max',
            'required'
        ]));

        return $this->show($storefrontProductOption);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontProductOption  $storefrontProductOption
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontProductOption $storefrontProductOption)
    {
        if ($storefrontProductOption->trashed()) {
            $storefrontProductOption->restore();
        } else {
            $storefrontProductOption->delete();
        }

        return $this->show($storefrontProductOption);
    }
}
