@extends('layouts.admin')

@section('title', 'Email Logs')
@section('page-title', 'Email Logs')
@section('page-subtitle', 'Track all sent and failed emails')

@section('content')

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @php
        $statCards = [
            [
                'label'  => 'Total Emails',
                'value'  => number_format($counts['total']),
                'icon'   => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'iconBg' => 'bg-brand-50 dark:bg-brand-900/30',
                'iconFg' => 'text-brand-600 dark:text-brand-400',
                'dotBg'  => 'bg-brand-500',
                'label2' => 'logged',
            ],
            [
                'label'  => 'Delivered',
                'value'  => number_format($counts['sent']),
                'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'iconBg' => 'bg-emerald-50 dark:bg-emerald-900/30',
                'iconFg' => 'text-emerald-600 dark:text-emerald-400',
                'dotBg'  => 'bg-emerald-500',
                'label2' => 'sent',
            ],
            [
                'label'  => 'Failed',
                'value'  => number_format($counts['failed']),
                'icon'   => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                'iconBg' => 'bg-red-50 dark:bg-red-900/30',
                'iconFg' => 'text-red-500 dark:text-red-400',
                'dotBg'  => 'bg-red-500',
                'label2' => 'errors',
            ],
        ];
    @endphp

    @foreach($statCards as $card)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm
                px-5 py-4 flex items-center gap-4 hover:-translate-y-0.5 transition-all">
        <div class="w-11 h-11 rounded-xl {{ $card['iconBg'] }} flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 {{ $card['iconFg'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-0.5">
                {{ $card['label'] }}
            </p>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $card['value'] }}</span>
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $card['dotBg'] }}"></span>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ $card['label2'] }}</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters & Search Bar --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm mb-4">
    <form method="GET" action="{{ route('admin.email-logs.index') }}"
          class="flex flex-col gap-3 px-4 py-3.5">

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            {{-- Search --}}
            <div class="flex items-center gap-2 flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by email address…"
                       class="bg-transparent text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 outline-none w-full" />
            </div>

            {{-- Status filter --}}
            <div class="relative flex-shrink-0">
                <select name="status"
                        class="appearance-none w-full sm:w-40 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                               text-sm text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 pr-8 outline-none cursor-pointer
                               focus:border-brand-400 transition">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="sent"   {{ request('status') === 'sent'   ? 'selected' : '' }}>Sent</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Provider filter --}}
            <div class="relative flex-shrink-0">
                <select name="provider"
                        class="appearance-none w-full sm:w-40 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                               text-sm text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 pr-8 outline-none cursor-pointer
                               focus:border-brand-400 transition">
                    <option value="" {{ !request('provider') ? 'selected' : '' }}>All Providers</option>
                    <option value="ses"    {{ request('provider') === 'ses'    ? 'selected' : '' }}>Amazon SES</option>
                    <option value="resend" {{ request('provider') === 'resend' ? 'selected' : '' }}>Resend</option>
                </select>
                <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Campaign filter --}}
            <div class="relative flex-shrink-0">
                <select name="campaign_id"
                        class="appearance-none w-full sm:w-44 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                               text-sm text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 pr-8 outline-none cursor-pointer
                               focus:border-brand-400 transition">
                    <option value="">All Campaigns</option>
                    @foreach($campaigns as $c)
                    <option value="{{ $c->id }}" {{ request('campaign_id') == $c->id ? 'selected' : '' }}>
                        {{ Str::limit($c->name, 26) }}
                    </option>
                    @endforeach
                </select>
                <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            {{-- Date from --}}
            <div class="flex items-center gap-2 flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="bg-transparent text-sm text-slate-700 dark:text-slate-200 outline-none w-full" />
            </div>
            <span class="text-xs text-slate-400 dark:text-slate-500 text-center flex-shrink-0">to</span>
            <div class="flex items-center gap-2 flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="bg-transparent text-sm text-slate-700 dark:text-slate-200 outline-none w-full" />
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="submit"
                        class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-brand-200/30">
                    Filter
                </button>
                @if(request('search') || request('status') || request('provider') || request('campaign_id') || request('date_from') || request('date_to'))
                <a href="{{ route('admin.email-logs.index') }}"
                   class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700
                          text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition border border-slate-200 dark:border-slate-700">
                    Clear
                </a>
                @endif
                <a href="{{ route('admin.email-logs.export', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    CSV
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Results count --}}
@if(request('search') || request('status') || request('provider') || request('campaign_id') || request('date_from') || request('date_to'))
<p class="text-xs text-slate-500 dark:text-slate-400 mb-3 px-1">
    Showing <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $logs->total() }}</span>
    result{{ $logs->total() !== 1 ? 's' : '' }}
    @if(request('search')) for <span class="font-semibold text-slate-700 dark:text-slate-300">"{{ request('search') }}"</span>@endif
    @if(request('status')) with status <span class="font-semibold text-slate-700 dark:text-slate-300">{{ request('status') }}</span>@endif
    @if(request('provider')) via <span class="font-semibold text-slate-700 dark:text-slate-300">{{ request('provider') === 'ses' ? 'Amazon SES' : 'Resend' }}</span>@endif
    @if(request('campaign_id')) in campaign <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $campaigns->firstWhere('id', request('campaign_id'))?->name ?? '#'.request('campaign_id') }}</span>@endif
    @if(request('date_from') || request('date_to')) from <span class="font-semibold text-slate-700 dark:text-slate-300">{{ request('date_from', '…') }}</span> to <span class="font-semibold text-slate-700 dark:text-slate-300">{{ request('date_to', 'today') }}</span>@endif
