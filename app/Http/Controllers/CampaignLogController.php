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
        $campaignLogs = CampaignLog::paginate(20);

        return response()->json([
            'data' => $campaignLogs,
            'message' => 'success',
            'status' => true
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCampaignLogRequest  $request
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
    public function show(CampaignLog $campaignLog, $message = 'success', $code = 200)
    {
        // and related campaign to data
        $campaignLog->logs;

        return response()->json([
            'data' => $campaignLog,
            'message' => $message,
            'status' => true
        ], $code);
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
        return $this->show($campaignLog);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CampaignLog  $campaignLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(CampaignLog $campaignLog)
    {
        if ($campaignLog->trashed()) {
            $campaignLog->restore();
        } else {
            $campaignLog->delete();
        }

        return $this->show($campaignLog);
    }
}
