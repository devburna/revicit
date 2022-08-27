<?php

namespace App\Http\Controllers;

use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Validation\ValidationException;

class CloudinaryController extends Controller
{
    // upload file
    public function upload($id, $media, $path)
    {
        if (filter_var($media, FILTER_VALIDATE_URL)) {
            $type = get_headers($media, true)['Content-Type'];
        } else {
            $type = mime_content_type($media->path());
            $media = $media->path();
        }

        try {
            return (new UploadApi())->upload($media, [
                'folder' => config('app.name') . "/{$path}/",
                'public_id' => $id,
                'overwrite' => true,
                'resource_type' => explode('/', $type)[0]
            ]);
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }
}
