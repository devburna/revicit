<?php

namespace App\Http\Controllers;

use App\Enums\StorefrontStatus;
use App\Http\Requests\StoreStorefrontRequest;
use App\Http\Requests\UpdateStorefrontRequest;
use App\Models\Storefront;
use Illuminate\Http\Request;
use Cloudinary\Api\Upload\UploadApi;

class StorefrontController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storefront = $request->company->storefront;

        return response()->json([
            'data' => $storefront,
            'message' => 'success',
            'status' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStorefrontRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStorefrontRequest $request)
    {
        // set status
        $request['status'] = StorefrontStatus::OPEN();

        $storefront = Storefront::create($request->only([
            'company_id',
            'name',
            'domain',
            'description',
            'currency',
            'status'
        ]));

        return $this->show($storefront, 'message', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Storefront  $storefront
     * @return \Illuminate\Http\Response
     */
    public function show(Storefront $storefront, $message = 'success', $code = 200)
    {
        // set site url
        $storefront->site_url = $storefront->domain;

        return response()->json([
            'status' => true,
            'data' => $storefront,
            'message' => $message
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStorefrontRequest  $request
     * @param  \App\Models\Storefront  $storefront
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStorefrontRequest $request, Storefront $storefront)
    {
        // upload to cloudinary
        if ($request->has('image')) {
            $request['logo_url'] = (new UploadApi())->upload($request->image->path(), [
                'folder' => config('app.name') . '/storefronts/',
                'public_id' => $storefront->id,
                'overwrite' => true,
                'resource_type' => 'image'
            ])['secure_url'];
        }

        // update
        $storefront->update($request->only([
            'name',
            'tagline',
            'domain',
            'description',
            'logo_url',
            'currency',
            'welcome_message',
            'success_message',
            'delivery_address',
            'delivery_note',
            'redirect_after_payment_url',
            'status'
        ]));

        return $this->show($storefront);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Storefront  $storefront
     * @return \Illuminate\Http\Response
     */
    public function destroy(Storefront $storefront)
    {
        if ($storefront->trashed()) {
            $storefront->restore();
        } else {
            $storefront->delete();
        }

        return $this->show($storefront);
    }
}
