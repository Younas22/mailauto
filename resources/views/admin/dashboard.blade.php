@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Here\'s what\'s happening today')

@section('content')

{{-- ─────────── STAT CARDS ─────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6 lg:mb-8">

    @php
        $statCards = [
            [
                'value'    => '48,291',
                'label'    => 'Total Emails Sent',
                'badge'    => '+12.5%',
                'badge_up' => true,
                'sub'      => 'vs last month: 42,934',
                'icon_bg'  => 'bg-brand-50 dark:bg-brand-900/20',
                'icon_fg'  => 'text-brand-600 dark:text-brand-400',
                'icon'     => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            [
                'value'    => '24',
                'label'    => 'Total Templates',
                'badge'    => '+3',
                'badge_up' => true,
                'sub'      => 'Active: 18 · Draft: 6',
                'icon_bg'  => 'bg-violet-50 dark:bg-violet-900/20',
                'icon_fg'  => 'text-violet-600 dark:text-violet-400',
                'icon'     => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
            ],
            [
                'value'    => '137',
                'label'    => 'Email Lists',
                'badge'    => '+8',
                'badge_up' => true,
                'sub'      => 'Total subscribers: 9,412',
                'icon_bg'  => 'bg-sky-50 dark:bg-sky-900/20',
                'icon_fg'  => 'text-sky-600 dark:text-sky-400',
                'icon'     => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            ],
            [
                'value'    => '96.3%',
                'label'    => 'Delivery Rate',
                'badge'    => '-1.2%',
                'badge_up' => false,
                'sub'      => 'Open rate: 34.7%',
                'icon_bg'  => 'bg-amber-50 dark:bg-amber-900/20',
                'icon_fg'  => 'text-amber-600 dark:text-amber-400',
                'icon'     => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            ],
        ];
    @endphp

    @foreach($statCards as $card)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6 hover:shadow-md dark:hover:shadow-slate-900/50 hover:-translate-y-0.5 transition-all duration-200 group">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl {{ $card['icon_bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                <svg class="w-[22px] h-[22px] {{ $card['icon_fg'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
            @if($card['badge_up'])
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/25 px-2 py-1 rounded-full">
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                {{ $card['badge'] }}
            </span>
            @else
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/25 px-2 py-1 rounded-full">
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                {{ $card['badge'] }}
            </span>
            @endif
        </div>
        <p class="text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white mb-1 tracking-tight">{{ $card['value'] }}</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">{{ $card['label'] }}</p>
        <div class="mt-4 pt-3.5 border-t border-slate-50 dark:border-slate-800">
            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach

</div>

{{-- ─────────── MIDDLE ROW ─────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5 mb-6 lg:mb-8">

    {{-- Email Volume Chart --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-[15px] font-bold text-slate-900 dark:text-white">Email Volume</h2>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Last 7 days</p>
            </div>
            <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                <button class="text-xs font-bold px-3 py-1.5 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg shadow-sm">Week</button>
                <button class="text-xs font-semibold px-3 py-1.5 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg transition">Month</button>
            </div>
        </div>

        {{-- Bar chart --}}
        <div class="flex items-end justify-between gap-2 h-36 lg:h-44">
            @php
                $bars = [
                    ['day' => 'Mon', 'height' => 55, 'sent' => '1,240'],
                    ['day' => 'Tue', 'height' => 80, 'sent' => '2,891'],
                    ['day' => 'Wed', 'height' => 45, 'sent' => '980'],
                    ['day' => 'Thu', 'height' => 90, 'sent' => '4,102'],
                    ['day' => 'Fri', 'height' => 70, 'sent' => '3,201'],
                    ['day' => 'Sat', 'height' => 35, 'sent' => '621'],
                    ['day' => 'Sun', 'height' => 60, 'sent' => '1,890'],
                ];
            @endphp
            @foreach($bars as $i => $bar)
            <div class="flex-1 flex flex-col items-center gap-2 group/bar">
                <div class="relative w-full flex justify-center">
                    <div class="absolute -top-9 left-1/2 -translate-x-1/2 hidden group-hover/bar:block bg-slate-900 dark:bg-slate-700 text-white text-[11px] font-semibold rounded-lg px-2.5 py-1.5 whitespace-nowrap z-10 shadow-lg">
                        {{ $bar['sent'] }} emails
                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900 dark:border-t-slate-700"></div>
                    </div>
                    <div
                        class="w-full max-w-[36px] rounded-t-lg cursor-pointer transition-all duration-200
                               {{ $bar['height'] >= 80 ? 'bg-brand-500 dark:bg-brand-500' : 'bg-brand-100 dark:bg-brand-900/40' }}
                               group-hover/bar:bg-brand-600 dark:group-hover/bar:bg-brand-500"
                        style="height: {{ $bar['height'] }}%">
                    </div>
                </div>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ $bar['day'] }}</span>
            </div>
            @endforeach
        </div>

        <div class="mt-5 pt-4 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
            <span>Total this week: <span class="font-semibold text-slate-700 dark:text-slate-300">14,925</span></span>
            <span class="flex items-center gap-1 text-emerald-500 font-semibold">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                +18.4% vs last week
            </span>
        </div>
    </div>

    {{-- Campaign Status --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
        <h2 class="text-[15px] font-bold text-slate-900 dark:text-white mb-0.5">Campaign Status</h2>
        <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">Active campaigns overview</p>

        <div class="space-y-4">
            @php
                $statuses = [
                    ['label' => 'Sent',      'count' => 18, 'color' => 'bg-emerald-500', 'pct' => 56, 'text' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/25'],
                    ['label' => 'Scheduled', 'count' => 7,  'color' => 'bg-brand-500',   'pct' => 22, 'text' => 'text-brand-600 dark:text-brand-400',   'bg' => 'bg-brand-50 dark:bg-brand-900/25'],
                    ['label' => 'Draft',     'count' => 5,  'color' => 'bg-amber-400',   'pct' => 15, 'text' => 'text-amber-600 dark:text-amber-400',   'bg' => 'bg-amber-50 dark:bg-amber-900/25'],
                    ['label' => 'Failed',    'count' => 2,  'color' => 'bg-red-400',     'pct' => 7,  'text' => 'text-red-600 dark:text-red-400',     'bg' => 'bg-red-50 dark:bg-red-900/25'],
                ];
            @endphp
            @foreach($statuses as $s)
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $s['label'] }}</span>
                    <span class="text-xs font-bold {{ $s['text'] }} {{ $s['bg'] }} px-2 py-0.5 rounded-full">{{ $s['count'] }}</span>
                </div>
                <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="{{ $s['color'] }} h-full rounded-full transition-all duration-700" style="width: {{ $s['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 pt-4 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
            <span class="text-sm text-slate-500 dark:text-slate-400">Total Campaigns</span>
            <span class="text-xl font-extrabold text-slate-900 dark:text-white">32</span>
        </div>
    </div>

</div>

{{-- ─────────── BOTTOM ROW ─────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5">

    {{-- Recent Activity --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 lg:px-6 py-4 border-b border-slate-50 dark:border-slate-800">
            <div>
                <h2 class="text-[15px] font-bold text-slate-900 dark:text-white">Recent Activity</h2>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Latest system events</p>
            </div>
            <a href="{{ route('admin.email-logs.index') }}"
               class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition flex items-center gap-1">
                View all
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
            @php
                $activities = [
                    ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                     'color' => 'bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400',
                     'title' => 'Campaign "Summer Sale" sent',
                     'desc'  => '4,200 recipients · 96.2% delivered',
                     'time'  => '2 min ago',
                     'badge' => 'Sent', 'badge_color' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'],

                    ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                     'color' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400',
                     'title' => 'New email list imported',
                     'desc'  => '"Newsletter Q2" · 1,832 contacts',
                     'time'  => '18 min ago',
                     'badge' => 'Import', 'badge_color' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400'],

                    ['icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z',
                     'color' => 'bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400',
                     'title' => 'Template "Welcome Email" updated',
                     'desc'  => 'Modified subject line and CTA button',
                     'time'  => '1 hr ago',
                     'badge' => 'Updated', 'badge_color' => 'bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400'],

                    ['icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                     'color' => 'bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400',
                     'title' => 'Delivery failure detected',
                     'desc'  => '47 emails bounced · Campaign "Flash Deal"',
                     'time'  => '3 hr ago',
                     'badge' => 'Error', 'badge_color' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400'],

                    ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                     'color' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                     'title' => 'Campaign "Weekly Digest" scheduled',
                     'desc'  => 'Sending tomorrow at 09:00 AM',
                     'time'  => '5 hr ago',
                     'badge' => 'Scheduled', 'badge_color' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'],
                ];
            @endphp

            @foreach($activities as $activity)
            <div class="flex items-start gap-4 px-5 lg:px-6 py-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors duration-150">
                <div class="w-10 h-10 rounded-xl {{ $activity['color'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $activity['icon'] }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 leading-snug">{{ $activity['title'] }}</p>
                        <span class="text-[11px] {{ $activity['badge_color'] }} font-semibold px-2 py-0.5 rounded-full flex-shrink-0">{{ $activity['badge'] }}</span>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $activity['desc'] }}</p>
                </div>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap flex-shrink-0 mt-0.5">{{ $activity['time'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right column --}}
    <div class="space-y-4 lg:space-y-5">

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            <h2 class="text-[15px] font-bold text-slate-900 dark:text-white mb-4">Quick Actions</h2>
            <div class="space-y-2.5">
                <a href="{{ route('admin.campaigns.create') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all
                          bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-300/30 hover:-translate-y-0.5 hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Campaign
                </a>
                <a href="{{ route('admin.templates.create') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all
                          bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white hover:-translate-y-0.5 hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    New Template
                </a>
                <a href="{{ route('admin.email-lists.import') }}"
                   class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all
                          bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/70 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import List
                </a>
            </div>
        </div>

        {{-- Top Performing Lists --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-[15px] font-bold text-slate-900 dark:text-white">Top Lists</h2>
                <a href="{{ route('admin.email-lists.index') }}"
                   class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition">
                    View all →
                </a>
            </div>
            <div class="space-y-4">
                @php
                    $lists = [
                        ['name' => 'Newsletter Subscribers', 'count' => '3,421', 'pct' => 85],
                        ['name' => 'Product Updates',        'count' => '2,108', 'pct' => 65],
                        ['name' => 'Flash Sale Alerts',      'count' => '1,884', 'pct' => 55],
                        ['name' => 'VIP Customers',          'count' => '912',   'pct' => 30],
                    ];
                @endphp
                @foreach($lists as $list)
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate mr-2">{{ $list['name'] }}</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0 font-medium">{{ $list['count'] }}</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="bg-brand-500 h-full rounded-full transition-all duration-700" style="width: {{ $list['pct'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@endsection
