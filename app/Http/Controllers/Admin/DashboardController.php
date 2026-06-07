<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\EmailGroup;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Delivery totals ────────────────────────────────────────────────────
        $totalSent   = CampaignLog::where('status', 'sent')->count();
        $totalFailed = CampaignLog::where('status', 'failed')->count();
        $totalLogs   = $totalSent + $totalFailed;
        $deliveryRate = $totalLogs > 0 ? round($totalSent / $totalLogs * 100, 1) : 0.0;

        $thisMonthSent = CampaignLog::where('status', 'sent')
            ->where('sent_at', '>=', now()->startOfMonth())
            ->count();
        $lastMonthSent = CampaignLog::where('status', 'sent')
            ->whereBetween('sent_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->count();

        // ── Templates ──────────────────────────────────────────────────────────
        $totalTemplates  = EmailTemplate::count();
        $activeTemplates = EmailTemplate::where('status', 'active')->count();
        $newTemplates    = EmailTemplate::where('created_at', '>=', now()->subDays(7))->count();

        // ── Subscribers ────────────────────────────────────────────────────────
        $totalSubscribers   = EmailList::count();
        $totalGroups        = EmailGroup::count();
        $pendingCount       = EmailList::where('status', 'pending')->count();
        $newSubscribersWeek = EmailList::where('created_at', '>=', now()->subDays(7))->count();

        // ── Email volume chart — last 7 days ───────────────────────────────────
        $chartDays = collect(range(6, 0))->map(fn (int $n) => [
            'label' => now()->subDays($n)->format('D'),
            'sent'  => CampaignLog::where('status', 'sent')
                ->whereDate('sent_at', now()->subDays($n)->toDateString())
                ->count(),
        ]);

        $weekTotal     = $chartDays->sum('sent');
        $chartMax      = max(1, $chartDays->max('sent'));
        $lastWeekTotal = CampaignLog::where('status', 'sent')
            ->whereBetween('sent_at', [
                now()->subDays(14)->startOfDay(),
                now()->subDays(7)->endOfDay(),
            ])
            ->count();

        // ── Campaign status breakdown ──────────────────────────────────────────
        $campaignCounts = Campaign::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');
        $totalCampaigns = Campaign::count();

        // ── Recent email log activity ──────────────────────────────────────────
        $recentLogs = CampaignLog::with(['campaign:id,name', 'template:id,title'])
            ->latest()
            ->limit(8)
            ->get();

        // ── Top email groups by subscriber count ───────────────────────────────
        $topGroups     = EmailGroup::withCount('emails')
            ->orderByDesc('emails_count')
            ->limit(4)
            ->get();
        $maxGroupCount = max(1, $topGroups->max('emails_count') ?? 1);

        return view('admin.dashboard', compact(
            'totalSent', 'totalFailed', 'totalLogs', 'deliveryRate',
            'thisMonthSent', 'lastMonthSent',
            'totalTemplates', 'activeTemplates', 'newTemplates',
            'totalSubscribers', 'totalGroups', 'pendingCount', 'newSubscribersWeek',
            'chartDays', 'weekTotal', 'chartMax', 'lastWeekTotal',
            'campaignCounts', 'totalCampaigns',
            'recentLogs',
            'topGroups', 'maxGroupCount'
        ));
    }
}
