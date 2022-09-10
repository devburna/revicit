<?php

namespace App\Http\Requests;

use App\Enums\StorefrontProductQuantity;
use App\Enums\StorefrontProductStatus;
use App\Enums\StorefrontProductType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontProductRequest extends FormRequest
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
            'description' => 'string|max:255',
            'tags' => 'string',
            'regular_price' => 'numeric',
            'sale_price' => 'numeric|lt:regular_price',
            'quantity' => 'enum_value:' . StorefrontProductQuantity::class,
            'stock_keeping_unit' => 'numeric',
            'stock_quantity' => 'numeric|required_if:quantity,' . StorefrontProductQuantity::LIMITED(),
            'item_unit' => 'string',
            'type' => 'enum_value:' . StorefrontProductType::class,
            'status' => 'enum_value:' . StorefrontProductStatus::class,
        ];
    }
}
