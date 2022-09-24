<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorefrontOrderReviewRequest;
use App\Models\StorefrontOrder;
use App\Models\StorefrontOrderReview;

class StorefrontOrderReviewController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontOrderReviewRequest  $request
     * @param  \App\Models\StorefrontOrder  $storefrontOrder
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStorefrontOrderReviewRequest $request, StorefrontOrder $storefrontOrder)
    {
        $request['storefront_order_id'] = $storefrontOrder->id;

        $storefrontOrderReview = StorefrontOrderReview::create($request->only([
            'rating',
            'comment'
        ]));

        return $this->show($storefrontOrderReview);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StorefrontOrderReview  $storefrontOrderReview
     * @return \Illuminate\Http\Response
     */
    public function show(StorefrontOrderReview $storefrontOrderReview, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $storefrontOrderReview,
            'message' => $message
        ], $code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StorefrontOrderReview  $storefrontOrderReview
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorefrontOrderReview $storefrontOrderReview)
    {
        if ($storefrontOrderReview->trashed()) {
            $storefrontOrderReview->restore();
        } else {
            $storefrontOrderReview->delete();
        }

        return $this->show($storefrontOrderReview);
    }
}
