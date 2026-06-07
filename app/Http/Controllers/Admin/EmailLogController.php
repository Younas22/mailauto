<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(15)->withQueryString();

        $counts = [
            'total'  => CampaignLog::count(),
            'sent'   => CampaignLog::where('status', 'sent')->count(),
            'failed' => CampaignLog::where('status', 'failed')->count(),
        ];

        $campaigns = Campaign::orderBy('name')->get(['id', 'name']);

        return view('admin.email-logs.index', compact('logs', 'counts', 'campaigns'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = CampaignLog::with(['campaign:id,name', 'template:id,title'])->latest();

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status') && in_array($request->status, ['sent', 'failed'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $filename = 'email-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Campaign', 'Template', 'Status', 'Error', 'Sent At', 'Created At']);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $log) {
                    fputcsv($handle, [
                        $log->email,
                        $log->campaign?->name ?? '',
                        $log->template?->title ?? '',
                        $log->status,
                        $log->error_message ?? '',
                        $log->sent_at?->format('Y-m-d H:i:s') ?? '',
                        $log->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
