@extends('layouts.admin')

@section('title', $campaign->name)
@section('page-title', $campaign->name)
@section('page-subtitle', 'Campaign details and sending progress')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm text-slate-400 dark:text-slate-500 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Dashboard</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('admin.campaigns.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Campaigns</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-600 dark:text-slate-300 font-medium truncate max-w-[200px]">{{ $campaign->name }}</span>
</nav>

{{-- Flash messages --}}
@foreach(['success' => ['emerald', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'], 'error' => ['red', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']] as $type => [$color, $icon])
@if(session($type))
<div class="mb-5 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 border border-{{ $color }}-200 dark:border-{{ $color }}-800 rounded-xl px-4 py-3 flex items-center gap-3">
    <svg class="w-4 h-4 text-{{ $color }}-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
    </svg>
    <p class="text-sm text-{{ $color }}-700 dark:text-{{ $color }}-400 font-medium">{{ session($type) }}</p>
</div>
@endif
@endforeach

@php
    $statusConfig = [
        'draft'     => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',   'Draft'],
        'running'   => ['bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',    'Running'],
        'paused'    => ['bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400','Paused'],
        'completed' => ['bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400','Completed'],
        'failed'    => ['bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',        'Failed'],
    ][$campaign->status] ?? ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $campaign->status];
@endphp

{{-- Campaign header card --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-7 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-600 flex items-center justify-center shadow-md shadow-brand-300/40 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $campaign->name }}</h2>
                    <span id="status-badge"
                          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusConfig[0] }}">
                        @if($campaign->status === 'running')
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        @endif
                        {{ $statusConfig[1] }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 flex flex-wrap gap-x-4 gap-y-1">
                    <span>List: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $campaign->emailGroup?->name ?? '—' }}</span></span>
                    <span>Delay: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $campaign->delay_minutes }} min</span></span>
                    <span>Created {{ $campaign->created_at->format('M j, Y') }}</span>
                </p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            @if(in_array($campaign->status, ['draft', 'paused', 'failed']))
            <form method="POST" action="{{ route('admin.campaigns.start', $campaign) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $campaign->status === 'paused' ? 'Resume' : 'Start Campaign' }}
                </button>
            </form>
            @endif

            @if($campaign->status === 'running')
            <form method="POST" action="{{ route('admin.campaigns.pause', $campaign) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-sm transition hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pause
                </button>
            </form>
            @endif

            @if(in_array($campaign->status, ['draft', 'paused']))
            <a href="{{ route('admin.campaigns.edit', $campaign) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 hover:bg-brand-50 dark:hover:bg-brand-900/20 text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 hover:border-brand-200 dark:hover:border-brand-800 text-sm font-bold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            @endif

            <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}"
                  onsubmit="return confirm('Delete this campaign and all its logs?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 border border-slate-200 dark:border-slate-700 hover:border-red-200 dark:hover:border-red-800 text-sm font-bold rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Progress section --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-7 mb-5">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-[15px] font-bold text-slate-900 dark:text-white">Sending Progress</h3>
        <span class="text-3xl font-black text-brand-600 dark:text-brand-400 tabular-nums" id="progress-text">{{ $campaign->progress_percentage }}%</span>
    </div>

    {{-- Progress bar --}}
    <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mb-6">
        <div id="progress-bar"
             class="h-full rounded-full transition-all duration-500
                    {{ $campaign->status === 'completed' ? 'bg-emerald-500' : ($campaign->status === 'failed' ? 'bg-red-400' : 'bg-gradient-to-r from-brand-500 to-violet-500') }}"
             style="width: {{ $campaign->progress_percentage }}%">
        </div>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 lg:gap-4">
        @php
            $statItems = [
                ['label' => 'Total',     'id' => 'total-count',     'value' => $campaign->total_emails, 'text' => 'text-slate-800 dark:text-slate-200',   'bg' => 'bg-slate-50 dark:bg-slate-800'],
                ['label' => 'Sent',      'id' => 'sent-count',      'value' => $campaign->sent_count,   'text' => 'text-emerald-700 dark:text-emerald-400','bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                ['label' => 'Opened',    'id' => 'opened-count',    'value' => $openedCount,            'text' => 'text-violet-700 dark:text-violet-400',  'bg' => 'bg-violet-50 dark:bg-violet-900/20'],
                ['label' => 'Replied',   'id' => 'replied-count',   'value' => $repliedCount,           'text' => 'text-teal-700 dark:text-teal-400',      'bg' => 'bg-teal-50 dark:bg-teal-900/20'],
                ['label' => 'Failed',    'id' => 'failed-count',    'value' => $campaign->failed_count, 'text' => 'text-red-700 dark:text-red-400',        'bg' => 'bg-red-50 dark:bg-red-900/20'],
                ['label' => 'Remaining', 'id' => 'remaining-count', 'value' => $campaign->remaining,    'text' => 'text-brand-700 dark:text-brand-400',    'bg' => 'bg-brand-50 dark:bg-brand-900/20'],
            ];
        @endphp
        @foreach($statItems as $stat)
        <div class="rounded-xl {{ $stat['bg'] }} p-4 text-center">
            <p class="text-2xl lg:text-3xl font-black {{ $stat['text'] }} tabular-nums" id="{{ $stat['id'] }}">{{ $stat['value'] }}</p>
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wide">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    @if($campaign->started_at)
    <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-400 dark:text-slate-500">
        <span>Started: <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $campaign->started_at->format('M j, Y H:i') }}</span></span>
        @if($campaign->completed_at)
        <span>Completed: <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $campaign->completed_at->format('M j, Y H:i') }}</span></span>
        <span>Duration: <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $campaign->started_at->diffForHumans($campaign->completed_at, true) }}</span></span>
        @endif
    </div>
    @endif
