<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontProductImageRequest;
use App\Http\Requests\StoreStorefrontProductRequest;
use App\Http\Requests\UpdateStorefrontProductRequest;
use App\Models\StorefrontProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Normalizer\SlugNormalizer;
use Illuminate\Support\Str;

class StorefrontProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storefrontProducts = $request->storefront->products()->with(['images', 'options'])->withTrashed()->paginate(20);

        return response()->json([
            'data' => $storefrontProducts,
            'message' => 'success',
            'status' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreStorefrontProductRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                // upload images
                $images = [];
                foreach ($request->images as $image) {
                    $img = (new CloudinaryController())->upload(Str::random(24), $image['file'], "{$request->storefront->domain}/products");

                    $img['name'] = array_key_exists('name', $image) ? $image['name'] : null;
                    array_push($images, $img);
                }
                $request['images'] = $images;

                // set slug
                $request['slug'] = (new SlugNormalizer())->normalize($request->name);

                $storefrontProduct = $this->store($request);

                // store product images
                foreach ($request->images as $image) {
                    $img['storefront_product_id'] = $storefrontProduct->id;
                    $img['name'] = $image['name'];
                    $img['image_url']  = $image['secure_url'];
                    $img['meta']  = json_encode($image);

                    (new StorefrontProductImageController())->store(new StoreStorefrontProductImageRequest($img));
                }

                return $this->show($storefrontProduct, 'message', 201);
            });
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontProductRequest  $request
     */
    public function store(StoreStorefrontProductRequest $request)
    {
        return StorefrontProduct::create($request->only([
            'storefront_id',
            'name',
            'slug',
            'description',
            'tags',
            'regular_price',
            'sale_price',
            'quantity',
            'stock_keeping_unit',
            'stock_quantity',
            'item_unit',
            'type',
            'status',
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontProduct $storefrontProduct, $message = 'success', $code = 200)
    {
        // add product images to data
        $storefrontProduct->images;

        // add product options to data
        $storefrontProduct->options;

        return response()->json([
            'status' => true,
            'data' => $storefrontProduct,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontProductRequest  $request
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontProductRequest $request, StorefrontProduct $storefrontProduct)
    {
        // update slug
        if ($request->name) {
            $request['slug'] = (new SlugNormalizer())->normalize($request->name);
        }

        // update
        $storefrontProduct->update($request->only([
            'name',
            'slug',
            'description',
            'tags',
            'regular_price',
            'sale_price',
            'quantity',
            'stock_keeping_unit',
            'stock_quantity',
            'item_unit',
            'type',
            'status',
        ]));

        return $this->show($storefrontProduct);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontProduct  $storefrontProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontProduct $storefrontProduct)
    {
        if ($storefrontProduct->trashed()) {
            $storefrontProduct->restore();
        } else {
            $storefrontProduct->delete();
        }

        return $this->show($storefrontProduct);
    }
}
