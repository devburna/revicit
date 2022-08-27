<?php

namespace App\Http\Requests;

use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use BenSampo\Enum\Rules\EnumValue;

class StoreCampaignRequest extends FormRequest
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
            // global required data
            'title' => 'required|string',
            'type' => ['required', new EnumValue(CampaignType::class)],
            'template' => 'required_unless:type,' . CampaignType::SOCIAL_MEDIA() . '|string',
            'scheduled_for' => 'date|after:1 hour',
            'draft' => 'required|boolean',
            'meta.contacts' => 'required_unless:type,' . CampaignType::SOCIAL_MEDIA() . '|array',

            // mail campaign required meta data
            'meta.from.name' => 'required_if:type,' . CampaignType::MAIL() . '|required_if:type,' . CampaignType::MAIL_SMS() . '|string',
            'meta.from.email' => 'required_if:type,' . CampaignType::MAIL() . '|required_if:type,' . CampaignType::MAIL_SMS() . '|email',
            'meta.mail.subject' => 'required_if:type,' . CampaignType::MAIL() . '|required_if:type,' . CampaignType::MAIL_SMS() . '|string',

            // sms campaign required meta data
            'meta.from.name' => 'required_if:type,' . CampaignType::SMS() . '|string',
            'meta.from.phone' => 'required_if:type,' . CampaignType::SMS() . '|string',
            'meta.sms.content' => 'required_if:type,' . CampaignType::SMS() . '|string',


            // mail and sms campaign required meta data
            'meta.mail.subject' => 'required_if:type,' . CampaignType::MAIL_SMS() . '|string',
            'meta.sms.content' => 'required_if:type,' . CampaignType::MAIL_SMS() . '|string',

            // social media campaign required meta data
            'meta.social_media.content' => 'required_if:type,' . CampaignType::SMS() . '|string',
            'meta.social_media.platforms' => 'required_if:type,' . CampaignType::SOCIAL_MEDIA(), '|array',
            'meta.social_media.platforms.*' => ['required_if:type,' . CampaignType::SOCIAL_MEDIA(), '|exists:social_media_platforms,id'],
            'meta.social_media.video_urls' => 'required_if:type,' . CampaignType::SOCIAL_MEDIA() . '|array',
            'meta.social_media.video_urls.*' => 'required_if:type,' . CampaignType::SOCIAL_MEDIA() . '|url',
            'meta.social_media.image_urls' => 'required_if:type,' . CampaignType::SOCIAL_MEDIA() . '|array',
            'meta.social_media.image_urls.*' => 'required_if:type,' . CampaignType::SOCIAL_MEDIA() . '|url'
        ];
    }
}
