@php
    $colors = [
        'valid'   => ['ring' => 'ring-emerald-200 dark:ring-emerald-800/50', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/10',
                      'icon' => 'text-emerald-600 dark:text-emerald-400', 'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/40',
                      'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                      'label' => 'Valid'],
        'missing' => ['ring' => 'ring-red-200 dark:ring-red-800/50', 'bg' => 'bg-red-50 dark:bg-red-900/10',
                      'icon' => 'text-red-500 dark:text-red-400', 'iconBg' => 'bg-red-100 dark:bg-red-900/40',
                      'badge' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                      'label' => 'Missing'],
        'invalid' => ['ring' => 'ring-amber-200 dark:ring-amber-800/50', 'bg' => 'bg-amber-50 dark:bg-amber-900/10',
                      'icon' => 'text-amber-500 dark:text-amber-400', 'iconBg' => 'bg-amber-100 dark:bg-amber-900/40',
                      'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                      'label' => 'Invalid'],
    ];
    $c = $colors[$status] ?? $colors['missing'];
@endphp

<div class="rounded-2xl border ring-1 {{ $c['ring'] }} {{ $c['bg'] }} p-5 flex flex-col gap-3">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl {{ $c['iconBg'] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </span>
            <div>
                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $label }}</p>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $c['badge'] }}">
                    @if($status === 'valid')
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @elseif($status === 'missing')
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    @endif
                    {{ $c['label'] }}
                </span>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $description }}</p>

    {{-- Extra note (selector, DMARC policy warning, etc.) --}}
    @if(!empty($extra))
        <p class="text-xs text-slate-500 dark:text-slate-400">{!! $extra !!}</p>
    @endif

    {{-- Record value --}}
    @if($record)
        <div x-data="{ expanded: false }">
            <button @click="expanded = !expanded"
                    class="text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center gap-1 transition-colors">
                <svg :class="expanded ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span x-text="expanded ? 'Hide record' : 'Show record'">Show record</span>
            </button>
            <div x-show="expanded" x-transition class="mt-2">
                <pre class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-400 bg-white/60 dark:bg-slate-900/40 rounded-lg p-3 overflow-x-auto break-all whitespace-pre-wrap border border-slate-200 dark:border-slate-700/50">{{ $record }}</pre>
            </div>
        </div>
    @endif

    {{-- Fix hint for non-valid --}}
    @if($status !== 'valid')
        <div class="border-t border-current/10 pt-3 mt-auto">
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">How to fix</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{!! $fix !!}</p>
        </div>
    @endif
</div>
