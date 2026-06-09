@extends('layouts.admin')

@section('title', 'DNS Health Check')
@section('page-title', 'DNS Health Checker')
@section('page-subtitle', 'Validate SPF, DKIM, and DMARC records for any domain')

@section('content')

{{-- ── Search bar ────────────────────────────────────────────────────────── --}}
<div class="mb-6">
    <form method="GET" action="{{ route('admin.deliverability.dns-check') }}" class="flex gap-3 flex-wrap">
        <div class="flex-1 min-w-64">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                    </svg>
                </span>
                <input type="text" name="domain"
                       value="{{ $domain ?? '' }}"
                       placeholder="e.g. yourdomain.com"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                              bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100
                              text-sm placeholder-slate-400 dark:placeholder-slate-600
                              focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400
                              transition-all" />
            </div>
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700
                       text-white text-sm font-semibold shadow-sm shadow-brand-300/30 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Check DNS
        </button>
    </form>
</div>

@if($result)

{{-- ── Score header ─────────────────────────────────────────────────────── --}}
@php
    $spfStatus   = $result['spf']['status'];
    $dkimStatus  = $result['dkim']['status'];
    $dmarcStatus = $result['dmarc']['status'];
    $validCount  = collect([$spfStatus, $dkimStatus, $dmarcStatus])->filter(fn($s) => $s === 'valid')->count();
    $score       = (int) round($validCount / 3 * 100);
    $scoreColor  = $score === 100 ? 'emerald' : ($score >= 67 ? 'amber' : 'red');
    $scoreLabel  = $score === 100 ? 'Healthy' : ($score >= 67 ? 'Needs Attention' : 'Critical Issues');
@endphp

<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 mb-6 flex flex-wrap items-center gap-5 shadow-sm">
    {{-- Score ring --}}
    <div class="relative w-20 h-20 flex-shrink-0">
        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 80 80">
            <circle cx="40" cy="40" r="34" fill="none" stroke="#e2e8f0" stroke-width="7" class="dark:stroke-slate-700"/>
            <circle cx="40" cy="40" r="34" fill="none"
                    stroke="{{ $scoreColor === 'emerald' ? '#10b981' : ($scoreColor === 'amber' ? '#f59e0b' : '#ef4444') }}"
                    stroke-width="7" stroke-linecap="round"
                    stroke-dasharray="{{ round(2 * 3.14159 * 34) }}"
                    stroke-dashoffset="{{ round(2 * 3.14159 * 34 * (1 - $score / 100)) }}"/>
        </svg>
        <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-slate-800 dark:text-white">
            {{ $score }}%
        </span>
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $domain }}</span>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                @if($scoreColor === 'emerald') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                @elseif($scoreColor === 'amber') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                @endif">
                {{ $scoreLabel }}
            </span>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ $validCount }}/3 email authentication records configured correctly.
            @if(isset($result['checked_at']))
                Checked {{ \Carbon\Carbon::parse($result['checked_at'])->diffForHumans() }}.
            @endif
        </p>
    </div>

    {{-- Recheck button --}}
    <form method="POST" action="{{ route('admin.deliverability.dns-recheck') }}" class="flex-shrink-0">
        @csrf
        <input type="hidden" name="domain" value="{{ $domain }}">
        <button type="submit" data-no-loading
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700
                       text-sm font-medium text-slate-600 dark:text-slate-300
                       hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Recheck (bypass cache)
        </button>
    </form>
</div>

