<?php

namespace App\Http\Controllers;

use App\Models\CampaignLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackingController extends Controller
{
    // 1x1 transparent GIF
    private const PIXEL = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function open(string $token): Response
    {
        CampaignLog::where('tracking_token', $token)->increment('open_count');

        return response(base64_decode(self::PIXEL), 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    public function click(string $token, Request $request): RedirectResponse
    {
        $url = $request->query('url', '');

        if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
            CampaignLog::where('tracking_token', $token)->increment('click_count');
            return redirect()->away($url);
        }

        return redirect('/');
    }
}