</p>
@endif

{{-- Main table card --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">

    @if($logs->isEmpty())
    {{-- Empty state --}}
    <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="text-base font-bold text-slate-700 dark:text-slate-300 mb-1">No email logs found</p>
        <p class="text-sm text-slate-400 dark:text-slate-500 max-w-xs">
            @if(request('search') || request('status') || request('provider') || request('campaign_id') || request('date_from') || request('date_to'))
                No logs match your current filters. Try adjusting your search.
            @else
                Email logs will appear here once campaigns start sending.
            @endif
        </p>
        @if(request('search') || request('status') || request('provider') || request('campaign_id') || request('date_from') || request('date_to'))
        <a href="{{ route('admin.email-logs.index') }}"
           class="mt-4 text-sm font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition">
            Clear filters
        </a>
        @endif
    </div>

    @else

    {{-- ─── DESKTOP TABLE ─── --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Template</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Campaign</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Provider</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Error</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($logs as $log)
                @php
                    $badge = match($log->status) {
                        'sent'   => ['bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400', 'Sent',   'bg-emerald-500'],
                        'failed' => ['bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',         'Failed', 'bg-red-500'],
                        default  => ['bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400', ucfirst($log->status), 'bg-amber-500'],
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition group">

                    {{-- Email --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($log->email, 0, 1)) }}
                            </div>
                            <span class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[200px]" title="{{ $log->email }}">
                                {{ $log->email }}
                            </span>
                        </div>
                    </td>

                    {{-- Template --}}
                    <td class="px-4 py-4">
                        @if($log->template)
                            <span class="text-slate-700 dark:text-slate-300 font-medium text-sm">{{ Str::limit($log->template->title, 28) }}</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 text-sm">—</span>
                        @endif
                    </td>

                    {{-- Campaign --}}
                    <td class="px-4 py-4">
                        @if($log->campaign)
                            <a href="{{ route('admin.campaigns.show', $log->campaign) }}"
                               class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium text-sm transition">
                                {{ Str::limit($log->campaign->name, 22) }}
                            </a>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 text-sm">—</span>
                        @endif
                    </td>

                    {{-- Provider --}}
                    <td class="px-4 py-4">
                        @if($log->provider)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $log->provider === 'ses' ? 'Amazon SES' : ($log->provider === 'resend' ? 'Resend' : ucfirst($log->provider)) }}
                            </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 text-sm">—</span>
                        @endif
                    </td>

                    {{-- Status badge --}}
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $badge[0] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $badge[2] }}"></span>
                            {{ $badge[1] }}
                        </span>
                    </td>

                    {{-- Error message --}}
                    <td class="px-4 py-4 max-w-[200px]">
                        @if($log->error_message)
                            <span class="text-xs text-red-500 dark:text-red-400 truncate block" title="{{ $log->error_message }}">
                                {{ Str::limit($log->error_message, 40) }}
                            </span>
                        @else
                            <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-4 text-right">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $log->created_at->format('M d, Y') }}</span>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $log->created_at->format('h:i A') }}</p>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ─── MOBILE CARDS ─── --}}
    <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
        @foreach($logs as $log)
        @php
            $badge = match($log->status) {
                'sent'   => ['bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400', 'Sent',   'bg-emerald-500'],
                'failed' => ['bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',         'Failed', 'bg-red-500'],
                default  => ['bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400', ucfirst($log->status), 'bg-amber-500'],
            };
        @endphp
        <div class="p-4 space-y-3">

            {{-- Row 1: Avatar + Email + Badge --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($log->email, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $log->email }}</span>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold {{ $badge[0] }} whitespace-nowrap flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badge[2] }}"></span>
                    {{ $badge[1] }}
                </span>
            </div>

            {{-- Row 2: Template + Campaign --}}
            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                @if($log->template)
                <div class="flex items-center gap-1.5 min-w-0">
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span class="truncate">{{ Str::limit($log->template->title, 22) }}</span>
                </div>
                @endif
                @if($log->campaign)
                <div class="flex items-center gap-1.5 min-w-0">
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <a href="{{ route('admin.campaigns.show', $log->campaign) }}"
                       class="truncate text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium transition">
                        {{ Str::limit($log->campaign->name, 22) }}
                    </a>
                </div>
                @endif
                @if($log->provider)
                <div class="flex items-center gap-1.5 min-w-0">
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="truncate">{{ $log->provider === 'ses' ? 'Amazon SES' : ($log->provider === 'resend' ? 'Resend' : ucfirst($log->provider)) }}</span>
                </div>
                @endif
            </div>

            {{-- Row 3: Error + Date --}}
            <div class="flex items-center justify-between gap-2">
                @if($log->error_message)
                <span class="text-xs text-red-500 dark:text-red-400 truncate flex-1">{{ Str::limit($log->error_message, 36) }}</span>
                @else
                <span></span>
                @endif
                <span class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0">{{ $log->created_at->format('M d, Y · h:i A') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Showing <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $logs->firstItem() }}</span>–<span class="font-semibold text-slate-700 dark:text-slate-300">{{ $logs->lastItem() }}</span>
            of <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $logs->total() }}</span> logs
        </p>
        <div class="text-sm">
            {{ $logs->links() }}
        </div>
    </div>
    @else
    <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800">
        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $logs->total() }} log{{ $logs->total() !== 1 ? 's' : '' }} total</p>
    </div>
    @endif

    @endif
