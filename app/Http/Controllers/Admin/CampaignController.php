<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use App\Models\EmailGroup;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::with('emailGroup')->latest()->paginate(12);

        $counts = [
            'total'     => Campaign::count(),
            'running'   => Campaign::where('status', 'running')->count(),
            'completed' => Campaign::where('status', 'completed')->count(),
            'draft'     => Campaign::where('status', 'draft')->count(),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'counts'));
    }

    public function create(): View
    {
        $emailGroups    = EmailGroup::withCount(['emails', 'pendingEmails'])->get();
        $templateCount  = EmailTemplate::where('status', 'active')->count();

        return view('admin.campaigns.create', compact('emailGroups', 'templateCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email_group_id'  => 'required|exists:email_groups,id',
            'delay_minutes'   => 'required|integer|min:0|max:1440',
        ]);

        $group       = EmailGroup::findOrFail($data['email_group_id']);
        $totalEmails = $group->pendingEmails()->count();

        $campaign = Campaign::create([
            'name'           => $data['name'],
            'email_group_id' => $data['email_group_id'],
            'delay_minutes'  => $data['delay_minutes'],
            'status'         => 'draft',
            'total_emails'   => $totalEmails,
            'sent_count'     => 0,
            'failed_count'   => 0,
        ]);

        return redirect()
            ->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully. Ready to launch!');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load('emailGroup');
        $recentLogs = $campaign->logs()->with('template')->latest()->limit(20)->get();

        return view('admin.campaigns.show', compact('campaign', 'recentLogs'));
    }

    public function start(Campaign $campaign): RedirectResponse
    {
        if (!in_array($campaign->status, ['draft', 'paused', 'failed'])) {
            return back()->with('error', 'Campaign is already running or completed.');
        }

        $pendingEmails = EmailList::where('group_id', $campaign->email_group_id)
            ->where('status', 'pending')
            ->get();

        if ($pendingEmails->isEmpty()) {
            return back()->with('error', 'No pending emails in this list. All may have already been sent.');
        }

        if (EmailTemplate::where('status', 'active')->doesntExist()) {
            return back()->with('error', 'No active templates found. Please create at least one active template first.');
        }

        $campaign->update([
            'status'       => 'running',
            'total_emails' => $pendingEmails->count(),
            'started_at'   => $campaign->started_at ?? now(),
        ]);

        $delay = (int) $campaign->delay_minutes;

        foreach ($pendingEmails as $index => $emailItem) {
            SendCampaignEmailJob::dispatch($campaign->id, $emailItem->id)
                ->delay(now()->addMinutes($index * $delay));
        }

        return back()->with('success', "Campaign launched! {$pendingEmails->count()} emails queued with {$delay} min delay between each.");
    }

    public function pause(Campaign $campaign): RedirectResponse
    {
        if ($campaign->status === 'running') {
            $campaign->update(['status' => 'paused']);
            return back()->with('success', 'Campaign paused. Queued jobs will wait before processing.');
        }

        return back()->with('error', 'Campaign is not currently running.');
    }

    public function resume(Campaign $campaign): RedirectResponse
    {
        if ($campaign->status === 'paused') {
            $campaign->update(['status' => 'running']);
            return back()->with('success', 'Campaign resumed.');
        }

        return back()->with('error', 'Campaign is not paused.');
    }

    public function progress(Campaign $campaign): JsonResponse
    {
        $campaign->refresh();
        $processed  = $campaign->sent_count + $campaign->failed_count;
        $percentage = $campaign->total_emails > 0
            ? (int) round($processed / $campaign->total_emails * 100)
            : 0;

        return response()->json([
            'status'      => $campaign->status,
            'total'       => $campaign->total_emails,
            'sent'        => $campaign->sent_count,
            'failed'      => $campaign->failed_count,
            'processed'   => $processed,
            'remaining'   => max(0, $campaign->total_emails - $processed),
            'percentage'  => $percentage,
            'success_rate'=> $processed > 0 ? (int) round($campaign->sent_count / $processed * 100) : 0,
        ]);
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()
            ->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted.');
    }
}
