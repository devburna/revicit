<?php

namespace App\Http\Requests;

use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use BenSampo\Enum\Rules\EnumValue;

class UpdateServiceBasketRequest extends FormRequest
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
            'name' => 'string|max:50',
            'description' => 'string|max:500',
            'category' => ['required', new EnumValue(CampaignType::class)],
            'price' => 'numeric',
            'currency' => 'in:ngn,usd',
            'price_capped_at' => 'numeric',
            'meta.network' => 'string|max:50|in:ayrshare,vonage,zoho',
        ];
    }
}
