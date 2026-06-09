<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailGroup;
use App\Models\EmailTemplate;
use App\Models\WarmupLog;
use App\Models\WarmupPlan;
use App\Services\WarmupScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarmupController extends Controller
{
    public function __construct(private WarmupScheduleService $scheduler) {}

    public function index(): View
    {
        $plans = WarmupPlan::withCount('logs')
            ->orderByDesc('created_at')
            ->get()
            ->each(fn ($p) => $p->append([])); // trigger computed attrs if needed

        return view('admin.deliverability.warmup.index', compact('plans'));
    }

    public function create(): View
    {
        $groups    = EmailGroup::orderBy('name')->get();
        $templates = EmailTemplate::where('status', 'active')->orderBy('title')->get();
        $providers = ['ses' => 'Amazon SES', 'resend' => 'Resend'];
        $schedule  = $this->scheduler->fullSchedule();

        return view('admin.deliverability.warmup.create', compact('groups', 'templates', 'providers', 'schedule'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'domain'             => 'required|string|max:253',
            'provider'           => 'required|in:ses,resend',
            'email_group_id'     => 'nullable|exists:email_groups,id',
            'email_template_id'  => 'nullable|exists:email_templates,id',
            'start_date'         => 'required|date|after_or_equal:today',
            'end_date'           => 'required|date|after:start_date',
            'day1_emails'        => 'required|in:5,10',
            'increase_factor'    => 'required|integer|min:1|max:5',
            'max_bounce_rate'    => 'required|numeric|min:0.1|max:20',
            'max_complaint_rate' => 'required|numeric|min:0.01|max:5',
        ]);

        $data['domain']      = strtolower(trim($data['domain']));
        $data['current_day'] = 1;
        $data['daily_limit'] = (int) $data['day1_emails'];
        $data['status']      = 'pending';

        $plan = WarmupPlan::create($data);

        return redirect()->route('admin.warmup.show', $plan)
            ->with('success', 'Warmup plan created. Activate it to start sending.');
    }

    public function show(WarmupPlan $warmup): View
    {
        $warmup->load('group', 'template');

        $logs = $warmup->logs()
            ->orderBy('date')
            ->get();

        $todayLog   = $warmup->todayLog();
        $totalSent  = $warmup->totalSent();
        $schedule   = $this->scheduler->fullSchedule();
        $safety     = $this->scheduler->safetyCheck($warmup);
        $calDay     = $warmup->calendarDay();
        $todayLimit = $this->scheduler->dailyLimitForDay($calDay);
        $sentToday  = $todayLog?->emails_sent ?? 0;
        $remaining  = max(0, $todayLimit - $sentToday);

        return view('admin.deliverability.warmup.show', compact(
            'warmup', 'logs', 'todayLog', 'totalSent',
            'schedule', 'safety', 'calDay', 'todayLimit',
            'sentToday', 'remaining'
        ));
    }

    public function activate(WarmupPlan $warmup): RedirectResponse
    {
        if (!in_array($warmup->status, ['pending', 'paused'])) {
            return back()->with('error', 'Plan cannot be activated from its current status.');
        }

        $warmup->update(['status' => 'active', 'pause_reason' => null]);

        return back()->with('success', 'Warmup plan activated. It will run at the next scheduled time.');
    }

    public function pause(WarmupPlan $warmup): RedirectResponse
    {
        if ($warmup->status !== 'active') {
            return back()->with('error', 'Only active plans can be paused.');
        }

        $warmup->update(['status' => 'paused', 'pause_reason' => 'Manually paused by admin']);

        return back()->with('success', 'Warmup plan paused.');
    }

    public function resume(WarmupPlan $warmup): RedirectResponse
    {
        if ($warmup->status !== 'paused') {
            return back()->with('error', 'Only paused plans can be resumed.');
        }

        $warmup->update(['status' => 'active', 'pause_reason' => null]);

        return back()->with('success', 'Warmup plan resumed.');
    }

    public function stop(WarmupPlan $warmup): RedirectResponse
    {
        if (in_array($warmup->status, ['completed', 'failed'])) {
            return back()->with('error', 'Plan is already stopped.');
        }

        $warmup->update(['status' => 'failed', 'pause_reason' => 'Manually stopped by admin']);

        return back()->with('success', 'Warmup plan stopped.');
    }

    public function runNow(WarmupPlan $warmup): RedirectResponse
    {
        if ($warmup->status !== 'active') {
            return back()->with('error', 'Only active plans can be triggered manually.');
        }

        \Artisan::call('warmup:run', ['--plan' => $warmup->id]);

        return back()->with('success', 'Warmup run dispatched. Check back in a few minutes to see results.');
    }

    public function destroy(WarmupPlan $warmup): RedirectResponse
    {
        $warmup->delete();

        return redirect()->route('admin.warmup.index')
            ->with('success', 'Warmup plan deleted.');
    }
}
