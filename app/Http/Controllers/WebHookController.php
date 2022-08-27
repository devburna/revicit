<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebHookRequest;
use App\Http\Requests\UpdateWebHookRequest;
use App\Models\WebHook;

class WebHookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $webHooks = WebHook::orderByDesc('created_at')->paginate(50);

        return response()->json([
            'data' => $webHooks,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreWebHookRequest  $request
     */
    public function store(StoreWebHookRequest $request)
    {
        WebHook::create($request->only([
            'origin',
            'status',
            'data',
            'message'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WebHook  $webHook
     * @return \Illuminate\Http\Response
     */
    public function show(WebHook $webHook)
    {
        return response()->json([
            'data' => $webHook,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WebHook  $webHook
     * @return \Illuminate\Http\Response
     */
    public function destroy(WebHook $webHook)
    {
        if ($webHook->trashed()) {
            $webHook->restore();
        } else {
            $webHook->delete();
        }

        return $this->show($webHook);
    }
}
