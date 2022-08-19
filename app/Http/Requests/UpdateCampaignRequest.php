<?php

namespace App\Http\Requests;

use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use BenSampo\Enum\Rules\EnumValue;

class UpdateCampaignRequest extends FormRequest
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
            'title' => 'string',
            'type' => ['', new EnumValue(CampaignType::class)],
            'template' => 'string',
            'scheduled_for' => 'date',
            'meat' => 'string'
        ];
    }
}
