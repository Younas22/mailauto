<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Webhook\ResendWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ResendWebhookController extends Controller
{
    public function __construct(private ResendWebhookService $service) {}

    public function handle(Request $request): Response
    {
        try {
            $this->service->handle($request);
            return response('OK', 200);
        } catch (\RuntimeException $e) {
            Log::error('[Resend Webhook] Processing failed: ' . $e->getMessage());
            return response('Bad Request', 400);
        } catch (\Throwable $e) {
            Log::error('[Resend Webhook] Unexpected error: ' . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }
}
