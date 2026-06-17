<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCampaignChunkJob;
use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use App\Models\EmailGroup;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\TemplateCategory;
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
        $emailGroups        = EmailGroup::withCount(['emails', 'pendingEmails'])->get();
        $templateCount      = EmailTemplate::where('status', 'active')->count();
        $templates          = EmailTemplate::where('status', 'active')->get(['id', 'title', 'category']);
        $templateCategories = TemplateCategory::orderBy('name')->get(['id', 'name']);
        $randomRotation     = Setting::get('campaign_random_rotation', '0') === '1';
        $dailyLimit         = (int) Setting::get('campaign_daily_limit', 50);
        $delaySeconds       = $dailyLimit > 0 ? (int) floor(86400 / $dailyLimit) : 86400;
        $delayLabel         = $this->formatDelay($delaySeconds);
        $followup1          = null;
        $followup2          = null;

        return view('admin.campaigns.create', compact(
            'emailGroups', 'templateCount', 'templates', 'templateCategories',
            'randomRotation', 'dailyLimit', 'delaySeconds', 'delayLabel',
            'followup1', 'followup2'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $randomRotation = Setting::get('campaign_random_rotation', '0') === '1';

        $rules = [
            'name'                   => 'required|string|max:255',
            'email_group_id'         => 'required|exists:email_groups,id',
            'template_category_id'   => 'nullable|exists:template_categories,id',
            'followup1_enabled'      => 'nullable|boolean',
            'followup1_template_id'  => 'nullable|exists:email_templates,id',
            'followup1_days'         => 'nullable|integer|min:1',
            'followup2_enabled'      => 'nullable|boolean',
            'followup2_template_id'  => 'nullable|exists:email_templates,id',
            'followup2_days'         => 'nullable|integer|min:1',
        ];

        if (!$randomRotation) {
            $rules['template_id'] = 'required|exists:email_templates,id';
        }

        $data = $request->validate($rules);

        $group       = EmailGroup::findOrFail($data['email_group_id']);
        $totalEmails = $group->pendingEmails()->count();

        $campaign = Campaign::create([
            'name'                 => $data['name'],
            'email_group_id'       => $data['email_group_id'],
            'template_id'          => $randomRotation ? null : ($data['template_id'] ?? null),
            'template_category_id' => $data['template_category_id'] ?? null,
            'delay_minutes'        => 0,
            'status'               => 'draft',
            'total_emails'         => $totalEmails,
            'sent_count'           => 0,
            'failed_count'         => 0,
        ]);

        // Create follow-up records
        if (!empty($data['followup1_enabled'])) {
            $fu1 = $campaign->followups()->create([
                'sort_order'  => 1,
                'template_id' => $data['followup1_template_id'] ?? null,
                'delay_days'  => $data['followup1_days'] ?? 3,
                'status'      => 'pending',
            ]);

            if (!empty($data['followup2_enabled'])) {
                $campaign->followups()->create([
                    'sort_order'  => 2,
                    'template_id' => $data['followup2_template_id'] ?? null,
                    'delay_days'  => $data['followup2_days'] ?? 3,
                    'status'      => 'pending',
                ]);
            }
        }

        return redirect()
            ->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully. Ready to launch!');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load('emailGroup');
        $recentLogs   = $campaign->logs()->with('template')->latest()->limit(50)->get();
        $openedCount  = $campaign->logs()->where('open_count', '>', 0)->count();
        $repliedCount = $campaign->logs()->where('reply_count', '>', 0)->count();
        $followups    = $campaign->followups()->with('template')->orderBy('sort_order')->get();

        $templateStats = $campaign->logs()
            ->whereNotNull('email_template_id')
            ->selectRaw('email_template_id, COUNT(*) as total_sent, SUM(CASE WHEN open_count > 0 THEN 1 ELSE 0 END) as total_opens, SUM(CASE WHEN reply_count > 0 THEN 1 ELSE 0 END) as total_replies')
            ->groupBy('email_template_id')
            ->get()
            ->keyBy('email_template_id');

        $usedTemplates = EmailTemplate::whereIn('id', $templateStats->keys())->get()->keyBy('id');

        return view('admin.campaigns.show', compact('campaign', 'recentLogs', 'openedCount', 'repliedCount', 'followups', 'templateStats', 'usedTemplates'));
    }

    public function edit(Campaign $campaign): View
    {
        if (!in_array($campaign->status, ['draft', 'paused'])) {
            return redirect()->route('admin.campaigns.show', $campaign)
                ->with('error', 'Only draft or paused campaigns can be edited.');
        }

        $emailGroups        = EmailGroup::withCount(['emails', 'pendingEmails'])->get();
        $templates          = EmailTemplate::where('status', 'active')->get(['id', 'title', 'category']);
        $templateCategories = TemplateCategory::orderBy('name')->get(['id', 'name']);
        $randomRotation     = Setting::get('campaign_random_rotation', '0') === '1';
        $dailyLimit         = (int) Setting::get('campaign_daily_limit', 50);
        $delaySeconds       = $dailyLimit > 0 ? (int) floor(86400 / $dailyLimit) : 86400;
        $delayLabel         = $this->formatDelay($delaySeconds);
        $followup1          = $campaign->followups->where('sort_order', 1)->first();
        $followup2          = $campaign->followups->where('sort_order', 2)->first();

        return view('admin.campaigns.edit', compact(
            'campaign', 'emailGroups', 'templates', 'templateCategories',
            'randomRotation', 'dailyLimit', 'delaySeconds', 'delayLabel',
            'followup1', 'followup2'
        ));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        if (!in_array($campaign->status, ['draft', 'paused'])) {
            return back()->with('error', 'Only draft or paused campaigns can be edited.');
        }

        $randomRotation = Setting::get('campaign_random_rotation', '0') === '1';

        $rules = [
            'name'                   => 'required|string|max:255',
            'email_group_id'         => 'required|exists:email_groups,id',
            'template_category_id'   => 'nullable|exists:template_categories,id',
            'followup1_enabled'      => 'nullable|boolean',
            'followup1_template_id'  => 'nullable|exists:email_templates,id',
            'followup1_days'         => 'nullable|integer|min:1',
            'followup2_enabled'      => 'nullable|boolean',
            'followup2_template_id'  => 'nullable|exists:email_templates,id',
            'followup2_days'         => 'nullable|integer|min:1',
        ];

        if (!$randomRotation) {
            $rules['template_id'] = 'required|exists:email_templates,id';
        }

        $data = $request->validate($rules);

        $group = EmailGroup::findOrFail($data['email_group_id']);
        $data['total_emails']          = $group->pendingEmails()->count();
        $data['template_id']           = $randomRotation ? null : ($data['template_id'] ?? null);
        $data['template_category_id']  = $data['template_category_id'] ?? null;
        $data['delay_minutes']         = 0;

        $campaign->update([
            'name'                 => $data['name'],
            'email_group_id'       => $data['email_group_id'],
            'template_id'          => $data['template_id'],
            'template_category_id' => $data['template_category_id'],
            'delay_minutes'        => 0,
            'total_emails'         => $data['total_emails'],
        ]);

        // Remove only pending followups (don't touch running/completed)
        $campaign->followups()->where('status', 'pending')->delete();

        if (!empty($data['followup1_enabled'])) {
            $campaign->followups()->create([
                'sort_order'  => 1,
                'template_id' => $data['followup1_template_id'] ?? null,
                'delay_days'  => $data['followup1_days'] ?? 3,
                'status'      => 'pending',
            ]);

            if (!empty($data['followup2_enabled'])) {
                $campaign->followups()->create([
                    'sort_order'  => 2,
                    'template_id' => $data['followup2_template_id'] ?? null,
                    'delay_days'  => $data['followup2_days'] ?? 3,
                    'status'      => 'pending',
                ]);
            }
        }

        return redirect()
            ->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully.');
    }

    public function start(Campaign $campaign): RedirectResponse
    {
        if (!in_array($campaign->status, ['draft', 'paused', 'failed'])) {
            return back()->with('error', 'Campaign is already running or completed.');
        }

        // COUNT only — never load 50k rows into memory for a size check
        $pendingCount = EmailList::where('group_id', $campaign->email_group_id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount === 0) {
            return back()->with('error', 'No pending emails in this list. All may have already been sent.');
        }

        if (EmailTemplate::where('status', 'active')->doesntExist()) {
            return back()->with('error', 'No active templates found. Please create at least one active template first.');
        }

        $maxPerCampaign = (int) Setting::get('campaign_max_per_campaign', 10000);
        if ($pendingCount > $maxPerCampaign) {
            return back()->with('error', "This campaign has {$pendingCount} pending emails which exceeds the configured limit of {$maxPerCampaign}. Reduce the group size or increase the limit in Settings → Campaign.");
        }

        $delaySeconds = (int) Setting::get('campaign_delay', 1728);
        $delayLabel   = $this->formatDelay($delaySeconds);

        $campaign->update([
            'status'       => 'running',
            'total_emails' => $pendingCount,
            'started_at'   => $campaign->started_at ?? now(),
        ]);

        ProcessCampaignChunkJob::dispatch($campaign->id);

        return back()->with('success', "Campaign launched! {$pendingCount} emails will be queued with {$delayLabel} between each.");
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

    private function formatDelay(int $seconds): string
    {
        if ($seconds >= 3600) {
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            return $m > 0 ? "{$h}h {$m}m" : "{$h} hour" . ($h !== 1 ? 's' : '');
        }
        if ($seconds >= 60) {
            $m = intdiv($seconds, 60);
            return "{$m} minute" . ($m !== 1 ? 's' : '');
        }
        return "{$seconds} second" . ($seconds !== 1 ? 's' : '');
    }
}
