<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialMediaPlatformRequest extends FormRequest
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
            'name' => 'string|unique:social_media_platforms,name,' . $this->socialMediaPlatform->id,
            'slug' => 'string|unique:social_media_platforms,slug,' . $this->socialMediaPlatform->id,
            'video' => 'boolean',
            'image' => 'boolean',
            'reels' => 'boolean'
        ];
    }
}
