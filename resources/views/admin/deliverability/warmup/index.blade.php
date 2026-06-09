@extends('layouts.admin')

@section('title', 'Email Warmup Planner')
@section('page-title', 'Email Warmup Planner')
@section('page-subtitle', 'Gradually increase sending volume to build domain reputation')

@section('content')

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Header bar ────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ $plans->count() }} {{ Str::plural('plan', $plans->count()) }} configured
        </p>
    </div>
    <a href="{{ route('admin.warmup.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700
              text-white text-sm font-semibold shadow-sm shadow-brand-300/30 transition-all active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Warmup Plan
    </a>
</div>

@if($plans->isEmpty())
<div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-16 text-center shadow-sm">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </div>
    <p class="text-slate-600 dark:text-slate-400 font-medium mb-1">No warmup plans yet</p>
    <p class="text-slate-400 dark:text-slate-600 text-sm mb-5">Create your first plan to start building domain reputation gradually.</p>
    <a href="{{ route('admin.warmup.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition-all">
        Create First Plan
    </a>
</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($plans as $plan)
    @php
        $statusColors = [
            'pending'   => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
            'active'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'paused'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'failed'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        ];
        $progressColor = $plan->status === 'active' ? 'bg-emerald-500' : ($plan->status === 'paused' ? 'bg-amber-400' : 'bg-slate-300 dark:bg-slate-600');
        $progress = $plan->progressPercent();
    @endphp
    <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow card-hover">
        {{-- Card header --}}
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $plan->name }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">{{ $plan->domain }}</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold flex-shrink-0 {{ $statusColors[$plan->status] ?? '' }}">
                {{ ucfirst($plan->status) }}
            </span>
        </div>

        {{-- Progress bar --}}
        <div class="mb-4">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1.5">
                <span>Day {{ $plan->current_day }} / {{ \App\Services\WarmupScheduleService::MAX_DAY }}</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-2 rounded-full transition-all duration-500 {{ $progressColor }}" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        {{-- Key metrics --}}
        <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="text-center">
                <p class="text-lg font-bold text-slate-800 dark:text-white">{{ number_format($plan->daily_limit) }}</p>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Daily limit</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-slate-800 dark:text-white">{{ number_format($plan->totalSent()) }}</p>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Total sent</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-slate-800 dark:text-white">{{ ucfirst($plan->provider) }}</p>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider">Provider</p>
            </div>
        </div>

        @if($plan->pause_reason)
        <div class="mb-3 flex items-start gap-2 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2">
            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ Str::limit($plan->pause_reason, 60) }}
        </div>
        @endif

        <a href="{{ route('admin.warmup.show', $plan) }}"
           class="block w-full text-center py-2 rounded-xl border border-slate-200 dark:border-slate-700
                  text-sm font-medium text-slate-600 dark:text-slate-300
                  hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            View Details →
        </a>
    </div>
    @endforeach
</div>
@endif

@endsection
