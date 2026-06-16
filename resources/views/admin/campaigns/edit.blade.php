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

            {{-- Delay (read-only, from settings) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Delay Between Emails
                </label>
                <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $delayLabel }}</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">— 1 email every {{ $delayLabel }} ({{ $dailyLimit }} per day)</span>
                </div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
                    Auto-set from Campaign Settings. Change <a href="{{ route('admin.settings.index') }}" class="underline hover:text-brand-500 transition">Daily Email Limit</a> to adjust.
                </p>
            </div>

            {{-- Template selection --}}
            @if($randomRotation)
                <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-400">Random rotation enabled</p>
                        <p class="text-xs mt-0.5 text-emerald-700 dark:text-emerald-500">
                            Each email will pick a random active template automatically.
                        </p>
                        {{-- Category filter for random rotation --}}
                        @if($templateCategories->isNotEmpty())
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-emerald-800 dark:text-emerald-400 mb-1">
                                Template Category <span class="font-normal text-emerald-700 dark:text-emerald-500">(optional — restrict random picks to one category)</span>
                            </label>
                            <select name="template_category_id"
                                    class="w-full px-3 py-2 text-sm border border-emerald-200 dark:border-emerald-700 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400
                                           bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition">
                                <option value="">All Categories (any active template)</option>
                                @foreach($templateCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('template_category_id', $campaign->template_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
            @else
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Email Template <span class="text-red-500">*</span>
                    </label>
                    @if($templates->isEmpty())
                        <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 rounded-xl">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700 dark:text-red-400">
                                <a href="{{ route('admin.templates.create') }}" class="underline font-semibold">Create at least one active template</a> before launching.
                            </p>
                        </div>
                    @else
                        <select name="template_id" required
                                class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                                       focus:outline-none focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400
                                       bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition
                                       @error('template_id') border-red-300 dark:border-red-700 @enderror">
                            <option value="">— Select a template —</option>
                            @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" {{ old('template_id', $campaign->template_id) == $tmpl->id ? 'selected' : '' }}>
                                {{ $tmpl->title }}{{ $tmpl->category ? ' — ' . $tmpl->category : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('template_id')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
                            Random template rotation is off — every email in this campaign will use this template.
                        </p>
                    @endif
                </div>
            @endif

            {{-- ─── Follow-up Emails ─── --}}
            @php
                $fu1HasData = $followup1 && $followup1->status === 'pending';
                $fu2HasData = $followup2 && $followup2->status === 'pending';
                $fu1Init    = old('followup1_enabled', $fu1HasData ? '1' : '0') === '1';
                $fu2Init    = old('followup2_enabled', $fu2HasData ? '1' : '0') === '1';
            @endphp
            <div x-data="{
                    fu1: {{ $fu1Init ? 'true' : 'false' }},
                    fu2: {{ $fu2Init ? 'true' : 'false' }}
                 }"
                 class="border-t border-slate-100 dark:border-slate-800 pt-6">

                <div class="flex items-center gap-2.5 mb-1">
                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Follow-up Emails</h3>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-4">
                    Sent automatically after the campaign completes — only to recipients who haven't replied.
                    @if($followup1 && $followup1->status !== 'pending')
                    <span class="text-amber-600 dark:text-amber-400 font-medium">Note: running/completed follow-ups cannot be re-configured.</span>
                    @endif
                </p>

                {{-- Follow-up 1 --}}
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition select-none
                                  {{ ($followup1 && $followup1->status !== 'pending') ? 'opacity-60 cursor-not-allowed' : '' }}">
                        <div class="relative">
                            <input type="checkbox" x-model="fu1"
                                   {{ ($followup1 && $followup1->status !== 'pending') ? 'disabled' : '' }}
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-checked:bg-violet-500 rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Enable Follow-up 1</span>
                        @if($followup1 && $followup1->status !== 'pending')
                        <span class="text-xs px-2 py-0.5 rounded-lg
                            {{ $followup1->status === 'running' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' }}">
                            {{ ucfirst($followup1->status) }}
                        </span>
                        @endif
                    </label>

                    <div x-show="fu1" x-cloak x-transition
                         class="border-t border-slate-100 dark:border-slate-800 px-4 py-4 space-y-4 bg-slate-50/50 dark:bg-slate-800/20">
                        <input type="hidden" name="followup1_enabled" :value="fu1 ? '1' : '0'">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                    Template <span class="font-normal text-slate-400">(optional)</span>
                                </label>
                                <select name="followup1_template_id"
                                        {{ ($followup1 && $followup1->status !== 'pending') ? 'disabled' : '' }}
                                        class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                                               focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400
                                               bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition
                                               disabled:opacity-60 disabled:cursor-not-allowed">
                                    <option value="">— Same as campaign (random/auto) —</option>
                                    @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" {{ old('followup1_template_id', $followup1?->template_id) == $tmpl->id ? 'selected' : '' }}>
                                        {{ $tmpl->title }}{{ $tmpl->category ? ' — ' . $tmpl->category : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                    Send After <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="followup1_days"
                                           value="{{ old('followup1_days', $followup1?->delay_days ?? 3) }}" min="1"
                                           {{ ($followup1 && $followup1->status !== 'pending') ? 'disabled' : '' }}
                                           class="w-24 px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                                                  focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400
                                                  bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition
                                                  disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">days after campaign completes</span>
                                </div>
                            </div>
                        </div>

                        {{-- Follow-up 2 (nested) --}}
                        <div class="rounded-xl border border-violet-100 dark:border-violet-900/40 overflow-hidden">
                            <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-violet-50/50 dark:hover:bg-violet-900/10 transition select-none
                                          {{ ($followup2 && $followup2->status !== 'pending') ? 'opacity-60 cursor-not-allowed' : '' }}">
                                <div class="relative">
                                    <input type="checkbox" x-model="fu2"
                                           {{ ($followup2 && $followup2->status !== 'pending') ? 'disabled' : '' }}
                                           class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-checked:bg-violet-500 rounded-full transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                </div>
                                <span class="text-sm font-semibold text-violet-700 dark:text-violet-400">Enable Follow-up 2</span>
                                @if($followup2 && $followup2->status !== 'pending')
                                <span class="text-xs px-2 py-0.5 rounded-lg
                                    {{ $followup2->status === 'running' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' }}">
                                    {{ ucfirst($followup2->status) }}
                                </span>
                                @endif
                            </label>

                            <div x-show="fu2" x-cloak x-transition
                                 class="border-t border-violet-100 dark:border-violet-900/30 px-4 py-4 space-y-4 bg-violet-50/30 dark:bg-violet-900/5">
                                <input type="hidden" name="followup2_enabled" :value="fu2 ? '1' : '0'">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                            Template <span class="font-normal text-slate-400">(optional)</span>
                                        </label>
                                        <select name="followup2_template_id"
                                                {{ ($followup2 && $followup2->status !== 'pending') ? 'disabled' : '' }}
                                                class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                                                       focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400
                                                       bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition
                                                       disabled:opacity-60 disabled:cursor-not-allowed">
                                            <option value="">— Same as campaign (random/auto) —</option>
                                            @foreach($templates as $tmpl)
                                            <option value="{{ $tmpl->id }}" {{ old('followup2_template_id', $followup2?->template_id) == $tmpl->id ? 'selected' : '' }}>
                                                {{ $tmpl->title }}{{ $tmpl->category ? ' — ' . $tmpl->category : '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                                            Send After <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="followup2_days"
                                                   value="{{ old('followup2_days', $followup2?->delay_days ?? 3) }}" min="1"
                                                   {{ ($followup2 && $followup2->status !== 'pending') ? 'disabled' : '' }}
                                                   class="w-24 px-3 py-2 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                                                          focus:outline-none focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400
                                                          bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 transition
                                                          disabled:opacity-60 disabled:cursor-not-allowed">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">days after Follow-up 1 completes</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
