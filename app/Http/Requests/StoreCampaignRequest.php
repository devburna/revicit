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
            'scheduled_for' => 'date|after:1 hour',
            'draft' => 'required|boolean',

            // mail and sms required meta data
            'meta.contacts' => 'required_if:type,' . CampaignType::MAIL() . 'required_if:type,' . CampaignType::SMS() . '|array|max:50',
            'meta.contacts.*' => 'required|exists:contacts,id',

            // mail campaign required meta data
            'meta.mail.subject' => 'required_if:type,' . CampaignType::MAIL() . '|string|max:50',
            'meta.mail.template' => 'required_if:type,' . CampaignType::MAIL(),
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
            'meta.mail.subject.required_if' => 'The subject is required.',
            'meta.mail.template.required_if' => 'The subject is required.',
            'meta.contacts.*.exists' => "This contact is'nt registered."
        ];
    }
}
