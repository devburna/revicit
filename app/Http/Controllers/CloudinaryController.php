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
            $response = (new UploadApi())->upload($media, [
                'folder' => config('app.name') . "/{$path}/",
                'public_id' => $id,
                'overwrite' => true,
                'resource_type' => explode('/', $type)[0]
            ]);

            return [
                'secure_url' => $response['secure_url'],
                'resource_type' => $response['resource_type'],
                'public_id' => $response['public_id'],
            ];
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }

    // delete
    public function delete($public_id, $resource_type)
    {
        try {
            $upload = (new UploadApi())->destroy($public_id, $resource_type);

            return $upload;
        } catch (\Throwable $th) {
            abort(422, $th->getMessage());
        }
    }
}
