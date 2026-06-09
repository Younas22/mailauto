@php
    $badges = [
        'valid'   => ['cls' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'label' => '✓ Valid'],
        'missing' => ['cls' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',                 'label' => '✗ Missing'],
        'invalid' => ['cls' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',         'label' => '⚠ Invalid'],
    ];
    $b = $badges[$status] ?? $badges['missing'];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $b['cls'] }}">
    {{ $b['label'] }}
</span>