</div>

{{-- Follow-up Emails card --}}
@if($followups->isNotEmpty())
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-7 mb-5">
    <div class="flex items-center gap-2.5 mb-4">
        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-[15px] font-bold text-slate-900 dark:text-white">Follow-up Emails</h3>
        <span class="text-xs text-slate-400 dark:text-slate-500">Only sent to recipients who haven't replied</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-{{ $followups->count() > 1 ? '2' : '1' }} gap-4">
        @foreach($followups as $fu)
        @php
            $fuStatusConfig = [
                'pending'   => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', 'Pending'],
                'running'   => ['bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400', 'Running'],
                'completed' => ['bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400', 'Completed'],
            ][$fu->status] ?? ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $fu->status];

            $fuProgress = $fu->total_emails > 0
                ? (int) round(($fu->sent_count + $fu->failed_count) / $fu->total_emails * 100)
                : 0;
        @endphp
        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                        <span class="text-xs font-black text-violet-700 dark:text-violet-400">{{ $fu->sort_order }}</span>
                    </div>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200">Follow-up {{ $fu->sort_order }}</span>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold {{ $fuStatusConfig[0] }}">
                    @if($fu->status === 'running')
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    @endif
                    {{ $fuStatusConfig[1] }}
                </span>
            </div>

            {{-- Template --}}
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                Template:
                <span class="font-medium text-slate-700 dark:text-slate-300">
                    {{ $fu->template?->title ?? 'Same as campaign (auto)' }}
                </span>
            </p>

            {{-- Timing info --}}
            @if($fu->status === 'pending')
                @if($campaign->status === 'completed' && $campaign->completed_at)
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">
                    Scheduled in
                    <span class="font-semibold text-violet-600 dark:text-violet-400">{{ $fu->delay_days }} day{{ $fu->delay_days !== 1 ? 's' : '' }}</span>
                    after campaign completed
                    <span class="text-slate-500 dark:text-slate-400">(around {{ $campaign->completed_at->addDays($fu->delay_days)->format('M j') }})</span>
                </p>
                @elseif($fu->sort_order > 1)
                    @php $prevFu = $followups->where('sort_order', $fu->sort_order - 1)->first(); @endphp
                    @if($prevFu && $prevFu->completed_at)
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">
                        Scheduled in
                        <span class="font-semibold text-violet-600 dark:text-violet-400">{{ $fu->delay_days }} day{{ $fu->delay_days !== 1 ? 's' : '' }}</span>
                        after Follow-up {{ $fu->sort_order - 1 }}
                        <span class="text-slate-500 dark:text-slate-400">(around {{ $prevFu->completed_at->addDays($fu->delay_days)->format('M j') }})</span>
                    </p>
                    @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">
                        Triggers <span class="font-semibold text-violet-600 dark:text-violet-400">{{ $fu->delay_days }} day{{ $fu->delay_days !== 1 ? 's' : '' }}</span>
                        after Follow-up {{ $fu->sort_order - 1 }} completes
                    </p>
                    @endif
                @else
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">
                    Triggers <span class="font-semibold text-violet-600 dark:text-violet-400">{{ $fu->delay_days }} day{{ $fu->delay_days !== 1 ? 's' : '' }}</span>
                    after campaign completes
                </p>
                @endif
            @elseif($fu->status === 'running')
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">
                Started: <span class="font-medium text-slate-600 dark:text-slate-300">{{ $fu->started_at?->format('M j, Y H:i') ?? '—' }}</span>
            </p>
            {{-- Progress bar --}}
            <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mb-2">
                <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-brand-500 transition-all duration-500"
                     style="width: {{ $fuProgress }}%"></div>
            </div>
            @elseif($fu->status === 'completed')
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">
                Completed: <span class="font-medium text-slate-600 dark:text-slate-300">{{ $fu->completed_at?->format('M j, Y H:i') ?? '—' }}</span>
            </p>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-2 mt-2">
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 p-2 text-center">
                    <p class="text-base font-black text-slate-700 dark:text-slate-300 tabular-nums">{{ $fu->total_emails }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Target</p>
                </div>
                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 p-2 text-center">
                    <p class="text-base font-black text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fu->sent_count }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Sent</p>
                </div>
                <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-2 text-center">
                    <p class="text-base font-black text-red-700 dark:text-red-400 tabular-nums">{{ $fu->failed_count }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Failed</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Template Performance --}}
