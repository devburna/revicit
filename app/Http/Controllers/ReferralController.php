<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // and related referrals to data
        $referrals = $request->user()->referrals;

        // get related referrals details and add to data
        foreach ($referrals as $referral) {
            $referral->details;
        }

        return response()->json([
            'data' => $referrals,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Referral  $referral
     * @return \Illuminate\Http\Response
     */
    public function show(Referral $referral)
    {
        //
    }
}
