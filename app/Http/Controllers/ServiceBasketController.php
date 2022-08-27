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
        $serviceBaskets = ServiceBasket::orderByDesc('name')->paginate(50);

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

        // encode meta data
        $request['meta'] = json_encode($request->meta);

        $serviceBasket = ServiceBasket::create($request->only([
            'name',
            'code',
            'description',
            'category',
            'network',
            'price',
            'price_capped_at',
            'meta'
        ]));

        return $this->store($serviceBasket);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceBasket  $serviceBasket
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceBasket $serviceBasket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceBasket  $serviceBasket
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceBasket $serviceBasket)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ServiceBasket  $serviceBasket
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceBasket $serviceBasket)
    {
        //
    }
}