@if($usedTemplates->isNotEmpty())
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-7 mb-5">
    <div class="flex items-center gap-2.5 mb-4">
        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <h3 class="text-[15px] font-bold text-slate-900 dark:text-white">Template Performance</h3>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="relative">
            <select id="template-select"
                    class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                           text-sm text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 pr-8 outline-none cursor-pointer
                           focus:border-brand-400 transition min-w-[220px]">
                <option value="">— Select a template —</option>
                @foreach($usedTemplates as $tplId => $tpl)
                <option value="{{ $tplId }}">{{ $tpl->title }}</option>
                @endforeach
            </select>
            <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        <button id="filter-reset-btn" onclick="clearTplFilter()"
                class="hidden text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition px-2 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
            Clear filter ✕
        </button>
    </div>

    <div id="template-stats-cards" class="hidden grid grid-cols-3 gap-3">
        <button data-filter="all"
                class="tpl-stat-card rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-4 text-center w-full transition hover:ring-2 hover:ring-emerald-300 dark:hover:ring-emerald-700">
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 tabular-nums" id="tpl-sent">0</p>
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wide">Sent</p>
        </button>
        <button data-filter="open"
                class="tpl-stat-card rounded-xl bg-violet-50 dark:bg-violet-900/20 p-4 text-center w-full transition hover:ring-2 hover:ring-violet-300 dark:hover:ring-violet-700">
            <p class="text-2xl font-black text-violet-700 dark:text-violet-400 tabular-nums" id="tpl-opens">0</p>
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wide">Opens</p>
        </button>
        <button data-filter="reply"
                class="tpl-stat-card rounded-xl bg-teal-50 dark:bg-teal-900/20 p-4 text-center w-full transition hover:ring-2 hover:ring-teal-300 dark:hover:ring-teal-700">
            <p class="text-2xl font-black text-teal-700 dark:text-teal-400 tabular-nums" id="tpl-replies">0</p>
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wide">Replies</p>
        </button>
    </div>
