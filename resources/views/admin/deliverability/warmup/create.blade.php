@extends('layouts.admin')

@section('title', 'New Warmup Plan')
@section('page-title', 'New Warmup Plan')
@section('page-subtitle', 'Configure a gradual email volume ramp-up for your domain')

@section('content')

<div class="max-w-2xl"
     x-data="warmupForm()"
     x-init="init()">

    <form method="POST" action="{{ route('admin.warmup.store') }}" class="space-y-6">
        @csrf

        {{-- Plan details --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-5">Plan Details</h2>

            <div class="space-y-4">
                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Plan Name
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. Domain warmup — travelbookingpanel.com"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100
                                  text-sm placeholder-slate-400 dark:placeholder-slate-600 transition-all" />
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Domain + Provider --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                            Sending Domain
                        </label>
                        <input type="text" name="domain" value="{{ old('domain') }}" required
                               placeholder="yourdomain.com"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100
                                      text-sm font-mono placeholder-slate-400 dark:placeholder-slate-600 transition-all" />
                        @error('domain')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                            Provider
                        </label>
                        <select name="provider"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                       bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all">
                            @foreach($providers as $value => $label)
                                <option value="{{ $value }}" {{ old('provider', 'ses') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('provider')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Start Date + Duration --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                            Start Date
                        </label>
                        <input type="date" name="start_date"
                               x-model="startDate"
                               @change="recalc()"
                               value="{{ old('start_date', today()->toDateString()) }}" required
                               min="{{ today()->toDateString() }}"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                      bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all" />
                        @error('start_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                            Duration
                        </label>
                        <select x-model="duration" @change="recalc()"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                       bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all">
                            <option value="7">7 days</option>
                            <option value="15" selected>15 days</option>
                            <option value="20">20 days</option>
                            <option value="25">25 days</option>
                            <option value="30">30 days</option>
                        </select>
                    </div>
                </div>

                {{-- End date (auto-calculated, read-only display) --}}
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-700">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs text-slate-500 dark:text-slate-400">End date:</span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="endDateDisplay"></span>
                    <input type="hidden" name="end_date" :value="endDate" />
                    @error('end_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Schedule Parameters --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Ramp-up Settings</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">Choose how many emails to send on Day 1 and how fast to grow volume each day.</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Day 1 Emails
                    </label>
                    <select name="day1_emails" x-model="day1" @change="recalc()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                   bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all">
                        <option value="5">5 emails</option>
                        <option value="10" selected>10 emails</option>
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Emails to send on the very first day</p>
                    @error('day1_emails')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Daily Increase
                    </label>
                    <select name="increase_factor" x-model="factor" @change="recalc()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                   bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all">
                        <option value="1">1 — Slow increase</option>
                        <option value="2" selected>2 — Moderate increase</option>
                        <option value="3">3 — Normal increase</option>
                        <option value="4">4 — Fast increase</option>
                        <option value="5">5 — Aggressive increase</option>
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Emails added each day = factor × Day 1 count</p>
                    @error('increase_factor')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Email source --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Source</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">Warmup emails are sent to real subscribers in your list to generate authentic engagement signals.</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Email Group <span class="font-normal text-slate-400">(optional)</span>
                    </label>
                    <select name="email_group_id"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                   bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all">
                        <option value="">— All groups —</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('email_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('email_group_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Template <span class="font-normal text-slate-400">(optional)</span>
                    </label>
                    <select name="email_template_id"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                   bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all">
                        <option value="">— Random active template —</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('email_template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('email_template_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Safety thresholds --}}
        <div class="bg-white dark:bg-[#111827] border border-slate-100 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Safety Thresholds</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-5">The plan will auto-pause when these rates are exceeded in a 7-day rolling window.</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Max Bounce Rate (%)
                    </label>
                    <input type="number" name="max_bounce_rate" step="0.1" min="0.1" max="20"
                           value="{{ old('max_bounce_rate', '5.0') }}"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all" />
                    <p class="mt-1 text-[11px] text-slate-400">Recommended: ≤ 5%</p>
                    @error('max_bounce_rate')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">
                        Max Complaint Rate (%)
                    </label>
                    <input type="number" name="max_complaint_rate" step="0.01" min="0.01" max="5"
                           value="{{ old('max_complaint_rate', '0.10') }}"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                                  bg-white dark:bg-slate-800/60 text-slate-800 dark:text-slate-100 text-sm transition-all" />
                    <p class="mt-1 text-[11px] text-slate-400">Recommended: ≤ 0.1%</p>
                    @error('max_complaint_rate')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Dynamic Warmup Schedule Preview --}}
        <div class="bg-brand-50 dark:bg-brand-900/10 border border-brand-100 dark:border-brand-800/40 rounded-2xl p-5">
            <h3 class="text-xs font-semibold text-brand-700 dark:text-brand-400 uppercase tracking-wide mb-1">
                Warmup Schedule Preview
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-4"
               x-text="'Showing ' + schedule.length + '-day ramp starting at ' + day1 + ' emails/day'"></p>

            {{-- Day grid --}}
            <div class="grid gap-2"
                 :style="'grid-template-columns: repeat(' + Math.min(schedule.length, 5) + ', minmax(0, 1fr))'">
                <template x-for="item in schedule" :key="item.day">
                    <div class="text-center bg-white dark:bg-slate-800/60 rounded-xl py-2 px-1 border border-brand-100/60 dark:border-slate-700">
                        <div class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 mb-0.5"
                             x-text="'Day ' + item.day"></div>
                        <div class="text-xs font-bold text-brand-700 dark:text-brand-400"
                             x-text="formatNumber(item.emails)"></div>
                    </div>
                </template>
            </div>

            {{-- Summary line --}}
            <p class="mt-3 text-[11px] text-slate-500 dark:text-slate-400"
               x-text="summaryLine"></p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700
                           text-white text-sm font-semibold shadow-sm transition-all active:scale-95">
                Create Plan
            </button>
            <a href="{{ route('admin.warmup.index') }}"
               class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700
                      text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function warmupForm() {
    return {
        startDate: '{{ today()->toDateString() }}',
        duration:  15,
        day1:      10,
        factor:    2,
        endDate:      '',
        endDateDisplay: '',
        schedule:     [],
        summaryLine:  '',

        init() {
            this.recalc();
        },

        recalc() {
            this.computeEndDate();
            this.buildSchedule();
        },

        computeEndDate() {
            if (!this.startDate) return;
            const d = new Date(this.startDate + 'T00:00:00');
            d.setDate(d.getDate() + parseInt(this.duration) - 1);
            const yyyy = d.getFullYear();
            const mm   = String(d.getMonth() + 1).padStart(2, '0');
            const dd   = String(d.getDate()).padStart(2, '0');
            this.endDate = `${yyyy}-${mm}-${dd}`;
            this.endDateDisplay = d.toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        },

        buildSchedule() {
            const days      = parseInt(this.duration);
            const d1        = parseInt(this.day1);
            const f         = parseInt(this.factor);
            const increment = f * d1; // additive step per day
            const result    = [];
            for (let i = 1; i <= days; i++) {
                const emails = d1 + increment * (i - 1);
                result.push({ day: i, emails: emails });
            }
            this.schedule = result;

            const last = result[result.length - 1];
            this.summaryLine = `+${this.formatNumber(increment)} emails added each day. Day ${days} reaches ${this.formatNumber(last.emails)} emails/day.`;
        },

        formatNumber(n) {
            return Number(n).toLocaleString();
        }
    };
}
</script>

@endsection
