<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MetaController extends Controller
{
    public $metaUrl, $metaKey;

    public function __construct()
    {
        $this->metaUrl = env('META_URL');
        $this->metaKey = env('META_KEY');
    }

    public function whatsappMessage($message, $to)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->metaKey}"
            ])->post("{$this->metaUrl}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ],
            ]);

            // catch error
            if (!$response->ok()) {
                throw ValidationException::withMessages(['Error occured, kindly contact support for more information']);
            }

            return $response->json();
        } catch (\Throwable $th) {
            throw ValidationException::withMessages([$th->getMessage()]);
        }
    }
}
