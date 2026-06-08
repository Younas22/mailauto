@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-subtitle', 'Manage your application preferences and configuration')

@section('content')

{{-- ─── Alerts ───────────────────────────────────────────────────────── --}}
@foreach(['general','email','ses','resend','campaign','log','security'] as $sec)
    @if(session("success_{$sec}"))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 rounded-xl px-4 py-3 text-sm font-medium shadow-sm">
        <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session("success_{$sec}") }}
        <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif
@endforeach

@if($errors->any())
<div x-data="{ show: true }" x-show="show"
     class="mb-5 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-700 dark:text-red-300 rounded-xl px-4 py-3 text-sm shadow-sm">
    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
        <p class="font-semibold mb-1">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
@endif

{{-- ─── Main Layout ──────────────────────────────────────────────────── --}}
<div x-data="{
        activeTab: '{{ old('_section', 'general') }}',
        tabs: [
            { id: 'general',  label: 'General',       icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
            { id: 'email',    label: 'Email / SMTP',  icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
            { id: 'ses',      label: 'Amazon SES',    icon: 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z' },
            { id: 'resend',   label: 'Resend',        icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
            { id: 'campaign', label: 'Campaigns',     icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z' },
            { id: 'log',      label: 'Logs',          icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { id: 'security', label: 'Security',      icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
            { id: 'system',   label: 'System Info',   icon: 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01' },
        ]
     }" class="flex flex-col lg:flex-row gap-6">

    {{-- ── LEFT: Tab Navigation ──────────────────────────────────────── --}}
    <aside class="lg:w-56 xl:w-64 flex-shrink-0">

        {{-- Mobile: horizontal scrollable tabs --}}
        <div class="lg:hidden bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-2 flex gap-1 overflow-x-auto no-scrollbar mb-1">
            <template x-for="tab in tabs" :key="tab.id">
                <button @click="activeTab = tab.id"
                        :class="activeTab === tab.id
                            ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 font-semibold'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs whitespace-nowrap transition-all duration-150 flex-shrink-0">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon"/>
                    </svg>
                    <span x-text="tab.label"></span>
                </button>
            </template>
        </div>

        {{-- Desktop: vertical pill nav --}}
        <nav class="hidden lg:block bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-2 space-y-0.5 sticky top-24">
            <template x-for="tab in tabs" :key="tab.id">
                <button @click="activeTab = tab.id"
                        :class="activeTab === tab.id
                            ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 font-semibold shadow-sm'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm transition-all duration-150">
                    <span :class="activeTab === tab.id ? 'bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                          class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors duration-150">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon"/>
                        </svg>
                    </span>
                    <span x-text="tab.label"></span>
                    <svg x-show="activeTab === tab.id" class="ml-auto w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </template>
        </nav>
    </aside>

    {{-- ── RIGHT: Content Panels ────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-5">

        {{-- ══════════════════════════════════════════════════
             1. GENERAL SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'general'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_section" value="general">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    {{-- Card header --}}
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">General Settings</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Site identity and regional preferences</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Row 1: Site Name + Tagline --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Website Name <span class="text-red-400">*</span></label>
                                <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? 'MailAuto') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 dark:focus:border-brand-500 transition outline-none"
                                       placeholder="My Application">
                                @error('site_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Website Tagline</label>
                                <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 dark:focus:border-brand-500 transition outline-none"
                                       placeholder="Email Automation Made Simple">
                            </div>
                        </div>

                        {{-- Row 2: Admin Email --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Admin Email <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="email" name="admin_email" value="{{ old('admin_email', $settings['admin_email'] ?? '') }}"
                                       class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 dark:focus:border-brand-500 transition outline-none"
                                       placeholder="admin@example.com">
                            </div>
                            @error('admin_email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Row 3: Timezone + Date Format --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Timezone <span class="text-red-400">*</span></label>
                                <select name="timezone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 dark:focus:border-brand-500 transition outline-none">
                                    @php $currentTz = old('timezone', $settings['timezone'] ?? 'UTC'); @endphp
                                    @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ $currentTz === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Date Format <span class="text-red-400">*</span></label>
                                <select name="date_format" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 dark:focus:border-brand-500 transition outline-none">
                                    @php
                                        $formats = ['Y-m-d' => 'YYYY-MM-DD', 'd/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY', 'd-m-Y' => 'DD-MM-YYYY', 'M j, Y' => 'Month D, YYYY'];
                                        $currentFmt = old('date_format', $settings['date_format'] ?? 'Y-m-d');
                                    @endphp
                                    @foreach($formats as $val => $label)
                                    <option value="{{ $val }}" {{ $currentFmt === $val ? 'selected' : '' }}>{{ $label }} ({{ date($val) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Row 4: Logo + Favicon --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Logo --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Site Logo</label>
                                @if(!empty($settings['logo']))
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ asset('storage/'.$settings['logo']) }}" alt="Logo" class="h-10 rounded-lg border border-slate-200 dark:border-slate-700 object-contain bg-white dark:bg-slate-800 p-1">
                                    <span class="text-xs text-slate-400">Current logo</span>
                                </div>
                                @endif
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="flex-1 flex items-center gap-2.5 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 hover:border-brand-300 dark:hover:border-brand-600 transition-colors bg-slate-50 dark:bg-slate-800/40">
                                        <svg class="w-5 h-5 text-slate-400 group-hover:text-brand-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">Upload Logo</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500">PNG, SVG, JPG — max 2MB</p>
                                        </div>
                                    </div>
                                    <input type="file" name="logo" accept="image/*" class="sr-only">
                                </label>
                                @error('logo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Favicon --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Favicon</label>
                                @if(!empty($settings['favicon']))
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ asset('storage/'.$settings['favicon']) }}" alt="Favicon" class="h-8 w-8 rounded border border-slate-200 dark:border-slate-700 object-contain bg-white dark:bg-slate-800 p-0.5">
                                    <span class="text-xs text-slate-400">Current favicon</span>
                                </div>
                                @endif
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="flex-1 flex items-center gap-2.5 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 hover:border-brand-300 dark:hover:border-brand-600 transition-colors bg-slate-50 dark:bg-slate-800/40">
                                        <svg class="w-5 h-5 text-slate-400 group-hover:text-brand-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 0M5 3a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2M5 3l0 2m14-2l0 2M9 9h6m-6 4h6m-3 4h.01"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-slate-600 dark:text-slate-400 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">Upload Favicon</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500">ICO, PNG, SVG — max 512KB</p>
                                        </div>
                                    </div>
                                    <input type="file" name="favicon" accept="image/*,.ico" class="sr-only">
                                </label>
                                @error('favicon')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Card footer --}}
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold rounded-xl transition-all duration-150 shadow-sm shadow-brand-300/30 dark:shadow-brand-900/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save General Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════
             2. EMAIL / SMTP SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'email'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Active Campaign Provider — instant switcher + active-provider test --}}
            <div x-data="providerSwitcher('{{ $settings['active_email_provider'] ?? 'ses' }}', {{ ($settings['email_fallback_enabled'] ?? '0') === '1' ? 'true' : 'false' }}, '{{ $settings['backup_email_provider'] ?? '' }}')" class="mb-5">
                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Active Campaign Provider</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Pick which provider sends your campaign emails — switches instantly, no .env changes or page reload needed</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Provider buttons (only one active at a time) --}}
                        <div class="flex flex-wrap gap-3">
                            <button type="button" @click="setActive('ses')" :disabled="switching"
                                    :class="active === 'ses'
                                        ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 ring-2 ring-emerald-200 dark:ring-emerald-800/40'
                                        : 'border-slate-200 dark:border-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600'"
                                    class="flex items-center gap-2.5 px-5 py-3 rounded-xl border transition disabled:opacity-60 disabled:cursor-wait">
                                <span class="text-base leading-none">🟢</span>
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Amazon SES</span>
                                <span x-show="active === 'ses'" x-cloak
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            </button>

                            <button type="button" @click="setActive('resend')" :disabled="switching"
                                    :class="active === 'resend'
                                        ? 'border-violet-400 bg-violet-50 dark:bg-violet-900/20 ring-2 ring-violet-200 dark:ring-violet-800/40'
                                        : 'border-slate-200 dark:border-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600'"
                                    class="flex items-center gap-2.5 px-5 py-3 rounded-xl border transition disabled:opacity-60 disabled:cursor-wait">
                                <span class="text-base leading-none">🟣</span>
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Resend</span>
                                <span x-show="active === 'resend'" x-cloak
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span> Active
                                </span>
                            </button>

                            <span x-show="switching" x-cloak class="inline-flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Switching…
                            </span>
                        </div>

                        <div x-show="switchMessage" x-cloak x-transition
                             :class="switchSuccess ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800/40' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40'"
                             class="text-xs font-medium px-3 py-2 rounded-lg border" x-text="switchMessage"></div>

                        {{-- Send a test email through whichever provider is currently active --}}
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 bg-slate-50/50 dark:bg-slate-800/20">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-3">
                                Send Test Email via Active Provider
                                <span class="font-normal text-slate-400 dark:text-slate-500">— uses <span x-text="active === 'ses' ? 'Amazon SES' : 'Resend'" class="font-semibold"></span> directly, bypassing the configured mail driver</span>
                            </p>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="email" x-model="testEmail" placeholder="recipient@example.com"
                                       class="flex-1 px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                <button type="button" @click="sendTest()" :disabled="testLoading"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-all duration-150 flex-shrink-0">
                                    <svg x-show="testLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="!testLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span x-text="testLoading ? 'Sending…' : 'Send Test'"></span>
                                </button>
                            </div>
                            <div x-show="testResult" x-cloak x-transition
                                 :class="testSuccess ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800/40' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40'"
                                 class="mt-3 text-xs font-medium px-3 py-2 rounded-lg border" x-text="testResult"></div>
                        </div>

                        {{-- Automatic fallback — retry through a backup provider when the primary send fails --}}
                        <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 bg-slate-50/50 dark:bg-slate-800/20">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Automatic Provider Fallback</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">If the active provider fails to send, automatically retry the same email through a backup provider.</p>
                                </div>
                                <button type="button" role="switch" :aria-checked="fallbackEnabled.toString()" @click="fallbackEnabled = !fallbackEnabled"
                                        :class="fallbackEnabled ? 'bg-brand-600' : 'bg-slate-300 dark:bg-slate-700'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors">
                                    <span :class="fallbackEnabled ? 'translate-x-6' : 'translate-x-1'"
                                          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                </button>
                            </div>

                            <div x-show="fallbackEnabled" x-cloak x-transition class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                                <label class="text-xs font-medium text-slate-500 dark:text-slate-400 flex-shrink-0">Backup provider</label>
                                <div class="relative flex-1 sm:flex-none sm:w-48">
                                    <select x-model="backupProvider"
                                            class="appearance-none w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none cursor-pointer">
                                        <option value="">Select a provider…</option>
                                        <option value="ses"    :disabled="active === 'ses'">Amazon SES</option>
                                        <option value="resend" :disabled="active === 'resend'">Resend</option>
                                    </select>
                                </div>
                                <button type="button" @click="saveFallback()" :disabled="fallbackSaving"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-all duration-150 flex-shrink-0">
                                    <span x-text="fallbackSaving ? 'Saving…' : 'Save'"></span>
                                </button>
                            </div>
                            <div x-show="!fallbackEnabled" x-cloak class="flex justify-end">
                                <button type="button" @click="saveFallback()" :disabled="fallbackSaving"
                                        class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                                    <span x-text="fallbackSaving ? 'Saving…' : 'Save'"></span>
                                </button>
                            </div>

                            <div x-show="fallbackMessage" x-cloak x-transition
                                 :class="fallbackSuccess ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800/40' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40'"
                                 class="mt-3 text-xs font-medium px-3 py-2 rounded-lg border" x-text="fallbackMessage"></div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.email') }}">
                @csrf
                <input type="hidden" name="_section" value="email">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Email / SMTP Settings</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Configure your outgoing mail server</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Mail Driver --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Mail Driver <span class="text-red-400">*</span></label>
                            <select name="mail_driver" class="w-full sm:w-48 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                @php $driver = old('mail_driver', $settings['mail_driver'] ?? 'smtp'); @endphp
                                <option value="smtp"     {{ $driver==='smtp'     ? 'selected':'' }}>SMTP</option>
                                <option value="ses"      {{ $driver==='ses'      ? 'selected':'' }}>Amazon SES</option>
                                <option value="resend"   {{ $driver==='resend'   ? 'selected':'' }}>Resend</option>
                                <option value="sendmail" {{ $driver==='sendmail' ? 'selected':'' }}>Sendmail</option>
                                <option value="log"      {{ $driver==='log'      ? 'selected':'' }}>Log (Testing)</option>
                            </select>
                        </div>

                        {{-- SMTP Host + Port --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">SMTP Host</label>
                                <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="smtp.gmail.com">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">SMTP Port</label>
                                <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="587">
                            </div>
                        </div>

                        {{-- Username + Password --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">SMTP Username</label>
                                <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="user@gmail.com">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">SMTP Password</label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="smtp_password"
                                           value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}"
                                           class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                           placeholder="••••••••">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Encryption --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">Encryption Type</label>
                            <div class="flex flex-wrap gap-3">
                                @php $enc = old('smtp_encryption', $settings['smtp_encryption'] ?? 'tls'); @endphp
                                @foreach(['tls' => 'TLS', 'ssl' => 'SSL', 'starttls' => 'STARTTLS', 'none' => 'None'] as $val => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="smtp_encryption" value="{{ $val }}" {{ $enc === $val ? 'checked' : '' }}
                                           class="w-4 h-4 text-brand-600 border-slate-300 focus:ring-brand-300">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- From Email + From Name --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">From Email <span class="text-red-400">*</span></label>
                                <input type="email" name="mail_from_email" value="{{ old('mail_from_email', $settings['mail_from_email'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="no-reply@example.com">
                                @error('mail_from_email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">From Name <span class="text-red-400">*</span></label>
                                <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'MailAuto') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="MailAuto">
                                @error('mail_from_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Test Email --}}
                        <div x-data="testEmail()" class="rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-4 bg-slate-50/50 dark:bg-slate-800/20">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-3">Send Test Email</p>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="email" x-model="email" placeholder="recipient@example.com"
                                       class="flex-1 px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                <button type="button" @click="send()" :disabled="loading"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-all duration-150 flex-shrink-0">
                                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <span x-text="loading ? 'Sending…' : 'Send Test'"></span>
                                </button>
                            </div>
                            <div x-show="result" x-transition
                                 :class="success ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800/40' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40'"
                                 class="mt-3 text-xs font-medium px-3 py-2 rounded-lg border" x-text="result"></div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Email Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════
             3. AMAZON SES SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'ses'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form method="POST" action="{{ route('admin.settings.ses') }}">
                @csrf
                <input type="hidden" name="_section" value="ses">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Amazon SES Settings</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">AWS Simple Email Service configuration</p>
                        </div>
                        {{-- Connection Status --}}
                        <div x-data="sesStatus()" class="flex items-center gap-2">
                            <span :class="connected ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-colors">
                                <span :class="connected ? 'bg-emerald-500' : 'bg-slate-400'" class="w-1.5 h-1.5 rounded-full transition-colors"></span>
                                <span x-text="connected ? 'Connected' : 'Not Verified'"></span>
                            </span>
                            <button type="button" @click="verify()" :disabled="loading"
                                    class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline disabled:opacity-50 transition">
                                <span x-text="loading ? 'Checking…' : 'Verify'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Access Key + Secret Key --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">AWS Access Key</label>
                                <input type="text" name="ses_access_key" value="{{ old('ses_access_key', $settings['ses_access_key'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="AKIAIOSFODNN7EXAMPLE">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">AWS Secret Key</label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="ses_secret_key"
                                           value="{{ old('ses_secret_key', $settings['ses_secret_key'] ?? '') }}"
                                           class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                           placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Region --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">AWS Region</label>
                            <select name="ses_region" class="w-full sm:w-64 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                @php $region = old('ses_region', $settings['ses_region'] ?? 'us-east-1'); @endphp
                                @foreach(['us-east-1'=>'US East (N. Virginia)','us-east-2'=>'US East (Ohio)','us-west-2'=>'US West (Oregon)','eu-west-1'=>'EU (Ireland)','eu-central-1'=>'EU (Frankfurt)','ap-southeast-1'=>'Asia Pacific (Singapore)','ap-northeast-1'=>'Asia Pacific (Tokyo)'] as $val => $label)
                                <option value="{{ $val }}" {{ $region === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Verified Domain + Sender Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Verified Domain</label>
                                <input type="text" name="ses_verified_domain" value="{{ old('ses_verified_domain', $settings['ses_verified_domain'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="example.com">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Sender Email</label>
                                <input type="email" name="ses_sender_email" value="{{ old('ses_sender_email', $settings['ses_sender_email'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="sender@example.com">
                            </div>
                        </div>

                        {{-- Info banner --}}
                        <div class="flex items-start gap-3 bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800/40 rounded-xl px-4 py-3">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-amber-700 dark:text-amber-400">Select <span class="font-semibold">Amazon SES</span> as the mail driver on the Email / SMTP tab to make this the active sending provider — no <code class="bg-amber-100 dark:bg-amber-900/40 px-1 rounded font-mono">.env</code> changes needed.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save SES Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════
             RESEND SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'resend'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form method="POST" action="{{ route('admin.settings.resend') }}">
                @csrf
                <input type="hidden" name="_section" value="resend">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Resend Settings</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Resend transactional email API configuration</p>
                        </div>
                        {{-- Connection Status --}}
                        <div x-data="resendStatus()" class="flex items-center gap-2">
                            <span :class="connected ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-colors">
                                <span :class="connected ? 'bg-emerald-500' : 'bg-slate-400'" class="w-1.5 h-1.5 rounded-full transition-colors"></span>
                                <span x-text="connected ? 'Connected' : 'Not Verified'"></span>
                            </span>
                            <button type="button" @click="verify()" :disabled="loading"
                                    class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline disabled:opacity-50 transition">
                                <span x-text="loading ? 'Checking…' : 'Verify'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- API Key --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Resend API Key</label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" name="resend_api_key"
                                       value="{{ old('resend_api_key', $settings['resend_api_key'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm font-mono text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="re_xxxxxxxxxxxxxxxxxxxxxxxx">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Verified Domain + Sender Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Verified Domain</label>
                                <input type="text" name="resend_domain" value="{{ old('resend_domain', $settings['resend_domain'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="example.com">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Sender Email</label>
                                <input type="email" name="resend_sender_email" value="{{ old('resend_sender_email', $settings['resend_sender_email'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="sender@example.com">
                            </div>
                        </div>

                        {{-- Info banner --}}
                        <div class="flex items-start gap-3 bg-violet-50 dark:bg-violet-900/15 border border-violet-200 dark:border-violet-800/40 rounded-xl px-4 py-3">
                            <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-violet-700 dark:text-violet-400">Select <span class="font-semibold">Resend</span> as the mail driver on the Email / SMTP tab to make this the active sending provider — no <code class="bg-violet-100 dark:bg-violet-900/40 px-1 rounded font-mono">.env</code> changes needed.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Resend Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════
             4. CAMPAIGN SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'campaign'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form method="POST" action="{{ route('admin.settings.campaign') }}">
                @csrf
                <input type="hidden" name="_section" value="campaign">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Campaign Settings</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Default behaviour for email campaigns</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Sending limits --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Delay Between Emails <span class="text-slate-400 font-normal">(seconds)</span></label>
                                <input type="number" name="campaign_delay" min="1" max="3600"
                                       value="{{ old('campaign_delay', $settings['campaign_delay'] ?? '5') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                @error('campaign_delay')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Daily Email Limit</label>
                                <input type="number" name="campaign_daily_limit" min="1"
                                       value="{{ old('campaign_daily_limit', $settings['campaign_daily_limit'] ?? '500') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                @error('campaign_daily_limit')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Max Emails Per Campaign</label>
                                <input type="number" name="campaign_max_per_campaign" min="1"
                                       value="{{ old('campaign_max_per_campaign', $settings['campaign_max_per_campaign'] ?? '10000') }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                @error('campaign_max_per_campaign')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Toggle options --}}
                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Options</p>

                            @php
                                $toggles = [
                                    ['name' => 'campaign_random_rotation', 'label' => 'Enable Random Template Rotation', 'desc' => 'Randomly rotate between available templates when sending campaign emails.', 'color' => 'violet'],
                                    ['name' => 'campaign_retry_failed',    'label' => 'Enable Retry Failed Emails',       'desc' => 'Automatically retry emails that failed to send due to transient errors.', 'color' => 'violet'],
                                ];
                            @endphp

                            @foreach($toggles as $toggle)
                            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 cursor-pointer hover:border-slate-200 dark:hover:border-slate-700 transition-colors group">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $toggle['label'] }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $toggle['desc'] }}</p>
                                </div>
                                <div class="relative flex-shrink-0 mt-0.5" x-data="{ on: {{ ($settings[$toggle['name']] ?? '0') == '1' ? 'true' : 'false' }} }">
                                    <input type="hidden" name="{{ $toggle['name'] }}" :value="on ? '1' : '0'">
                                    <button type="button" @click="on = !on"
                                            :class="on ? 'bg-brand-600' : 'bg-slate-200 dark:bg-slate-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-1">
                                        <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                                              class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                                    </button>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Campaign Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════
             5. LOG SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'log'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form method="POST" action="{{ route('admin.settings.log') }}">
                @csrf
                <input type="hidden" name="_section" value="log">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Log Settings</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Control email log retention and cleanup</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Toggles --}}
                        @php
                            $logToggles = [
                                ['name' => 'log_enabled',     'label' => 'Enable Email Logs',      'desc' => 'Record a log entry for every email sent, failed, or bounced.'],
                                ['name' => 'log_auto_delete', 'label' => 'Auto Delete Old Logs',   'desc' => 'Automatically purge logs older than the retention period.'],
                            ];
                        @endphp

                        <div class="space-y-3">
                            @foreach($logToggles as $t)
                            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 cursor-pointer hover:border-slate-200 dark:hover:border-slate-700 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $t['label'] }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $t['desc'] }}</p>
                                </div>
                                <div class="relative flex-shrink-0 mt-0.5" x-data="{ on: {{ ($settings[$t['name']] ?? '1') == '1' ? 'true' : 'false' }} }">
                                    <input type="hidden" name="{{ $t['name'] }}" :value="on ? '1' : '0'">
                                    <button type="button" @click="on = !on"
                                            :class="on ? 'bg-teal-600' : 'bg-slate-200 dark:bg-slate-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-300 focus:ring-offset-1">
                                        <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                                              class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                                    </button>
                                </div>
                            </label>
                            @endforeach
                        </div>

                        {{-- Retention Days --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Log Retention Period <span class="text-slate-400 font-normal">(days)</span></label>
                            <div class="flex items-center gap-3">
                                <input type="number" name="log_retention_days" min="1" max="365"
                                       value="{{ old('log_retention_days', $settings['log_retention_days'] ?? '30') }}"
                                       class="w-32 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none">
                                <span class="text-xs text-slate-400">days (1 – 365)</span>
                            </div>
                            @error('log_retention_days')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Log Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════
             6. SECURITY SETTINGS
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'security'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">

            {{-- Change Password --}}
            <form method="POST" action="{{ route('admin.settings.security') }}">
                @csrf
                <input type="hidden" name="_section" value="security">

                <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Change Password</h2>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Update your admin account password</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Current Password</label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" name="current_password"
                                       class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                       placeholder="••••••••">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            @error('current_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">New Password</label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="password"
                                           class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                           placeholder="Min 8 chars, mixed case + numbers">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Confirm New Password</label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation"
                                           class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-brand-300/60 focus:border-brand-400 transition outline-none"
                                           placeholder="••••••••">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- 2FA (future) --}}
                        <div class="pt-2">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-3">Advanced Security</p>
                            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 cursor-pointer hover:border-slate-200 dark:hover:border-slate-700 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Enable Two-Factor Authentication</p>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wide">Soon</span>
                                    </div>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Add an extra layer of security with TOTP-based 2FA.</p>
                                </div>
                                <div class="relative flex-shrink-0 mt-0.5 opacity-50 pointer-events-none" x-data="{ on: {{ ($settings['enable_2fa'] ?? '0') == '1' ? 'true' : 'false' }} }">
                                    <input type="hidden" name="enable_2fa" :value="on ? '1' : '0'">
                                    <button type="button" :class="on ? 'bg-brand-600' : 'bg-slate-200 dark:bg-slate-700'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200">
                                        <span :class="on ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                                    </button>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Update Security Settings
                        </button>
                    </div>
                </div>
            </form>

            {{-- Force Logout --}}
            <div class="bg-white dark:bg-[#111827] rounded-2xl border border-red-100 dark:border-red-900/30 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-red-100 dark:border-red-900/30 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Force Logout All Devices</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Invalidate all active sessions except this one</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.settings.logout-devices') }}" x-data="{ confirm: false }">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/15 border border-red-200 dark:border-red-800/40 rounded-xl px-4 py-3">
                            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs text-red-700 dark:text-red-400">This will immediately log out all other active sessions. You will remain logged in on this device.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Confirm with Your Password</label>
                            <input type="password" name="logout_password" placeholder="Enter your password to confirm"
                                   class="w-full sm:w-80 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-red-300/60 focus:border-red-400 transition outline-none">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="confirm_logout" id="confirm_logout" value="1" class="w-4 h-4 rounded border-slate-300 text-red-600 focus:ring-red-300">
                            <label for="confirm_logout" class="text-sm text-slate-600 dark:text-slate-400 cursor-pointer">I understand this will end all other active sessions</label>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-red-50/50 dark:bg-red-900/10 border-t border-red-100 dark:border-red-900/30 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Force Logout All Devices
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             7. SYSTEM INFORMATION
        ══════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'system'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            @php
                use Illuminate\Support\Facades\DB;

                $dbStatus   = 'Unknown';
                $dbColor    = 'slate';
                try { DB::connection()->getPdo(); $dbStatus = 'Connected'; $dbColor = 'emerald'; }
                catch (\Exception $e) { $dbStatus = 'Error'; $dbColor = 'red'; }

                $queueStatus = 'Unknown';
                $queueColor  = 'slate';
                try {
                    $pending = DB::table('jobs')->count();
                    $queueStatus = $pending === 0 ? 'Idle (0 jobs)' : "Running ({$pending} pending)";
                    $queueColor  = $pending === 0 ? 'emerald' : 'amber';
                } catch (\Exception $e) {
                    $queueStatus = 'Queue table missing';
                    $queueColor  = 'red';
                }

                $sysCards = [
                    ['label' => 'Laravel Version',    'value' => app()->version(),               'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',              'color' => 'brand'],
                    ['label' => 'PHP Version',         'value' => PHP_VERSION,                    'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',              'color' => 'violet'],
                    ['label' => 'Server Time',         'value' => now()->format('Y-m-d H:i:s T'), 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',        'color' => 'blue'],
                    ['label' => 'App Environment',     'value' => ucfirst(app()->environment()),  'icon' => 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z', 'color' => 'amber'],
                    ['label' => 'Queue Status',        'value' => $queueStatus,                   'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => $queueColor],
                    ['label' => 'Database Status',     'value' => $dbStatus,                      'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4', 'color' => $dbColor],
                ];
            @endphp

            <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">System Information</h2>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Read-only server and environment details</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($sysCards as $card)
                    @php
                        $colorMap = [
                            'brand'   => ['bg' => 'bg-brand-50 dark:bg-brand-900/20',   'icon' => 'text-brand-600 dark:text-brand-400',   'iconBg' => 'bg-brand-100 dark:bg-brand-900/40'],
                            'violet'  => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'icon' => 'text-violet-600 dark:text-violet-400', 'iconBg' => 'bg-violet-100 dark:bg-violet-900/40'],
                            'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',     'icon' => 'text-blue-600 dark:text-blue-400',     'iconBg' => 'bg-blue-100 dark:bg-blue-900/40'],
                            'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/20',   'icon' => 'text-amber-600 dark:text-amber-400',   'iconBg' => 'bg-amber-100 dark:bg-amber-900/40'],
                            'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/40'],
                            'red'     => ['bg' => 'bg-red-50 dark:bg-red-900/20',       'icon' => 'text-red-600 dark:text-red-400',       'iconBg' => 'bg-red-100 dark:bg-red-900/40'],
                            'slate'   => ['bg' => 'bg-slate-50 dark:bg-slate-800/50',   'icon' => 'text-slate-600 dark:text-slate-400',   'iconBg' => 'bg-slate-100 dark:bg-slate-800'],
                        ];
                        $c = $colorMap[$card['color']] ?? $colorMap['slate'];
                    @endphp
                    <div class="{{ $c['bg'] }} rounded-xl p-4 flex items-start gap-3">
                        <div class="{{ $c['iconBg'] }} w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $card['label'] }}</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 truncate">{{ $card['value'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Storage info --}}
                <div class="mx-6 mb-6 rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Runtime Details</p>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php
                            $runtimeRows = [
                                ['key' => 'App Name',      'val' => config('app.name')],
                                ['key' => 'Debug Mode',    'val' => config('app.debug') ? 'Enabled' : 'Disabled'],
                                ['key' => 'Cache Driver',  'val' => ucfirst(config('cache.default'))],
                                ['key' => 'Queue Driver',  'val' => ucfirst(config('queue.default'))],
                                ['key' => 'Mail Driver',   'val' => ucfirst(config('mail.default'))],
                                ['key' => 'Session Driver','val' => ucfirst(config('session.driver'))],
                            ];
                        @endphp
                        @foreach($runtimeRows as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $row['key'] }}</span>
                            <span class="text-slate-700 dark:text-slate-300 font-semibold font-mono">{{ $row['val'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end right panel --}}
</div>{{-- end flex layout --}}

{{-- ─── Mobile sticky save hint ──────────────────────────────────── --}}
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 lg:hidden z-40 pointer-events-none">
    <div class="bg-slate-900/80 dark:bg-slate-700/90 backdrop-blur-sm text-white text-xs font-semibold px-4 py-2 rounded-full shadow-lg opacity-70">
        Scroll up to save settings
    </div>
</div>

@endsection

@push('scripts')
<script>
function providerSwitcher(initial, fallbackEnabled, backupProvider) {
    return {
        active: initial,
        switching: false,
        switchMessage: '',
        switchSuccess: false,
        testEmail: '',
        testLoading: false,
        testResult: '',
        testSuccess: false,
        fallbackEnabled: fallbackEnabled,
        backupProvider: backupProvider,
        fallbackSaving: false,
        fallbackMessage: '',
        fallbackSuccess: false,
        async saveFallback() {
            if (this.fallbackEnabled && !this.backupProvider) {
                this.fallbackSuccess = false;
                this.fallbackMessage = 'Choose a backup provider first.';
                return;
            }
            this.fallbackSaving = true;
            this.fallbackMessage = '';
            try {
                const fd = new FormData();
                fd.append('email_fallback_enabled', this.fallbackEnabled ? '1' : '0');
                fd.append('backup_email_provider', this.backupProvider || '');
                fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
                const res  = await fetch('{{ route('admin.settings.provider.fallback') }}', {
                    method: 'POST', body: fd, headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                this.fallbackSuccess = json.success;
                this.fallbackMessage = json.message;
            } catch (e) {
                this.fallbackSuccess = false;
                this.fallbackMessage = 'Network error — check console.';
            } finally {
                this.fallbackSaving = false;
            }
        },
        async setActive(provider) {
            if (this.active === provider || this.switching) return;
            this.switching     = true;
            this.switchMessage = '';
            try {
                const fd = new FormData();
                fd.append('active_email_provider', provider);
                fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
                const res  = await fetch('{{ route('admin.settings.provider') }}', {
                    method: 'POST', body: fd, headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                this.switchSuccess = json.success;
                this.switchMessage = json.message;
                if (json.success) {
                    this.active = provider;
                    if (this.backupProvider === provider) this.backupProvider = '';
                }
            } catch (e) {
                this.switchSuccess = false;
                this.switchMessage = 'Network error — check console.';
            } finally {
                this.switching = false;
            }
        },
        async sendTest() {
            if (!this.testEmail) { this.testResult = 'Please enter a recipient email.'; this.testSuccess = false; return; }
            this.testLoading = true;
            this.testResult  = '';
            try {
                const fd = new FormData();
                fd.append('test_to', this.testEmail);
                fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
                const res  = await fetch('{{ route('admin.settings.provider.test') }}', { method: 'POST', body: fd });
                const json = await res.json();
                this.testSuccess = json.success;
                this.testResult  = json.message;
            } catch (e) {
                this.testSuccess = false;
                this.testResult  = 'Network error — check console.';
            } finally {
                this.testLoading = false;
            }
        }
    };
}

function testEmail() {
    return {
        email: '',
        loading: false,
        result: '',
        success: false,
        async send() {
            if (!this.email) { this.result = 'Please enter a recipient email.'; this.success = false; return; }
            this.loading = true;
            this.result  = '';
            try {
                const fd = new FormData();
                fd.append('test_to', this.email);
                fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
                const res  = await fetch('{{ route('admin.settings.email.test') }}', { method: 'POST', body: fd });
                const json = await res.json();
                this.success = json.success;
                this.result  = json.message;
            } catch (e) {
                this.success = false;
                this.result  = 'Network error — check console.';
            } finally {
                this.loading = false;
            }
        }
    };
}

function sesStatus() {
    return {
        connected: false,
        loading: false,
        async verify() {
            this.loading = true;
            try {
                const fd = new FormData();
                fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
                const res  = await fetch('{{ route('admin.settings.ses.test') }}', { method: 'POST', body: fd });
                const json = await res.json();
                this.connected = json.success;
                alert(json.message);
            } catch (e) {
                this.connected = false;
            } finally {
                this.loading = false;
            }
        }
    };
}

function resendStatus() {
    return {
        connected: false,
        loading: false,
        async verify() {
            this.loading = true;
            try {
                const fd = new FormData();
                fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
                const res  = await fetch('{{ route('admin.settings.resend.test') }}', { method: 'POST', body: fd });
                const json = await res.json();
                this.connected = json.success;
                alert(json.message);
            } catch (e) {
                this.connected = false;
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
