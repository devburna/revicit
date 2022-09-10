<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontProductImageRequest;
use App\Http\Requests\UpdateStorefrontProductImageRequest;
use App\Models\StorefrontProduct;
use App\Models\StorefrontProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StorefrontProductImageController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductImageRequest  $request
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Http\Response
     */
    public function create(StoreStorefrontProductImageRequest $request, StorefrontProduct $storefrontProduct)
    {
        $image = (new CloudinaryController())->upload(Str::random(24), $request->image, "{$request->storefront->domain}/products");

        $request['storefront_product_id'] = $storefrontProduct->id;
        $request['image_url']  = $image['secure_url'];
        $request['meta']  = json_encode($image);

        return $this->store($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductImageRequest  $request
     */
    public function store(StoreStorefrontProductImageRequest $request)
    {
        return StorefrontProductImage::create($request->only([
            'storefront_product_id',
            'name',
            'image_url',
            'meta'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontProductImage  $storefrontProductImage
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontProductImage $storefrontProductImage, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $storefrontProductImage,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontProductImageRequest  $request
     * @param  \App\Models\StorefrontProductImage  $storefrontProductImage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontProductImageRequest $request, StorefrontProductImage $storefrontProductImage)
    {
        $image = (new CloudinaryController())->upload($storefrontProductImage->meta->public_id, $request->image, "{$request->storefront->domain}/products");

        $request['image_url']  = $image['secure_url'];
        $request['meta']  = json_encode($image);

        // update
        $storefrontProductImage->update($request->only([
            'name',
            'image_url',
            'meta'
        ]));

        return $this->show($storefrontProductImage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontProductImage  $storefrontProductImage
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontProductImage $storefrontProductImage)
    {
        return DB::transaction(function () use ($storefrontProductImage) {
            $storefrontProductImage->delete();

            return $this->show($storefrontProductImage);
        });
    }
}
