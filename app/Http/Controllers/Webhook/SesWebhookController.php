<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Webhook\SesWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SesWebhookController extends Controller
{
    public function __construct(private SesWebhookService $service) {}

    public function handle(Request $request): Response
    {
        // SNS sends content-type application/x-www-form-urlencoded for confirmations
        // but the body is always JSON. Force JSON parsing.
        $messageType = $request->header('x-amz-sns-message-type');

        if (!$messageType) {
            Log::warning('[SES Webhook] Missing x-amz-sns-message-type header.');
            return response('Bad Request', 400);
        }

        try {
            $this->service->handle($request);
            return response('OK', 200);
        } catch (\RuntimeException $e) {
            Log::error('[SES Webhook] Processing failed: ' . $e->getMessage());
            return response('Bad Request', 400);
        } catch (\Throwable $e) {
            Log::error('[SES Webhook] Unexpected error: ' . $e->getMessage());
            return response('Internal Server Error', 500);
        }
    }
}
