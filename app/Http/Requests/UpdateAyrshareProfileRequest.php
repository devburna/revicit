<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAyrshareProfileRequest extends FormRequest
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
            'company_id' => 'required|unique:ayrshare_profiles,company_id',
            'identity' => 'required|unique:ayrshare_profiles,identity',
            'key' => 'required|unique:ayrshare_profiles,key',
            'token' => 'required|unique:ayrshare_profiles,token',
        ];
    }
}
