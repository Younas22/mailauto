@extends('layouts.admin')

@section('title', 'Email Lists')
@section('page-title', 'Email Lists')
@section('page-subtitle', 'All imported contacts and their delivery status')

@section('content')

{{-- Import result banner --}}
@if(session('import_result'))
@php $r = session('import_result'); @endphp
<div x-data="{ show: true }" x-show="show"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="mb-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800">
        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Import completed successfully</p>
        <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="grid grid-cols-3 divide-x divide-slate-100 dark:divide-slate-800">
        <div class="px-5 py-4 text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $r['imported'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Imported</p>
        </div>
        <div class="px-5 py-4 text-center">
            <p class="text-2xl font-bold text-amber-500 dark:text-amber-400">{{ $r['duplicates'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Duplicates</p>
        </div>
        <div class="px-5 py-4 text-center">
            <p class="text-2xl font-bold text-red-500 dark:text-red-400">{{ $r['invalid'] }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Invalid</p>
        </div>
    </div>
</div>
@endif

@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-medium">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-6">
    @php
        $statCards = [
            ['label' => 'Total',   'count' => $counts['total'],   'bg' => 'bg-brand-50 dark:bg-brand-900/20',   'dot' => 'bg-brand-500',   'num' => 'text-brand-700 dark:text-brand-400'],
            ['label' => 'Pending', 'count' => $counts['pending'], 'bg' => 'bg-amber-50 dark:bg-amber-900/20',   'dot' => 'bg-amber-400',   'num' => 'text-amber-700 dark:text-amber-400'],
            ['label' => 'Sent',    'count' => $counts['sent'],    'bg' => 'bg-emerald-50 dark:bg-emerald-900/20','dot' => 'bg-emerald-500', 'num' => 'text-emerald-700 dark:text-emerald-400'],
            ['label' => 'Failed',  'count' => $counts['failed'],  'bg' => 'bg-red-50 dark:bg-red-900/20',       'dot' => 'bg-red-400',     'num' => 'text-red-700 dark:text-red-400'],
        ];
    @endphp
    @foreach($statCards as $card)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm px-4 py-4 lg:px-5 lg:py-5 flex items-center gap-3 hover:shadow-md dark:hover:shadow-slate-900/50 transition-shadow">
        <div class="w-10 h-10 rounded-xl {{ $card['bg'] }} flex items-center justify-center flex-shrink-0">
            <span class="w-3 h-3 rounded-full {{ $card['dot'] }}"></span>
        </div>
        <div>
            <p class="text-xl lg:text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($card['count']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Header bar --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    {{-- Status filter tabs --}}
    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl w-fit">
        @foreach([null => 'All', 'pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed'] as $val => $label)
        <a href="{{ route('admin.email-lists.index', $val ? ['status' => $val] : []) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg transition
                  {{ $status === $val || ($val === null && !$status)
                     ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 shadow-sm'
                     : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <a href="{{ route('admin.email-lists.import') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-brand-300/30 hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        Import CSV
    </a>
</div>

{{-- Desktop table --}}
<div class="hidden md:block bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Added</th>
                <th class="text-right px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
            @forelse($emails as $contact)
            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">
                <td class="px-6 py-3.5 text-slate-400 dark:text-slate-500 text-xs font-medium">{{ $contact->id }}</td>
                <td class="px-6 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                            @if($contact->status === 'sent') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400
                            @elseif($contact->status === 'failed') bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400
                            @else bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 @endif">
                            {{ strtoupper(substr($contact->name ?: $contact->email, 0, 1)) }}
                        </div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $contact->email }}</span>
                    </div>
                </td>
                <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400">{{ $contact->name ?: '—' }}</td>
                <td class="px-6 py-3.5">
                    @if($contact->status === 'pending')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Pending
                        </span>
                    @elseif($contact->status === 'sent')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sent
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Failed
                        </span>
                    @endif
                </td>
                <td class="px-6 py-3.5 text-slate-400 dark:text-slate-500 text-xs">{{ $contact->created_at->format('M d, Y') }}</td>
                <td class="px-6 py-3.5 text-right">
                    <form method="POST" action="{{ route('admin.email-lists.destroy', $contact) }}"
                          onsubmit="return confirm('Remove this contact?')"
                          class="inline opacity-60 group-hover:opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Remove
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 font-medium">No contacts found</p>
                        <p class="text-slate-400 dark:text-slate-500 text-sm">Import a CSV file to add email contacts.</p>
                        <a href="{{ route('admin.email-lists.import') }}"
                           class="mt-1 inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
                            Import CSV
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($emails->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Showing <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $emails->firstItem() }}</span>–<span class="font-semibold text-slate-700 dark:text-slate-300">{{ $emails->lastItem() }}</span>
            of <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $emails->total() }}</span> contacts
        </p>
        <div class="flex items-center gap-1">
            @if($emails->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 rounded-lg cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $emails->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">← Prev</a>
            @endif
            @foreach($emails->getUrlRange(max(1,$emails->currentPage()-2), min($emails->lastPage(),$emails->currentPage()+2)) as $page => $url)
                @if($page == $emails->currentPage())
                    <span class="px-3 py-1.5 text-xs font-bold text-white bg-brand-600 rounded-lg">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">{{ $page }}</a>
                @endif
            @endforeach
            @if($emails->hasMorePages())
                <a href="{{ $emails->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">Next →</a>
            @else
                <span class="px-3 py-1.5 text-xs text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 rounded-lg cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-2">
    @forelse($emails as $contact)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm px-4 py-3.5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                    @if($contact->status === 'sent') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400
                    @elseif($contact->status === 'failed') bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400
                    @else bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 @endif">
                    {{ strtoupper(substr($contact->name ?: $contact->email, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $contact->email }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $contact->name ?: 'No name' }} · {{ $contact->created_at->format('M d') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($contact->status === 'pending')
                    <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-xs font-semibold rounded-full">Pending</span>
                @elseif($contact->status === 'sent')
                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">Sent</span>
                @else
                    <span class="px-2 py-0.5 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-semibold rounded-full">Failed</span>
                @endif
                <form method="POST" action="{{ route('admin.email-lists.destroy', $contact) }}"
                      onsubmit="return confirm('Remove?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-10 text-center">
        <p class="text-slate-500 dark:text-slate-400 font-medium text-sm mb-3">No contacts found</p>
        <a href="{{ route('admin.email-lists.import') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
            Import CSV
        </a>
    </div>
    @endforelse
</div>

@endsection
