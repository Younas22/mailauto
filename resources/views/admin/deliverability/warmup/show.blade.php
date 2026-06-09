@extends('layouts.admin')

@section('title', 'Warmup — ' . $warmup->domain)
@section('page-title', $warmup->name)
@section('page-subtitle', 'Warmup plan for ' . $warmup->domain)

@section('content')

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-xl px-4 py-3 text-sm text-red-600 dark:text-red-400">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
    {{ session('error') }}
</div>
@endif

@php
    $statusColors = [
        'pending'   => ['badge' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',     'ring' => '#94a3b8'],
        'active'    => ['badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'ring' => '#10b981'],
        'paused'    => ['badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',   'ring' => '#f59e0b'],
        'completed' => ['badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',       'ring' => '#3b82f6'],
        'failed'    => ['badge' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',           'ring' => '#ef4444'],
    ];
    $sc        = $statusColors[$warmup->status] ?? $statusColors['pending'];
    $progress  = $warmup->progressPercent();
    $circumference = round(2 * 3.14159 * 34);
    $dashOffset    = round($circumference * (1 - $progress / 100));
@endphp

{{-- ── Status header card ───────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 mb-6 flex flex-wrap items-center gap-5 shadow-sm">
    {{-- Progress ring --}}
    <div class="relative w-20 h-20 flex-shrink-0">
        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 80 80">
            <circle cx="40" cy="40" r="34" fill="none" stroke="#e2e8f0" stroke-width="7" class="dark:stroke-slate-700"/>
            <circle cx="40" cy="40" r="34" fill="none"
                    stroke="{{ $sc['ring'] }}"
                    stroke-width="7" stroke-linecap="round"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $dashOffset }}"/>
        </svg>
        <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-slate-800 dark:text-white">
            {{ $progress }}%
        </span>
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap mb-1">
            <span class="text-lg font-bold text-slate-900 dark:text-white font-mono">{{ $warmup->domain }}</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sc['badge'] }}">
                {{ ucfirst($warmup->status) }}
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ ucfirst($warmup->provider) }}
            </span>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Day <strong class="text-slate-700 dark:text-slate-200">{{ $calDay }}</strong>
            of {{ \App\Services\WarmupScheduleService::MAX_DAY }}
            · Started {{ $warmup->start_date->format('M j, Y') }}
            · {{ number_format($totalSent) }} emails sent total
        </p>
        @if($warmup->pause_reason)
        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $warmup->pause_reason }}
        </p>
        @endif
    </div>

    {{-- Action buttons --}}
    <div class="flex flex-wrap gap-2 flex-shrink-0">
        @if($warmup->status === 'pending')
        <form method="POST" action="{{ route('admin.warmup.activate', $warmup) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Activate
            </button>
        </form>
        @endif

        @if($warmup->status === 'active')
        <form method="POST" action="{{ route('admin.warmup.run-now', $warmup) }}">
            @csrf
            <button type="submit" data-no-loading
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Run Now
            </button>
        </form>
        <form method="POST" action="{{ route('admin.warmup.pause', $warmup) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400 text-sm font-semibold hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Pause
            </button>
        </form>
        @endif

        @if($warmup->status === 'paused')
        <form method="POST" action="{{ route('admin.warmup.resume', $warmup) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Resume
            </button>
        </form>
        @endif

        @if(!in_array($warmup->status, ['completed', 'failed']))
        <form method="POST" action="{{ route('admin.warmup.stop', $warmup) }}"
              onsubmit="return confirm('Stop this warmup plan? This cannot be undone.')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-red-200 dark:border-red-800 text-red-500 text-sm font-semibold hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/></svg>
                Stop
            </button>
        </form>
        @endif
    </div>
</div>

{{-- ── Today's metrics ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php
        $metricCards = [
            ['value' => number_format($sentToday),  'label' => 'Sent Today',     'sub' => 'of ' . number_format($todayLimit) . ' target', 'color' => 'text-brand-600 dark:text-brand-400'],
            ['value' => number_format($remaining),   'label' => 'Remaining',      'sub' => 'for today',  'color' => 'text-amber-600 dark:text-amber-400'],
            ['value' => number_format($todayLimit),  'label' => 'Daily Limit',    'sub' => 'Day ' . $calDay . ' target', 'color' => 'text-slate-700 dark:text-slate-200'],
            ['value' => number_format($totalSent),   'label' => 'Total Sent',     'sub' => 'all time',   'color' => 'text-emerald-600 dark:text-emerald-400'],
        ];
    @endphp
    @foreach($metricCards as $card)
    <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm text-center">
        <p class="text-2xl font-bold {{ $card['color'] }} mb-0.5">{{ $card['value'] }}</p>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $card['label'] }}</p>
        <p class="text-[10px] text-slate-400 dark:text-slate-600">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Safety status ────────────────────────────────────────────────────── --}}
