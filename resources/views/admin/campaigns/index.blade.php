@extends('layouts.admin')

@section('title', 'Campaigns')
@section('page-title', 'Campaigns')
@section('page-subtitle', 'Manage and launch your email campaigns')

@section('content')

{{-- Flash message --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-medium">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
    <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

{{-- Header row --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">All Campaigns</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $counts['total'] }} total campaigns</p>
    </div>
    <a href="{{ route('admin.campaigns.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-brand-300/30 transition hover:-translate-y-0.5 hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Campaign
    </a>
</div>

{{-- Stats mini-cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
    @php
        $statCards = [
            ['label' => 'Total',     'value' => $counts['total'],     'bg' => 'bg-slate-100 dark:bg-slate-800',    'text' => 'text-slate-700 dark:text-slate-300',    'dot' => 'bg-slate-400 dark:bg-slate-500'],
            ['label' => 'Running',   'value' => $counts['running'],   'bg' => 'bg-blue-50 dark:bg-blue-900/20',    'text' => 'text-blue-700 dark:text-blue-400',      'dot' => 'bg-blue-500'],
            ['label' => 'Completed', 'value' => $counts['completed'], 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
            ['label' => 'Draft',     'value' => $counts['draft'],     'bg' => 'bg-amber-50 dark:bg-amber-900/20',  'text' => 'text-amber-700 dark:text-amber-400',    'dot' => 'bg-amber-500'],
        ];
    @endphp
    @foreach($statCards as $card)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm px-4 py-4 flex items-center gap-3 hover:shadow-md dark:hover:shadow-slate-900/50 transition-shadow">
        <div class="w-10 h-10 rounded-xl {{ $card['bg'] }} {{ $card['text'] }} flex items-center justify-center font-extrabold text-base flex-shrink-0">
            {{ $card['value'] }}
        </div>
        <div>
            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ $card['label'] }}</p>
            <div class="flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full {{ $card['dot'] }} {{ $card['dot'] === 'bg-blue-500' && $counts['running'] > 0 ? 'animate-pulse' : '' }}"></span>
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">campaigns</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Campaigns table --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">

    @if($campaigns->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-brand-400 dark:text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </div>
        <p class="text-base font-bold text-slate-800 dark:text-slate-200 mb-1">No campaigns yet</p>
        <p class="text-sm text-slate-400 dark:text-slate-500 mb-6 max-w-xs">Create your first campaign to start sending emails to your lists.</p>
        <a href="{{ route('admin.campaigns.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Campaign
        </a>
    </div>

    @else

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                    <th class="text-left px-5 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Campaign</th>
                    <th class="text-left px-4 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email List</th>
                    <th class="text-left px-4 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-4 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Progress</th>
                    <th class="text-left px-4 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Delay</th>
                    <th class="text-right px-5 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($campaigns as $campaign)
                @php
                    $statusConfig = [
                        'draft'     => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',   'Draft'],
                        'running'   => ['bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',    'Running'],
                        'paused'    => ['bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400','Paused'],
                        'completed' => ['bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400','Completed'],
                        'failed'    => ['bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',        'Failed'],
                    ][$campaign->status] ?? ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $campaign->status];
                    $pct = $campaign->progress_percentage;
                @endphp
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.campaigns.show', $campaign) }}"
                           class="font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition">
                            {{ $campaign->name }}
                        </a>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $campaign->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-slate-700 dark:text-slate-300 font-medium text-sm">{{ $campaign->emailGroup?->name ?? '—' }}</span>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $campaign->total_emails }} recipients</p>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusConfig[0] }}">
                            @if($campaign->status === 'running')
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            @endif
                            {{ $statusConfig[1] }}
                        </span>
                    </td>
                    <td class="px-4 py-4 w-48">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300
                                    {{ $campaign->status === 'completed' ? 'bg-emerald-500' : ($campaign->status === 'failed' ? 'bg-red-400' : 'bg-brand-500') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 w-8 text-right">{{ $pct }}%</span>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $campaign->sent_count }}/{{ $campaign->total_emails }} sent</p>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">{{ $campaign->delay_minutes }}m</span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}"
                               class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition"
                               title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                  onsubmit="return confirm('Delete this campaign?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
        @foreach($campaigns as $campaign)
        @php
            $statusCfg = [
                'draft'     => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',   'Draft'],
                'running'   => ['bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',    'Running'],
                'paused'    => ['bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400','Paused'],
                'completed' => ['bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400','Completed'],
                'failed'    => ['bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',        'Failed'],
            ][$campaign->status] ?? ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $campaign->status];
            $p = $campaign->progress_percentage;
        @endphp
        <div class="p-4 space-y-3 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <a href="{{ route('admin.campaigns.show', $campaign) }}"
                       class="font-bold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition text-sm">
                        {{ $campaign->name }}
                    </a>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $campaign->emailGroup?->name ?? '—' }} · {{ $campaign->total_emails }} recipients</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold {{ $statusCfg[0] }} whitespace-nowrap flex-shrink-0">
                    @if($campaign->status === 'running')<span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>@endif
                    {{ $statusCfg[1] }}
                </span>
            </div>
            <div>
                <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1.5">
                    <span>{{ $campaign->sent_count }}/{{ $campaign->total_emails }} sent</span>
                    <span class="font-semibold">{{ $p }}%</span>
                </div>
                <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $campaign->status === 'completed' ? 'bg-emerald-500' : 'bg-brand-500' }}"
                         style="width: {{ $p }}%"></div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 dark:text-slate-500">{{ $campaign->delay_minutes }}m delay · {{ $campaign->created_at->diffForHumans() }}</span>
                <a href="{{ route('admin.campaigns.show', $campaign) }}"
                   class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition">
                    View →
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($campaigns->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
        {{ $campaigns->links() }}
    </div>
    @endif

    @endif
</div>

@endsection
