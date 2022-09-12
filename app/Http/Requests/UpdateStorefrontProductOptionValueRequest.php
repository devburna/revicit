<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontProductOptionValueRequest extends FormRequest
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
            'values' => 'array',
            'values.*.label' => 'string|max:50',
            'values.*.image' => 'mimes:jpg,jpeg,png|max:5000',
            'values.*.price' => 'numeric',
            'values.*.default' => 'boolean',
        ];
    }
}