@if(!$safety['safe'])
<div class="mb-6 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/40 rounded-2xl px-5 py-4">
    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <div>
        <p class="text-sm font-semibold text-red-700 dark:text-red-400">Safety Threshold Exceeded</p>
        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">{{ $safety['reason'] }}</p>
    </div>
</div>
@endif

{{-- ── Warmup schedule timeline ─────────────────────────────────────────── --}}
<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm mb-6 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Warmup Schedule</h2>
    </div>
    <div class="p-5">
        @php $maxVolume = \App\Services\WarmupScheduleService::MAX_VOLUME; @endphp
        <div class="flex items-end gap-1.5 h-28">
            @foreach($schedule as $day => $limit)
            @php
                $barH    = max(4, (int) round($limit / $maxVolume * 100));
                $actual  = $logs->firstWhere(fn($l) => \Carbon\Carbon::parse($l->date)->diffInDays($warmup->start_date) + 1 === $day);
                $isToday = $day === $calDay;
                $isPast  = $day < $calDay;
                $isFuture = $day > $calDay;
                $barColor = $isToday ? 'bg-brand-500' : ($isPast ? 'bg-emerald-400' : 'bg-slate-200 dark:bg-slate-700');
            @endphp
            <div class="flex-1 flex flex-col items-center gap-1 group relative">
                {{-- Tooltip --}}
                <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-800 dark:bg-slate-700 text-white text-[10px] font-semibold px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                    Day {{ $day }}: {{ number_format($limit) }}
                    @if($actual) <br>Sent: {{ number_format($actual->emails_sent) }} @endif
                </div>
                {{-- Bar --}}
                <div class="w-full rounded-t-sm {{ $barColor }} transition-all" style="height: {{ $barH }}%"></div>
                {{-- Day label --}}
                <span class="text-[9px] font-semibold {{ $isToday ? 'text-brand-600 dark:text-brand-400' : 'text-slate-400' }}">
                    {{ $day }}
                </span>
            </div>
            @endforeach
        </div>
        <div class="flex items-center gap-4 mt-3 text-[11px] text-slate-400">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-400 inline-block"></span> Past days</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-brand-500 inline-block"></span> Today (Day {{ $calDay }})</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-slate-200 dark:bg-slate-700 inline-block"></span> Upcoming</span>
        </div>
    </div>
</div>

{{-- ── Daily log table ──────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Daily Log</h2>
        <span class="text-xs text-slate-400">{{ $logs->count() }} days recorded</span>
    </div>

    @if($logs->isEmpty())
    <div class="p-10 text-center text-sm text-slate-400 dark:text-slate-600">
        No logs yet — the plan will record entries once it starts sending.
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Target</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sent</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Failed</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Bounces</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Complaints</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($logs->sortByDesc('date') as $log)
                @php
                    $pct = $log->daily_limit > 0 ? min(100, round($log->emails_sent / $log->daily_limit * 100)) : 0;
                    $barW = $pct;
                @endphp
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                        {{ $log->date->format('D, M j') }}
                        @if($log->date->isToday())
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">Today</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center text-slate-500 dark:text-slate-400">{{ number_format($log->daily_limit) }}</td>
                    <td class="px-4 py-3.5 text-center font-semibold text-slate-700 dark:text-slate-300">{{ number_format($log->emails_sent) }}</td>
                    <td class="px-4 py-3.5 text-center {{ $log->emails_failed > 0 ? 'text-red-500 dark:text-red-400 font-medium' : 'text-slate-400' }}">
                        {{ $log->emails_failed > 0 ? number_format($log->emails_failed) : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-center {{ $log->bounce_count > 0 ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-slate-400' }}">
                        {{ $log->bounce_count > 0 ? $log->bounce_count . ' (' . $log->bounceRate() . '%)' : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-center {{ $log->complaint_count > 0 ? 'text-red-500 dark:text-red-400 font-medium' : 'text-slate-400' }}">
                        {{ $log->complaint_count > 0 ? $log->complaint_count . ' (' . $log->complaintRate() . '%)' : '—' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-1.5 rounded-full bg-brand-500" style="width: {{ $barW }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Delete link at bottom --}}
<div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
    <a href="{{ route('admin.warmup.index') }}"
       class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-1.5 transition-colors">
        ← Back to all plans
    </a>
    <form method="POST" action="{{ route('admin.warmup.destroy', $warmup) }}"
          onsubmit="return confirm('Delete this warmup plan and all its logs? This cannot be undone.')">
        @csrf @method('DELETE')
        <button type="submit" class="text-xs text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors">
            Delete plan
        </button>
    </form>
</div>

@endsection
