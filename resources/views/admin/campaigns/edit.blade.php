@extends('layouts.admin')

@section('title', 'Edit Campaign')
@section('page-title', 'Edit Campaign')
@section('page-subtitle', 'Update campaign settings')

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
    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition truncate max-w-[160px]">{{ $campaign->name }}</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-600 dark:text-slate-300 font-medium">Edit</span>
</nav>

@if($errors->any())
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
        <p class="text-sm text-red-700 dark:text-red-400 font-semibold">Please fix the errors below</p>
        <ul class="mt-1 space-y-0.5 text-sm text-red-600 dark:text-red-400">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}"
              class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-7 space-y-6">
            @csrf
            @method('PUT')

            {{-- Campaign name --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Campaign Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       value="{{ old('name', $campaign->name) }}"
                       placeholder="e.g. Summer Sale 2025"
                       class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                              focus:outline-none focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400
                              bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200
                              placeholder-slate-400 dark:placeholder-slate-500 transition
                              @error('name') border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 @enderror">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email list --}}
            <div x-data="{ selected: '{{ old('email_group_id', $campaign->email_group_id) }}', lists: {{ $emailGroups->toJson() }} }">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Email List <span class="text-red-500">*</span>
                </label>
                <select name="email_group_id" required x-model="selected"
                        class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                               focus:outline-none focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400
                               bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition
                               @error('email_group_id') border-red-300 dark:border-red-700 @enderror">
                    <option value="">— Select an email list —</option>
                    @foreach($emailGroups as $group)
                    <option value="{{ $group->id }}" {{ old('email_group_id', $campaign->email_group_id) == $group->id ? 'selected' : '' }}>
                        {{ $group->name }} ({{ $group->pending_emails_count }} pending)
                    </option>
                    @endforeach
                </select>

                <template x-if="selected">
                    <div class="mt-2 px-3 py-2 bg-brand-50 dark:bg-brand-900/20 border border-brand-100 dark:border-brand-800 rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-brand-700 dark:text-brand-400 font-medium">
                            <span x-text="lists.find(l => l.id == selected)?.pending_emails_count ?? 0"></span>
                            pending emails will be queued
                        </p>
                    </div>
                </template>

                @error('email_group_id')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Delay --}}
            <div x-data="{ delay: {{ old('delay_minutes', $campaign->delay_minutes) }} }">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Delay Between Emails
                </label>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="range" name="delay_minutes" min="0" max="60" step="1"
                               x-model="delay"
                               class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full appearance-none cursor-pointer accent-brand-600">
                        <div class="flex justify-between text-xs text-slate-400 dark:text-slate-500 mt-1.5">
                            <span>0 min</span>
                            <span>30 min</span>
                            <span>60 min</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 rounded-xl px-3 py-2 min-w-[80px] justify-center">
                        <span class="text-lg font-bold text-brand-700 dark:text-brand-400" x-text="delay"></span>
                        <span class="text-xs text-brand-600 dark:text-brand-500 font-semibold">min</span>
                    </div>
                </div>
                @error('delay_minutes')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold
                               bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-sm shadow-brand-300/30 hover:-translate-y-0.5 hover:shadow-md transition"
                        x-data x-on:click="$el.innerHTML = '<svg class=\'w-4 h-4 animate-spin\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8v8z\'></path></svg> Saving…'; $el.disabled = true;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
                <a href="{{ route('admin.campaigns.show', $campaign) }}"
                   class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition border border-slate-200 dark:border-slate-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Right sidebar --}}
    <div>
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-5">
            <h4 class="text-sm font-bold text-amber-800 dark:text-amber-400 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Editing {{ ucfirst($campaign->status) }} Campaign
            </h4>
            <p class="text-xs text-amber-700 dark:text-amber-500 leading-relaxed">
                Changes to email list will recalculate total emails. Start the campaign after saving to begin sending.
            </p>
        </div>
    </div>

</div>

@endsection
