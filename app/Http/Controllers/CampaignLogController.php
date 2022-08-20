<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignLogRequest;
use App\Http\Requests\UpdateCampaignLogRequest;
use App\Models\CampaignLog;

class CampaignLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCampaignLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCampaignLogRequest $request)
    {
        CampaignLog::create($request->only(['campaign_id', 'sender_name', 'sender_email', 'sender_phone', 'recipient_name', 'recipient_email', 'recipient_phone', 'meta', 'message', 'status']));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CampaignLog  $campaignLog
     * @return \Illuminate\Http\Response
     */
    public function show(CampaignLog $campaignLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CampaignLog  $campaignLog
     * @return \Illuminate\Http\Response
     */
    public function edit(CampaignLog $campaignLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCampaignLogRequest  $request
     * @param  \App\Models\CampaignLog  $campaignLog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCampaignLogRequest $request, CampaignLog $campaignLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CampaignLog  $campaignLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(CampaignLog $campaignLog)
    {
        //
    }
}
