<?php

namespace App\Http\Requests;

use App\Enums\CampaignLogStatus;
use Illuminate\Foundation\Http\FormRequest;
use BenSampo\Enum\Rules\EnumValue;

class StoreCampaignLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'campaign_id' => 'required|exists:campaigns,id',
            'type' => 'required',
            'sender_name' => 'required',
            'sender_email' => 'required_if:type,email|email',
            'sender_phone' => 'required_if:type,sms|string',
            'recipient_name' => 'required',
            'recipient_email' => 'required_if:type,email|email',
            'recipient_phone' => 'required_if:type,sms|string',
            'meta' => 'string',
            'message' => 'required|string',
            'status' => ['required', new EnumValue(CampaignLogStatus::class)],
        ];
    }
}
