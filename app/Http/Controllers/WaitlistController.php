<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWaitlistRequest;
use App\Models\Waitlist;

class WaitlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $waitlist = Waitlist::paginate(20);

        return response()->json([
            'data' => $waitlist,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreWaitlistRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWaitlistRequest $request)
    {
        $waitlist = Waitlist::create($request->only(['email']));

        return response()->json([
            'data' => $waitlist,
            'message' => 'success',
            'status' => true,
        ], 201);
    }
}