</div>

@endsection

@push('head')
<style>
    /* Pagination — light mode */
    nav[aria-label="Pagination Navigation"] {
        display: flex;
        align-items: center;
        gap: 4px;
        justify-content: flex-end;
    }
    nav[aria-label="Pagination Navigation"] span,
    nav[aria-label="Pagination Navigation"] a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        text-decoration: none;
        transition: all 0.15s;
    }
    nav[aria-label="Pagination Navigation"] a:hover {
        background: #f0f4ff;
        border-color: #818cf8;
        color: #4f46e5;
    }
    nav[aria-label="Pagination Navigation"] span[aria-current="page"] > span {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }
    nav[aria-label="Pagination Navigation"] span.cursor-default {
        background: #f8fafc;
        color: #cbd5e1;
        border-color: #f1f5f9;
    }

    /* Pagination — dark mode */
    .dark nav[aria-label="Pagination Navigation"] span,
    .dark nav[aria-label="Pagination Navigation"] a {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .dark nav[aria-label="Pagination Navigation"] a:hover {
        background: #312e81;
        border-color: #6366f1;
        color: #a5b4fc;
    }
    .dark nav[aria-label="Pagination Navigation"] span[aria-current="page"] > span {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }
    .dark nav[aria-label="Pagination Navigation"] span.cursor-default {
        background: #0f172a;
        color: #334155;
        border-color: #1e293b;
    }
</style>
@endpush
