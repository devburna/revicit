<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
            'name' => 'required|string|unique:companies,name',
            'address' => 'required|string',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'required|string|unique:companies,phone',
            'website' => 'required|url',
            'description'  => 'required|string',
        ];
    }
}
