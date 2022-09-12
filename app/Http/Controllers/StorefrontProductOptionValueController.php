<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontProductOptionValueRequest;
use App\Http\Requests\UpdateStorefrontProductOptionValueRequest;
use App\Models\StorefrontProductOption;
use App\Models\StorefrontProductOptionValue;
use Illuminate\Support\Str;

class StorefrontProductOptionValueController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductOptionValueRequest  $request
     * @param  \App\Models\StorefrontProductOption  $storefrontProductOption
     * @return \Illuminate\Http\Response
     */
    public function create(StoreStorefrontProductOptionValueRequest $request, StorefrontProductOption $storefrontProductOption)
    {
        try {
            // upload images
            $values = [];
            foreach ($request->values as $value) {
                if (array_key_exists('image', $value)) {
                    $image_url = (new CloudinaryController())->upload(Str::random(24), $value['image'], "{$request->storefront->domain}/products/options/values");

                    $value['image'] = $image_url;
                }

                array_push($values, $value);
            }
            $request['values'] = $values;

            $storefrontProductOptionValues = [];

            foreach ($request->values as $value) {

                // create value
                $value['storefront_product_option_id'] = $storefrontProductOption->id;

                if (array_key_exists('image', $value)) {
                    $value['image_url'] = $value['image']['image_url'];
                };

                $storefrontProductOptionValue = $this->store(new StoreStorefrontProductOptionValueRequest($value));

                array_push($storefrontProductOptionValues, $storefrontProductOptionValue);
            }

            return response()->json([
                'status' => true,
                'data' => $storefrontProductOptionValues,
                'message' => 'success'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'data' => $th->getMessage(),
                'message' => 'error'
            ], 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductOptionValueRequest  $request
     */
    public function store(StoreStorefrontProductOptionValueRequest $request)
    {
        return StorefrontProductOptionValue::create($request->only([
            'storefront_product_option_id',
            'label',
            'image_url',
            'price',
            'default'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontProductOptionValue  $storefrontProductOptionValue
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontProductOptionValue $storefrontProductOptionValue, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $storefrontProductOptionValue,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontProductOptionValueRequest  $request
     * @param  \App\Models\StorefrontProductOptionValue  $storefrontProductOptionValue
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontProductOptionValueRequest $request, StorefrontProductOptionValue $storefrontProductOptionValue)
    {
        // update
        $storefrontProductOptionValue->update($request->only([
            'label',
            'image_url',
            'price',
            'default'
        ]));

        return $this->show($storefrontProductOptionValue);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontProductOptionValue  $storefrontProductOptionValue
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontProductOptionValue $storefrontProductOptionValue)
    {
        if ($storefrontProductOptionValue->trashed()) {
            $storefrontProductOptionValue->restore();
        } else {
            $storefrontProductOptionValue->delete();
        }

        return $this->show($storefrontProductOptionValue);
    }
}
