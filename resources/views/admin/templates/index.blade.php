@extends('layouts.admin')

@section('title', 'Email Templates')
@section('page-title', 'Email Templates')
@section('page-subtitle', 'Manage your reusable email templates')

@section('content')

{{-- Flash message --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="mb-6 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-medium">
    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
    <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

{{-- Header bar --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $templates->total() }}</span> templates total
        </p>
    </div>
    <a href="{{ route('admin.templates.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-brand-300/30 hover:-translate-y-0.5 hover:shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Template
    </a>
</div>

{{-- Desktop table --}}
<div class="hidden md:block bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Title</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-left px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Created</th>
                <th class="text-right px-6 py-3.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
            @forelse($templates as $template)
            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">
                <td class="px-6 py-4 text-slate-400 dark:text-slate-500 font-medium text-sm">{{ $template->id }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $template->title }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ $template->subject }}</td>
                <td class="px-6 py-4">
                    @if($template->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span> Inactive
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-slate-400 dark:text-slate-500 text-xs">{{ $template->created_at->format('M d, Y') }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('admin.templates.edit', $template) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/30 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.templates.destroy', $template) }}"
                              onsubmit="return confirm('Delete this template?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 font-medium">No templates yet</p>
                        <p class="text-slate-400 dark:text-slate-500 text-sm">Create your first email template to get started.</p>
                        <a href="{{ route('admin.templates.create') }}"
                           class="mt-1 inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Template
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($templates->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Showing {{ $templates->firstItem() }}–{{ $templates->lastItem() }} of {{ $templates->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($templates->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 rounded-lg cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $templates->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">← Prev</a>
            @endif
            @foreach($templates->getUrlRange(1, $templates->lastPage()) as $page => $url)
                @if($page == $templates->currentPage())
                    <span class="px-3 py-1.5 text-xs font-bold text-white bg-brand-600 rounded-lg">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">{{ $page }}</a>
                @endif
            @endforeach
            @if($templates->hasMorePages())
                <a href="{{ $templates->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">Next →</a>
            @else
                <span class="px-3 py-1.5 text-xs text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 rounded-lg cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Mobile card layout --}}
<div class="md:hidden space-y-3">
    @forelse($templates as $template)
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $template->title }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $template->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            @if($template->status === 'active')
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-semibold rounded-full flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></span> Inactive
                </span>
            @endif
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 truncate">
            <span class="font-medium text-slate-600 dark:text-slate-300">Subject:</span> {{ $template->subject }}
        </p>
        <div class="flex gap-2 pt-3 border-t border-slate-50 dark:border-slate-800">
            <a href="{{ route('admin.templates.edit', $template) }}"
               class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/30 rounded-xl transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('admin.templates.destroy', $template) }}"
                  onsubmit="return confirm('Delete this template?')" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-xl transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-10 text-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="text-slate-500 dark:text-slate-400 font-medium text-sm">No templates yet</p>
        <a href="{{ route('admin.templates.create') }}"
           class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
            + Create Template
        </a>
    </div>
    @endforelse

    @if($templates->hasPages())
    <div class="flex justify-center pt-2">
        {{ $templates->links() }}
    </div>
    @endif
</div>

@endsection
