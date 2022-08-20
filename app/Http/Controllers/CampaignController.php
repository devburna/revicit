<?php

namespace App\Http\Controllers;

use App\Enums\CampaignLogStatus;
use App\Enums\CampaignStatus;
use App\Http\Requests\StoreCampaignLogRequest;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Models\Company;
use App\Models\Contact;
use App\Notifications\Contact as NotificationsContact;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function index(Company $company)
    {
        return response()->json([
            'data' => $company->campaigns->sortBy('created_at'),
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Company  $company
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreCampaignRequest $request, Company $company)
    {
        // set company id
        $request['company_id'] = $company->id;

        // set status
        if ($request->has('scheduled_for')) {
            $request['status'] = CampaignStatus::SCHEDULED();
        } else if ($request->draft) {
            $request['status'] = CampaignStatus::DRAFT();
        } else {
            $request['status'] = CampaignStatus::PUBLISHED();
        }

        // set meta data
        $meta = $request->meta;

        // json encode request meta data
        $request['meta'] = json_encode($meta);

        // store campaign
        $campaign = $this->store($request);

        // don't send campaign if drafted or scheduled
        if ($campaign->status->is(CampaignStatus::SCHEDULED()) || $campaign->status->is(CampaignStatus::DRAFT())) {
            return $this->show($campaign, 'success', 201);
        }

        // set campaign data
        $request['campaign'] = $campaign;

        // set request meta data
        $request['meta'] = $meta;

        // send campaign
        switch ($request->type) {
            case 'social-media':
                // send social media campaign via ayrshare.com
                (new AyrshareController())->post($request->meta['social_media']);
                break;

            default:
                // send email or sms campaign
                $this->sendCampaign($request);
                break;
        }

        // returns campaign details
        return $this->show($campaign, 'success', 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     */
    public function store(StoreCampaignRequest $request)
    {
        return Campaign::create($request->only([
            'company_id',
            'title',
            'type',
            'template',
            'scheduled_for',
            'meta',
            'status'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign, $message = 'success', $code = 200)
    {
        // and related campaign logs to data
        $campaign->logs;

        return response()->json([
            'data' => $campaign,
            'message' => $message,
            'status' => true,
        ], $code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCampaignRequest  $request
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        return $this->show($campaign, 'success', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campaign $campaign)
    {
        if ($campaign->trashed()) {
            $campaign->restore();
        } else {
            $campaign->delete();
        }

        return $this->show($campaign);
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     */
    public function sendCampaign(StoreCampaignRequest $request)
    {
        // save campaign data
        $campaign_request = $request->all();

        // new store campaign request
        $request = new StoreCampaignLogRequest();
        $request['campaign_id'] = $campaign_request['campaign']->id;

        // sender info
        $request['sender_name'] = $campaign_request['meta']['from']['name'];
        $request['sender_email'] = $campaign_request['meta']['from']['email'];
        $request['sender_phone'] = $campaign_request['meta']['from']['phone'];

        foreach ($campaign_request['meta']['contacts'] as $contact) {
            try {
                // find contact
                if (!$recipient = Contact::where('email', $contact['email'])->orWhere('phone', $contact['phone'])->first()) {
                    throw new ModelNotFoundException('Contact not registered.');
                };

                $request['recipient_name'] = $recipient->name;
                $request['recipient_email'] = $recipient->email;
                $request['recipient_phone'] = $recipient->phone;
                $request['message'] = trans('campaign.sent');
                $request['status'] = CampaignLogStatus::SENT();

                // send campaign
                $recipient->notify(new NotificationsContact($campaign_request['campaign']));

                // store campaign log
                (new CampaignLogController())->store($request);
            } catch (\Throwable $th) {
                $request['recipient_name'] = $contact['name'];
                $request['recipient_email'] = $contact['email'];
                $request['recipient_phone'] = $contact['phone'];
                $request['message'] = $th->getMessage();
                $request['status'] = CampaignLogStatus::FAILED();

                // store campaign log
                (new CampaignLogController())->store($request);
                continue;
            }
        }
    }
}