</div>
<script>
window.__tplStats = @json($templateStats->map(fn($s) => ['sent' => (int)$s->total_sent, 'opens' => (int)$s->total_opens, 'replies' => (int)$s->total_replies])->toArray());
</script>
@endif

{{-- Recent logs --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
    <div class="px-5 lg:px-7 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-[15px] font-bold text-slate-900 dark:text-white">Recent Activity</h3>
        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Last {{ $recentLogs->count() }} entries</span>
    </div>

    @if($recentLogs->isEmpty())
    <div class="py-14 px-6 text-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">No activity yet</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Start the campaign to begin sending emails.</p>
    </div>
    @else
    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm" id="logs-table">
            <thead>
                <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <th class="text-left px-5 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recipient</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Template</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Opens</th>
                    <th class="text-center px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Clicks</th>
                    <th class="text-center px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reply</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($recentLogs as $log)
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/30 transition-colors log-row"
                    data-template-id="{{ $log->email_template_id ?? '' }}"
                    data-open="{{ $log->open_count > 0 ? '1' : '0' }}"
                    data-reply="{{ $log->reply_count > 0 ? '1' : '0' }}">
                    <td class="px-5 py-3.5">
                        <p class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $log->email }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="text-slate-600 dark:text-slate-400 text-sm truncate max-w-[200px]">{{ $log->template?->title ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        @if($log->status === 'sent')
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Sent
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400"
                              title="{{ $log->error_message }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Failed
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($log->open_count > 0)
                        <span class="inline-flex items-center justify-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400">
                            {{ $log->open_count }}
                        </span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($log->click_count > 0)
                        <span class="inline-flex items-center justify-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                            {{ $log->click_count }}
                        </span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($log->reply_count > 0)
                        <span class="inline-flex items-center justify-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400"
                              title="{{ $log->replied_by }}">
                            {{ $log->reply_count }}
                        </span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-slate-400 dark:text-slate-500 text-xs">
                        {{ $log->sent_at?->diffForHumans() ?? $log->created_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile list --}}
    <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
        @foreach($recentLogs as $log)
        <div class="px-4 py-3.5 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ $log->email }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $log->template?->title ?? '—' }} · {{ $log->created_at->diffForHumans() }}</p>
            </div>
            @if($log->status === 'sent')
            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 whitespace-nowrap">Sent</span>
            @else
            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 whitespace-nowrap">Failed</span>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    const campaignId    = {{ $campaign->id }};
    const initialStatus = '{{ $campaign->status }}';

    const progressBar    = document.getElementById('progress-bar');
    const progressText   = document.getElementById('progress-text');
    const sentCount      = document.getElementById('sent-count');
    const failedCount    = document.getElementById('failed-count');
    const remainingCount = document.getElementById('remaining-count');
    const statusBadge    = document.getElementById('status-badge');

    function updateUI(data) {
        if (progressBar)    progressBar.style.width = data.percentage + '%';
        if (progressText)   progressText.textContent = data.percentage + '%';
        if (sentCount)      sentCount.textContent    = data.sent;
        if (failedCount)    failedCount.textContent  = data.failed;
        if (remainingCount) remainingCount.textContent = data.remaining;

        if (statusBadge) {
            const labels  = { draft:'Draft', running:'Running', paused:'Paused', completed:'Completed', failed:'Failed' };
            const classes = {
                draft:     'bg-slate-100 text-slate-600',
                running:   'bg-blue-100 text-blue-700',
                paused:    'bg-amber-100 text-amber-700',
                completed: 'bg-emerald-100 text-emerald-700',
                failed:    'bg-red-100 text-red-700',
            };
            statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold ' + (classes[data.status] || classes.draft);
            statusBadge.textContent = labels[data.status] || data.status;
        }

        if (progressBar) {
            if (data.status === 'completed') {
                progressBar.className = 'h-full rounded-full transition-all duration-500 bg-emerald-500';
            } else if (data.status === 'failed') {
                progressBar.className = 'h-full rounded-full transition-all duration-500 bg-red-400';
            }
        }
    }

    function poll() {
        fetch('{{ route("admin.campaigns.progress", $campaign->id) }}')
            .then(r => r.json())
            .then(data => {
                updateUI(data);
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(timer);
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(() => {});
    }

    let timer = null;
    if (initialStatus === 'running' || initialStatus === 'paused') {
        timer = setInterval(poll, 3000);
    }
})();

// Template performance filter
(function () {
    if (!window.__tplStats) return;

    const tplSelect  = document.getElementById('template-select');
    const statsCards = document.getElementById('template-stats-cards');
    const tplSent    = document.getElementById('tpl-sent');
    const tplOpens   = document.getElementById('tpl-opens');
    const tplReplies = document.getElementById('tpl-replies');
    const resetBtn   = document.getElementById('filter-reset-btn');
    const logRows    = document.querySelectorAll('.log-row');
    const statBtns   = document.querySelectorAll('.tpl-stat-card');

    let activeTemplateId = null;
    let activeFilter     = null;

    tplSelect && tplSelect.addEventListener('change', function () {
        activeTemplateId = this.value || null;
        activeFilter     = null;
        setActiveCard(null);
        updateStatCards();
        applyFilter();
    });

    statBtns.forEach(btn => btn.addEventListener('click', function () {
        const f = this.dataset.filter;
        activeFilter = (activeFilter === f) ? null : f;
        setActiveCard(activeFilter);
        applyFilter();
    }));

    function updateStatCards() {
        if (!activeTemplateId || !window.__tplStats[activeTemplateId]) {
            statsCards && statsCards.classList.add('hidden');
            return;
        }
        const s = window.__tplStats[activeTemplateId];
        if (tplSent)    tplSent.textContent    = s.sent;
        if (tplOpens)   tplOpens.textContent   = s.opens;
        if (tplReplies) tplReplies.textContent = s.replies;
        statsCards && statsCards.classList.remove('hidden');
    }

    function applyFilter() {
        logRows.forEach(row => {
            if (!activeTemplateId) { row.style.display = ''; return; }
            if (String(row.dataset.templateId) !== String(activeTemplateId)) { row.style.display = 'none'; return; }
            if (activeFilter === 'open'  && row.dataset.open  !== '1') { row.style.display = 'none'; return; }
            if (activeFilter === 'reply' && row.dataset.reply !== '1') { row.style.display = 'none'; return; }
            row.style.display = '';
        });
        resetBtn && resetBtn.classList.toggle('hidden', !activeTemplateId);
    }

    window.clearTplFilter = function () {
        if (tplSelect) tplSelect.value = '';
        activeTemplateId = null;
        activeFilter     = null;
        setActiveCard(null);
        statsCards && statsCards.classList.add('hidden');
        logRows.forEach(row => row.style.display = '');
        resetBtn && resetBtn.classList.add('hidden');
    };

    const ringMap = { all: 'ring-emerald-400', open: 'ring-violet-400', reply: 'ring-teal-400' };
    function setActiveCard(filter) {
        statBtns.forEach(btn => {
            const isActive = btn.dataset.filter === filter;
            btn.classList.toggle('ring-2', isActive);
            Object.values(ringMap).forEach(c => btn.classList.remove(c));
            if (isActive && ringMap[filter]) btn.classList.add(ringMap[filter]);
        });
    }
})();
</script>
@endpush
