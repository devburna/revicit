<?php

namespace App\Http\Requests;

use App\Enums\StorefrontCurrency;
use App\Enums\StorefrontFormFieldOption;
use App\Enums\StorefrontStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontRequest extends FormRequest
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
            'name' => 'string|unique:storefronts,name,' . $this->storefront->id,
            'tagline' => 'string|max:50',
            'domain' => 'string|unique:storefronts,domain,' . $this->storefront->id,
            'description' => 'string|max:500',
            'logo' => 'max:3000|dimension:512,512',
            'currency' => 'enum_value:' . StorefrontCurrency::class,
            'welcome_message' => 'string|max:255',
            'success_message' => 'string|max:255',
            'delivery_address' => 'enum_value:' . StorefrontFormFieldOption::class,
            'delivery_note' => 'enum_value:' . StorefrontFormFieldOption::class,
            'redirect_after_payment_url' => 'url',
            'status' => 'enum_value:' . StorefrontStatus::class,
        ];
    }
}
