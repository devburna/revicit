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
use App\Notifications\Sms;
use App\Notifications\SocialPost;
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
            'data' => $company->campaigns,
            'message' => 'success',
            'status' => true,
        ]);
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
     * @param  \App\Models\Company  $company
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCampaignRequest $request, Company $company)
    {
        // set company id
        $request['company_id'] = $company->id;

        // set status
        $request['status'] = CampaignStatus::PUBLISHED();

        if ($request->scheduled_for) {
            // validate scheduled_for
            $request->validate([
                'scheduled_for' => 'date'
            ]);

            // set status
            $request['status'] = CampaignStatus::SCHEDULED();
        }

        if ($request->meta['draft']) {
            // set status
            $request['status'] = CampaignStatus::DRAFT();
        }

        // json encode meta data
        $meta = $request->meta;
        $request['meta'] = json_encode($meta);

        // store campaign
        $campaign = Campaign::create($request->only([
            'company_id',
            'title',
            'type',
            'template',
            'scheduled_for',
            'meta',
            'status'
        ]));

        // store and not send campaign if drafted or scheduled
        if ($request->status->is(CampaignStatus::SCHEDULED() || CampaignStatus::DRAFT())) {
            return $this->show($campaign, 'success', 201);
        }

        // set campaign data
        $request['campaign'] = $campaign;
        $request['meta'] = $meta;

        // send campaign
        match ($request->type) {
            'email' => $this->email($request),
            'sms' => $this->sms($request),
            'social-post' => $this->socialPost($request),
        };

        return $this->show($campaign, 'success', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign, $message = 'success', $code = 200)
    {
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
    public function email(StoreCampaignRequest $request)
    {
        $campaign = $request->all();

        $request = new StoreCampaignLogRequest();
        $request['campaign_id'] = $campaign['campaign']->id;
        $request['sender_email'] = $campaign['meta']['from_email'];
        $request['sender_name'] = $campaign['meta']['from_name'];

        foreach ($campaign['contacts'] as $contact) {
            try {
                // find contact
                if (!$recipient = Contact::where('email', $contact['email'])->first()) {
                    throw new ModelNotFoundException('Contact not registered.');
                };
                $request['recipient_name'] = $recipient->name;
                $request['recipient_email'] = $recipient->email;
                $request['message'] = 'Email sent.';
                $request['status'] = CampaignLogStatus::SENT();

                // send email campaign
                $recipient->notify(new NotificationsContact($campaign['campaign']));

                // store campaign log
                (new CampaignLogController())->store($request);
            } catch (\Throwable $th) {
                $request['recipient_name'] = $contact['name'];
                $request['recipient_email'] = $contact['email'];
                $request['message'] = $th->getMessage();
                $request['status'] = CampaignLogStatus::FAILED();

                // store campaign log
                (new CampaignLogController())->store($request);
                continue;
            }
        }
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     */
    public function sms(StoreCampaignRequest $request)
    {
        foreach ($request->contacts as $contact) {
            //
        }
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     */
    public function socialPost(StoreCampaignRequest $request)
    {
        foreach ($request->contacts as $contact) {
            //
        }
    }
}
