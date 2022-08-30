<?php

namespace App\Http\Controllers;

use App\Enums\CampaignLogStatus;
use App\Enums\CampaignStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\StoreCampaignLogRequest;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreSocialNetworkPostRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ServiceBasket;
use App\Notifications\Contact as NotificationsContact;
use App\Notifications\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
                if ($request->scheduled_for || $request->draft && $request->scheduled_for) {
                    $request['status'] = CampaignStatus::SCHEDULED();
                } else if ($request->draft) {
                    $request['status'] = CampaignStatus::DRAFT();
                } else {
                    $request['status'] = CampaignStatus::PUBLISHED();
                }

                // set sender data
                $request['sender_name'] = $request->company->name;
                $request['sender_email'] = $request->company->email;
                $request['sender_phone'] = $request->company->phone;

                // upload media files if type social network
                if ($request->type === "social-network") {

                    // add service to request
                    $request['service'] = ServiceBasket::where('code', $request->meta['social_network']['platform'])->firstOrFail();

                    $mediaUrls = [];

                    // checks if user has profile
                    if (!$request->company->socialNetwork) {
                        throw ValidationException::withMessages(['No social network has been linked to this account.']);
                    }

                    // set profile
                    $request['profile'] = $request->company->socialNetwork->identity;

                    // check if platform is connected
                    if ($request->meta['social_network']['platform'] !== 'whatsapp' && !$request->company->socialNetwork["{$request->meta['social_network']['platform']}"]) {
                        throw ValidationException::withMessages(["Please connect your {$request->meta['social_network']['platform']} account to use this feature."]);
                    }

                    foreach ($request->meta['social_network']['medias'] as $media) {
                        // upload media
                        $upload = (new CloudinaryController())->upload(time(), $media, 'campaigns');

                        array_push($mediaUrls, $upload);
                    }

                    // add media urls to request
                    $request['media_urls'] = $mediaUrls;
                } else {

                    // add service to request
                    $request['service'] = ServiceBasket::where('category', $request->type)->firstOrFail();
                }

                // check if company can fund campaign
                if ($request->company->wallet->current_balance <= $request->service->price) {
                    throw ValidationException::withMessages(['Insufficient fund, please fund your wallet and try again.']);
                }

                // store campaign
                $request['meta'] = json_encode($request->all());

                if (!$request->campaign) {
                    $campaign = $this->store($request);
                } else {
                    $request->campaign->update($request->all());
                    $campaign = Campaign::find($request->campaign->id);
                }

                // don't send campaign if drafted or scheduled
                if ($campaign->status->is(CampaignStatus::SCHEDULED()) || $campaign->status->is(CampaignStatus::DRAFT())) {
                    return $this->show($campaign, 'success', 201);
                }

                // initiate campaign
                $campaign = $this->initiate($request, $campaign);

                // charge company wallet
                if ($campaign->amount > 0) {
                    $this->serviceCharge($campaign);
                }

                return $this->show($campaign, 'success', 201);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => $th->getMessage(),
                'status' => false,
            ], 422);
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
        // add campaign logs to data
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
        try {
            // published
            if ($campaign->status->is(CampaignStatus::PUBLISHED())) {
                throw ValidationException::withMessages(['Campaign has already been published']);
            }

            // store campaign request instance
            $storeCampaignRequest = new StoreCampaignRequest(json_decode(json_encode($campaign->meta), true));

            //
            if ($request->has('scheduled_for')) {
                $storeCampaignRequest['status'] = CampaignStatus::SCHEDULED();
                $storeCampaignRequest['scheduled_for'] = $request->scheduled_for;
            }

            // modify data
            $storeCampaignRequest['title'] = $request->title;
            $storeCampaignRequest['type'] = $request->type;
            $storeCampaignRequest['draft'] = $request->draft;
            $storeCampaignRequest['company'] = $request->company;
            $storeCampaignRequest['campaign'] = $campaign;

            // send campain
            return $this->create($storeCampaignRequest);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => $th->getMessage(),
                'status' => false,
            ], 422);
        }
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
    public function initiate(StoreCampaignRequest $request, Campaign $campaign)
    {
        return match ($request->type) {
            'social-network' => $this->socialNetwork($request, $campaign),
            'mail' => $this->sendCampaign($request, $campaign),
            'sms' => $this->sendCampaign($request, $campaign),
            default => throw ValidationException::withMessages(['Error occured, kindly reach out to support ASAP!'])
        };
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     * @param  \App\Models\Campaign  $campaign
     */
    public function sendCampaign(StoreCampaignRequest $request, Campaign $campaign)
    {
        // decode meta data
        $request['meta'] = json_decode($request->meta, true);

        // set campaign id
        $request['campaign_id'] = $campaign->id;

        // save campaign  logs
        $campaignLogs = [];

        // status
        $success = 0;
        $failed = 0;

        foreach ($request->meta['meta']['contacts'] as $contact) {

            // find contact
            if (!$recipient = Contact::find($contact)) {
                throw ValidationException::withMessages(["Error occured, kindly reach out to support ASAP!"]);
            };


            // get contact info
            $recipient = Contact::find($contact);
            $request['recipient_name'] = $recipient->name;
            $request['recipient_email'] = $recipient->email;
            $request['recipient_phone'] = $recipient->phone;

            try {
                // send campaign
                $response = $recipient->notify(new NotificationsContact($request->all()));

                // add response to request
                $request['response'] = json_encode($response);

                // increment success count on success
                $success++;

                // store campaign log
                $request['meta'] = json_encode($request->all());
                $request['message'] = 'Delivered';
                $request['status'] = CampaignLogStatus::SENT();
                $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                // add to campaign logs
                array_push($campaignLogs, $logs);
            } catch (\Throwable $th) {

                // increment failed count on failed
                $failed++;

                // store campaign log
                $request['meta'] = json_encode($th);
                $request['message'] = $th->getMessage();
                $request['status'] = CampaignLogStatus::FAILED();
                $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                // add to campaign logs
                array_push($campaignLogs, $logs);

                continue;
            }
        }

        // set receipt
        $campaign->quantity = $success;
        $campaign->success = $success;
        $campaign->failed = $failed;
        $campaign->amount = $request->service->price * $success;
        $campaign->currency = 'NGN';

        // logs to campaign
        $campaign->logs = $campaignLogs;

        return $campaign;
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     * @param  \App\Models\Campaign  $campaign
     */
    public function socialNetwork(StoreCampaignRequest $request, Campaign $campaign)
    {
        // decode meta data
        $request['meta'] = json_decode($request->meta, true);

        // whatsapp
        if ($request->meta['meta']['social_network']['platform'] === 'whatsapp') {
            return $this->whatsapp($request, $campaign);
        }


        // set campaign id
        $request['campaign_id'] = $campaign->id;

        // set recipient data
        $request['recipient_name'] = $request->company->name;
        $request['recipient_email'] = $request->company->email;
        $request['recipient_phone'] = $request->company->phone;

        // video only platforms
        $videoOnly = ['youtube', 'tiktok'];

        // save campaign  logs
        $campaignLogs = [];

        // status
        $success = 0;
        $failed = 0;

        try {

            // sort video media urls
            if (in_array($request->meta['meta']['social_network']['platform'], $videoOnly)) {
                $mediaUrls = [];
                foreach ($request->media_urls as $media) {
                    if ($media['resource_type'] === 'video') {
                        array_push($mediaUrls, $media['secure_url']);
                    }
                }
            } else {
                // sort all media urls
                $mediaUrls = array_reduce($request->media_urls, function ($urls, $url) {
                    array_push($urls, $url['secure_url']);
                    return $urls;
                }, []);
            }

            // send campaign
            $request['post'] = $request->meta['meta']['social_network']['post'];
            $request['platform'] = $request->meta['meta']['social_network']['platform'];
            $request['media_urls'] = $mediaUrls;

            $response = (new AyrshareController())->post($request->all());

            // verify response
            if (!$response['status'] === 'success') {
                throw ValidationException::withMessages(['Error occured, kindly reach out to support ASAP!']);
            }

            // increment success count on success
            $success++;

            // add response to request
            $request['response'] = json_encode($response);

            // store campaign log
            $request['meta'] = json_encode($request->all());
            $request['message'] = 'Delivered';
            $request['status'] = CampaignLogStatus::SENT();
            $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

            // add to campaign logs
            array_push($campaignLogs, $logs);
        } catch (\Throwable $th) {

            // increment failed count on failed
            $failed++;

            // store campaign log
            $request['meta'] = json_encode($th);
            $request['message'] = $th->getMessage();
            $request['status'] = CampaignLogStatus::FAILED();
            $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

            // add to campaign logs
            array_push($campaignLogs, $logs);
        }

        // store post
        $storeSocialNetworkPostRequest = (new StoreSocialNetworkPostRequest($request->all()));
        $storeSocialNetworkPostRequest['identity'] = $response['posts'][0]['id'];
        $storeSocialNetworkPostRequest['reference'] = $response['posts'][0]['refId'];
        $storeSocialNetworkPostRequest['post'] = $response['posts'][0]['post'];
        $storeSocialNetworkPostRequest['platform'] = $response['posts'][0]['postIds'][0]['platform'];
        $storeSocialNetworkPostRequest['meta'] = json_encode($response);
        (new SocialNetworkPostController())->store($storeSocialNetworkPostRequest);

        // set receipt
        $campaign->quantity = $success;
        $campaign->success = $success;
        $campaign->failed = $failed;
        $campaign->amount = $request->service->price * $success;
        $campaign->currency = 'NGN';

        // logs to campaign
        $campaign->logs = $campaignLogs;

        return $campaign;
    }

    public function whatsapp(StoreCampaignRequest $request, Campaign $campaign)
    {
        // set campaign id
        $request['campaign_id'] = $campaign->id;

        // save campaign  logs
        $campaignLogs = [];

        // status
        $success = 0;
        $failed = 0;

        foreach ($request->meta['meta']['contacts'] as $contact) {

            // find contact
            if (!$recipient = Contact::find($contact)) {
                throw ValidationException::withMessages(["Error occured, kindly reach out to support ASAP!"]);
            };


            // get contact info
            $recipient = Contact::find($contact);
            $request['recipient_name'] = $recipient->name;
            $request['recipient_email'] = $recipient->email;
            $request['recipient_phone'] = $recipient->phone;

            try {
                // send campaign
                $response = (new MetaController())->whatsappMessage($request->meta['meta']['social_network']['post'], $request->recipient_phone);

                // add response to request
                $request['response'] = json_encode($response);

                // increment success count on success
                $success++;

                // store campaign log
                $request['meta'] = json_encode($request->all());
                $request['message'] = 'Delivered';
                $request['status'] = CampaignLogStatus::SENT();
                $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                // add to campaign logs
                array_push($campaignLogs, $logs);
            } catch (\Throwable $th) {

                // increment failed count on failed
                $failed++;

                // store campaign log
                $request['meta'] = json_encode($th);
                $request['message'] = $th->getMessage();
                $request['status'] = CampaignLogStatus::FAILED();
                $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                // add to campaign logs
                array_push($campaignLogs, $logs);

                continue;
            }
        }

        // set receipt
        $campaign->quantity = $success;
        $campaign->success = $success;
        $campaign->failed = $failed;
        $campaign->amount = $request->service->price * $success;
        $campaign->currency = 'NGN';

        // logs to campaign
        $campaign->logs = $campaignLogs;

        return $campaign;
    }

    /**
     *
     * @param  \App\Models\Campaign  $campaign
     */
    private function serviceCharge(Campaign $campaign)
    {
        // store payment
        $storePaymentRequest = new StorePaymentRequest();
        $storePaymentRequest['company_wallet_id'] = $campaign->company->wallet->id;
        $storePaymentRequest['identity'] = Str::random(24);
        $storePaymentRequest['amount'] = $campaign->amount;
        $storePaymentRequest['currency'] = 'NGN';
        $storePaymentRequest['narration'] = "Campaign {$campaign->title}";
        $storePaymentRequest['type'] = PaymentType::DEBIT();
        $storePaymentRequest['status'] = PaymentStatus::SUCCESSFUL();
        $storePaymentRequest['meta'] = json_encode($campaign);
        $campaign->invoice = (new PaymentController())->store($storePaymentRequest);

        // send invoice to company
        $campaign->company->notify(new Payment($campaign->invoice));

        // debit company wallet
        $campaign->company->wallet->debit($campaign->amount);

        // clean campaign data
        unset($campaign->success);
        unset($campaign->failed);
        unset($campaign->amount);
        unset($campaign->currency);
        unset($campaign->quantity);

        return $campaign;
    }
}
