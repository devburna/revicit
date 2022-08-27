<?php

namespace App\Http\Controllers;

use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Validation\ValidationException;

class CloudinaryController extends Controller
{
    // upload file
    public function upload($id, $media, $path, $type)
    {
        try {
            $upload = (new UploadApi())->upload($media->path(), [
                'folder' => config('app.name') . "/{$path}/",
                'public_id' => $id,
                'overwrite' => true,
                'resource_type' => $type
            ]);

            return array($upload);
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }
}
