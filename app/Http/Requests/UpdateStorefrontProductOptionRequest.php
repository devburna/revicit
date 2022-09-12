<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontProductOptionRequest extends FormRequest
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
            'label' => 'string|max:50',
            'description' => 'string|max:50',
            'type' => 'string|max:50',
            'min' => 'numeric',
            'max' => 'numeric',
            'required' => 'boolean',
        ];
    }
}
