<?php

namespace App\Http\Controllers;

use App\Enums\CampaignLogStatus;
use App\Enums\CampaignStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\StoreCampaignLogRequest;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Http\Requests\ViewCompanyRequest;
use App\Models\Campaign;
use App\Models\CompanyWallet;
use App\Models\Contact;
use App\Models\ServiceBasket;
use App\Notifications\Contact as NotificationsContact;
use App\Notifications\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

use function PHPSTORM_META\type;

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
                    if (!$request->company->socialNetwork["{$request->meta['social_network']['platform']}"]) {
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
                $campaign = $this->store($request);

                // don't send campaign if drafted or scheduled
                if ($campaign->status->is(CampaignStatus::SCHEDULED()) || $campaign->status->is(CampaignStatus::DRAFT())) {
                    return $this->show($campaign, 'success', 201);
                }

                // initiate campaign
                $campaign = $this->initiate($request, $campaign);

                return $this->show($campaign, 'success', 201);
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

            // draft
            if ($campaign->status->is(CampaignStatus::DRAFT()) && !$request->draft) {

                // store campaign request instance
                $storeCampaignRequest = new StoreCampaignRequest(json_decode(json_encode($campaign->meta), true));

                // modified data
                $storeCampaignRequest['title'] = $request->title;
                $storeCampaignRequest['type'] = $request->type;
                $storeCampaignRequest['company'] = $request->company;

                // initiate campaign
                $campaign = $this->initiate($storeCampaignRequest, $campaign);
            }

            return $this->show($campaign, 'success', 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => $th->getMessage(),
                'status' => false,
            ]);
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
     */
    public function sendCampaign(StoreCampaignRequest $request, Campaign $campaign)
    {

        // decode meta data
        $request['meta'] = json_decode($request->meta);

        // set campaign id
        $request['campaign_id'] = $campaign->id;

        // save campaign  logs
        $campaignLogs = [];

        // set billing quantity
        $billingQuantity = 0;

        foreach ($request['meta']['contacts'] as $contact) {

            // get contact info
            $recipient = Contact::findOrFail($contact);
            $request['recipient_name'] = $recipient->name;
            $request['recipient_email'] = $recipient->email;
            $request['recipient_phone'] = $recipient->phone;

            try {
                // send campaign
                $response = $recipient->notify(new NotificationsContact($request->all()));

                // add response to request
                $request['response'] = json_encode($response);

                // increment success count on success
                $billingQuantity++;

                // store campaign log
                $request['meta'] = json_encode($request->all());
                $request['message'] = 'Delivered';
                $request['status'] = CampaignLogStatus::SENT();
                $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

                // add to campaign logs
                array_push($campaignLogs, $logs);
            } catch (\Throwable $th) {

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

        // charge company wallet (Service-B4-Pay)
        $this->serviceCharge($request->company->wallet, $request->service->price * $billingQuantity, "Campaign {$request->title}", $request->all(), false);

        // logs to campaign
        $campaign->logs = $campaignLogs;

        return $campaign;
    }

    /**
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     */
    public function socialNetwork(StoreCampaignRequest $request, Campaign $campaign)
    {
        // decode meta data
        $request['meta'] = json_decode($request->meta, true);

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

            // charge company wallet (Pay-B4-Service)
            $this->serviceCharge($request->company->wallet, $request->service->price, "Campaign {$request->title}", $request->all(), false);

            // send campaign
            $request['post'] = $request->meta['meta']['social_network']['post'];
            $request['platform'] = $request->meta['meta']['social_network']['platform'];
            $request['media_urls'] = $mediaUrls;

            $response = (new AyrshareController())->post($request->all());

            // verify response
            if (!$response['status'] === 'success') {

                // refund company wallet (Pay-B4-Service)
                $this->serviceCharge($request->company->wallet, $request->service->price, "Refund Campaign {$request->title}", $request->all(), true);

                throw ValidationException::withMessages(['Error occured, kindly reach out to support ASAP!']);
            }

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

            // store campaign log
            $request['meta'] = json_encode($th);
            $request['message'] = $th->getMessage();
            $request['status'] = CampaignLogStatus::FAILED();
            $logs = (new CampaignLogController())->store(new StoreCampaignLogRequest($request->all()));

            // add to campaign logs
            array_push($campaignLogs, $logs);
        }

        // logs to campaign
        $campaign->logs = $campaignLogs;

        return $campaign;
    }

    private function serviceCharge(CompanyWallet $wallet, $amount, $narration, $meta, $refund = false)
    {
        match ($refund) {
            true =>  $wallet->credit($amount),
            default =>  $wallet->debit($amount),
        };

        // store payment
        $storePaymentRequest = new StorePaymentRequest();
        $storePaymentRequest['company_wallet_id'] = $wallet->id;
        $storePaymentRequest['identity'] = Str::random(24);
        $storePaymentRequest['amount'] = $amount;
        $storePaymentRequest['currency'] = 'NGN';
        $storePaymentRequest['narration'] = $narration;
        $storePaymentRequest['type'] = $refund ? PaymentType::CREDIT() : PaymentType::DEBIT();
        $storePaymentRequest['status'] = PaymentStatus::SUCCESSFUL();
        $storePaymentRequest['meta'] = json_encode($meta);
        $payment = (new PaymentController())->store($storePaymentRequest);

        $wallet->company->notify(new Payment($payment));
    }
}
