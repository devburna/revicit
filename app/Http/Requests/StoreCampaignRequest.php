<?php

namespace App\Http\Requests;

use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;

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
            'type' => ['required', 'exists:service_baskets,category'],
            'scheduled_for' => 'date_format:Y-m-d H:i|after:1 minute',
            'draft' => 'required_without:scheduled_for|boolean',

            // mail and sms required meta data
            'meta.contacts' => 'required_unless:type,' . CampaignType::SOCIAL_NETWORK() . '|array',
            'meta.contacts.*' => 'required_unless:type,' . CampaignType::SOCIAL_NETWORK() . '|distinct',

            // mail campaign required meta data
            'meta.mail.subject' => 'required_if:type,' . CampaignType::MAIL() . '|string|max:50',
            'meta.mail.template' => 'required_if:type,' . CampaignType::MAIL(),

            // sms campaign required meta data
            'meta.sms.content' => 'required_if:type,' . CampaignType::SMS() . '|string|max:255',

            // social network required meta data
            'meta.social_network.post' => 'required_if:type,' . CampaignType::SOCIAL_NETWORK() . '|string|max:50',
            'meta.social_network.platform' => 'required_if:type,' . CampaignType::SOCIAL_NETWORK() . '|exists:service_baskets,code',
            'meta.social_network.medias' => 'required_if:type,' . CampaignType::SOCIAL_NETWORK() . '|array',
            'meta.social_network.medias.*' => 'required_if:type,' . CampaignType::SOCIAL_NETWORK() . 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'draft.required_without' => 'Please specify if campaign should be drafted.',
            'type.exists' => 'We currently do not offer this service at the moment.',
            'meta.mail.subject.required_if' => 'The mail subject is required.',
            'meta.mail.template.required_if' => 'The mail template is required.',
            'meta.contacts.*.required_unless' => 'Contact list cannot be empty.',
            'meta.contacts.*.exists' => "This contact is'nt registered.",
            'meta.contacts.*.distinct' => 'Contacts has a duplicate value.',
            'meta.sms.content.required_if' => 'The sms content is required.',
            'meta.social_network.platform.exists' => 'We currently do not offer this service at the moment, kindly contact support for additional information.',
        ];
    }
}
