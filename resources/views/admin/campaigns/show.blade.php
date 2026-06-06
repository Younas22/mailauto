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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:gap-4">
        @php
            $statItems = [
                ['label' => 'Total',     'id' => 'total-count',     'value' => $campaign->total_emails, 'text' => 'text-slate-800 dark:text-slate-200',   'bg' => 'bg-slate-50 dark:bg-slate-800'],
                ['label' => 'Sent',      'id' => 'sent-count',      'value' => $campaign->sent_count,   'text' => 'text-emerald-700 dark:text-emerald-400','bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
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
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($recentLogs as $log)
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-800/30 transition-colors">
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
</script>
@endpush