{{-- ── Record cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- SPF --}}
    @include('admin.deliverability._record-card', [
        'label'       => 'SPF',
        'status'      => $spfStatus,
        'record'      => $result['spf']['record'],
        'description' => 'Sender Policy Framework — specifies which mail servers may send email on behalf of your domain.',
        'fix'         => 'Add a TXT record to your DNS: <code class="font-mono">v=spf1 include:amazonses.com include:resend.com ~all</code>',
        'icon'        => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'extra'       => null,
    ])

    {{-- DKIM --}}
    @include('admin.deliverability._record-card', [
        'label'       => 'DKIM',
        'status'      => $dkimStatus,
        'record'      => $result['dkim']['record'],
        'description' => 'DomainKeys Identified Mail — cryptographically signs outgoing email so receivers can verify it wasn\'t altered.',
        'fix'         => 'Follow your ESP\'s DKIM setup guide (SES → Identity → DKIM settings; Resend → Domain → DKIM).',
        'icon'        => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
        'extra'       => isset($result['dkim']['selector']) ? 'Selector: <strong>' . e($result['dkim']['selector']) . '</strong>' : null,
    ])

    {{-- DMARC --}}
    @include('admin.deliverability._record-card', [
        'label'       => 'DMARC',
        'status'      => $dmarcStatus,
        'record'      => $result['dmarc']['record'],
        'description' => 'Domain-based Message Authentication — tells receivers what to do with mail that fails SPF/DKIM checks.',
        'fix'         => 'Add a TXT record at <code class="font-mono">_dmarc.yourdomain.com</code>: <code class="font-mono">v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com</code>',
        'icon'        => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
        'extra'       => isset($result['dmarc']['note']) && $result['dmarc']['note'] ? '<span class="text-amber-600 dark:text-amber-400">' . e($result['dmarc']['note']) . '</span>' : null,
    ])

</div>

{{-- ── What these records do (info box) ────────────────────────────────── --}}
<div class="bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-800/40 rounded-2xl p-5 mb-6">
    <h3 class="text-sm font-semibold text-brand-700 dark:text-brand-400 mb-3 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Why do these records matter?
    </h3>
    <ul class="space-y-1.5 text-sm text-slate-600 dark:text-slate-400">
        <li><strong class="text-slate-700 dark:text-slate-300">SPF</strong> — Prevents spammers from spoofing your domain. Without it, spam filters are more likely to reject or junk your mail.</li>
        <li><strong class="text-slate-700 dark:text-slate-300">DKIM</strong> — Adds a digital signature. Improves reputation with Gmail, Outlook, and other providers.</li>
        <li><strong class="text-slate-700 dark:text-slate-300">DMARC</strong> — Ties SPF and DKIM together and lets you enforce a policy. Required by Gmail/Yahoo for bulk senders.</li>
    </ul>
</div>

@endif

{{-- ── History table ────────────────────────────────────────────────────── --}}
@if($history->isNotEmpty())
<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Recent Checks</h2>
        <span class="text-xs text-slate-400">{{ $history->count() }} domains</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Domain</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">SPF</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">DKIM</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">DMARC</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Score</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Checked</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($history as $row)
                @php
                    $rowValid = collect([$row->spf_status, $row->dkim_status, $row->dmarc_status])
                        ->filter(fn($s) => $s === 'valid')->count();
                    $rowScore = (int) round($rowValid / 3 * 100);
                @endphp
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-200">{{ $row->domain }}</td>
                    <td class="px-4 py-3.5 text-center">@include('admin.deliverability._status-badge', ['status' => $row->spf_status])</td>
                    <td class="px-4 py-3.5 text-center">@include('admin.deliverability._status-badge', ['status' => $row->dkim_status])</td>
                    <td class="px-4 py-3.5 text-center">@include('admin.deliverability._status-badge', ['status' => $row->dmarc_status])</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="font-semibold text-sm
                            {{ $rowScore === 100 ? 'text-emerald-600 dark:text-emerald-400' :
                               ($rowScore >= 67 ? 'text-amber-600 dark:text-amber-400' : 'text-red-500 dark:text-red-400') }}">
                            {{ $rowScore }}%
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs text-slate-400 whitespace-nowrap">
                        {{ $row->checked_at->diffForHumans() }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('admin.deliverability.dns-check', ['domain' => $row->domain]) }}"
                           class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
    <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
        </svg>
    </div>
    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">No domains checked yet.</p>
    <p class="text-slate-400 dark:text-slate-600 text-xs mt-1">Enter a domain above to check its DNS health.</p>
</div>
@endif

@endsection
