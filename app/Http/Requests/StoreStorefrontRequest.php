<?php

namespace App\Http\Requests;

use App\Enums\StorefrontCurrency;
use Illuminate\Foundation\Http\FormRequest;

class StoreStorefrontRequest extends FormRequest
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
            'name' => 'required|string|unique:storefronts,name',
            'domain' => 'required|string|unique:storefronts,domain',
            'currency' => 'required|enum_value:' . StorefrontCurrency::class,
            'description' => 'string|max:500',
        ];
    }
}
