<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = CampaignLog::with(['campaign', 'template'])->latest();

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['sent', 'failed'])) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(15)->withQueryString();

        $counts = [
            'total'  => CampaignLog::count(),
            'sent'   => CampaignLog::where('status', 'sent')->count(),
            'failed' => CampaignLog::where('status', 'failed')->count(),
        ];

        return view('admin.email-logs.index', compact('logs', 'counts'));
    }
}
