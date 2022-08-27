<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceBasketRequest;
use App\Http\Requests\UpdateServiceBasketRequest;
use App\Models\ServiceBasket;
use League\CommonMark\Normalizer\SlugNormalizer;

class ServiceBasketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $serviceBaskets = ServiceBasket::withTrashed()->orderByDesc('name')->paginate(50);

        return response()->json([
            'data' => $serviceBaskets,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreServiceBasketRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceBasketRequest $request)
    {
        // set code
        $request['code'] = (new SlugNormalizer())->normalize($request->name);

        // validate code
        $request->validate([
            'code' => 'required|unique:service_baskets,code'
        ]);

        // set network
        $request['network'] = $request->meta['network'];

        // encode meta data
        $request['meta'] = json_encode($request->meta);

        $serviceBasket = ServiceBasket::create($request->only([
            'name',
            'code',
            'description',
            'category',
            'price',
            'price_capped_at',
            'currency',
            'meta'
        ]));

        return $this->show($serviceBasket, 'success', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceBasket  $serviceBasket
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceBasket $serviceBasket, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $serviceBasket,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServiceBasketRequest  $request
     * @param  \App\Models\ServiceBasket  $serviceBasket
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceBasketRequest $request, ServiceBasket $serviceBasket)
    {
        // update code
        if ($request->has('name')) {
            $request['code'] = (new SlugNormalizer())->normalize($request->name);

            // validate code
            $request->validate([
                'code' => 'unique:service_baskets,code,' . $serviceBasket->id
            ]);
        }

        // update meta
        if ($request->has('meta')) {

            // encode meta data
            $request['meta'] = json_encode($request->meta);
        }

        // update
        $serviceBasket->update($request->only([
            'name',
            'code',
            'description',
            'category',
            'price',
            'price_capped_at',
            'currency',
            'meta'
        ]));

        return $this->show($serviceBasket);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceBasket  $serviceBasket
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceBasket $serviceBasket)
    {
        if ($serviceBasket->trashed()) {
            $serviceBasket->restore();
        } else {
            $serviceBasket->delete();
        }

        return $this->show($serviceBasket);
    }
}
