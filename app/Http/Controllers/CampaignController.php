<?php

namespace App\Http\Controllers;

use App\Enums\CampaignLogStatus;
use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use App\Http\Requests\StoreCampaignLogRequest;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Http\Requests\ViewCompanyRequest;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ServiceBasket;
use App\Notifications\Contact as NotificationsContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ViewCompanyRequest $request)
    {
        $campaigns = $request->company->campaigns()->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'data' => $campaigns,
            'message' => 'success',
            'status' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function create(StoreCampaignRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                // set status
                if ($request->has('scheduled_for')) {
                    $request['status'] = CampaignStatus::SCHEDULED();
                } else if ($request->draft) {
                    $request['status'] = CampaignStatus::DRAFT();
                } else {
                    $request['status'] = CampaignStatus::PUBLISHED();
                }

                // json encode meta data
                $meta = $request->meta;
                $request['meta'] = json_encode($request->all());

                // upload media files if type social network
                if ($request->type === 'social-network') {
                    $mediaUrls = [];

                    // checks if user has profile key
                    if (!$request->company->socialNetwork) {
                        throw ValidationException::withMessages(['No social network has been linked to this account.']);
                    }

                    // set profile key
                    $request['profile'] = $request->company->socialNetwork->identity;

                    foreach ($meta['social_network']['medias'] as $media) {
                        // upload media
                        $upload = (new CloudinaryController())->upload(time(), $media, 'campaigns');

                        array_push($mediaUrls, $upload);
                    }

                    // add media urls to request
                    $request['mediaUrls'] = $mediaUrls;
                }

                // store campaign
                $campaign = $this->store($request);

                // don't send campaign if drafted or scheduled
                if ($campaign->status->is(CampaignStatus::SCHEDULED()) || $campaign->status->is(CampaignStatus::DRAFT())) {
                    return $this->show($campaign, 'success', 201);
                }

                // set campaign data
                $request['campaign'] = $campaign;
                $request['campaign_id'] = $campaign->id;

                // set sender data
                $request['sender_name'] = $request->company->name;
                $request['sender_email'] = $request->company->email;
                $request['sender_phone'] = $request->company->phone;

                // set meta data
                $request['meta'] = $meta;

                // send campaign
                match ($campaign->type) {
                    'social-network' => $this->socialNetwork($request),
                    default => $this->sendCampaign($request)
                };

                return response()->json([
                    'data' => $campaign,
                    'message' => 'success',
                    'status' => true,
                ]);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => $th->getMessage(),
                'status' => false,
            ]);
        }
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
        foreach ($request['meta']['contacts'] as $contact) {

            // get contact info
            $recipient = Contact::findOrFail($contact);
            $request['recipient_name'] = $recipient->name;
            $request['recipient_email'] = $recipient->email;
            $request['recipient_phone'] = $recipient->phone;

            try {
                // send campaign
                $response = $recipient->notify(new NotificationsContact($request->all()));

                // store campaign log
                $request['meta'] = json_encode($response);
                $request['message'] = 'Delivered';
                $request['status'] = CampaignLogStatus::SENT();
                (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));
            } catch (\Throwable $th) {

                // store campaign log
                $request['meta'] = json_encode($th);
                $request['message'] = $th->getMessage();
                $request['status'] = CampaignLogStatus::FAILED();
                (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                continue;
            }
        }
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     */
    public function socialNetwork(StoreCampaignRequest $request)
    {
        // sort media urls
        $images = [];
        $videos = [];

        foreach ($request->mediaUrls as $media) {
            if ($media['resource_type'] === 'image') {
                array($images, $media['url']);
            }

            if ($media['resource_type'] === 'video') {
                array($videos, $media['url']);
            }
        }

        // set receipient data
        $request['recipient_name'] = $request->company->name;
        $request['recipient_email'] = $request->company->email;
        $request['recipient_phone'] = $request->company->phone;

        foreach ($request['meta']['social_network']['platforms'] as $platform) {
            try {
                $videoImages = ['telegram', 'instagram', 'linkedin', 'twitter'];
                $videosOnly = ['youtube', 'tiktok'];

                // get service info
                $service = ServiceBasket::where('code', strtolower($platform))->firstOrFail();

                // set mediaUrls
                if (in_array($platform, $videoImages)) {
                    $mediaUrl = array_merge($images, $videos);
                }

                if (in_array($platform, $videosOnly)) {
                    $mediaUrl = $videos;
                }

                // add platform to request data
                $request['platform'] = array($platform);

                // send campaign
                $response = (new AyrshareController())->userPost($request->profile, $request['meta']['social_network']['post'], $platform, $mediaUrl);

                // store campaign log
                $request['meta'] = json_encode($response);
                $request['message'] = 'Delivered';
                $request['status'] = CampaignLogStatus::SENT();
                (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));
            } catch (\Throwable $th) {

                $request['meta'] = json_encode($th);
                $request['message'] = $th->getMessage();
                $request['status'] = CampaignLogStatus::FAILED();

                // store campaign log
                (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                continue;
            }
        }
    }
}
