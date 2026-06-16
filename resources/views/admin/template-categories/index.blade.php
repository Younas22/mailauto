@extends('layouts.admin')

@section('title', 'Template Categories')
@section('page-title', 'Template Categories')
@section('page-subtitle', 'Manage categories for your email templates')

@section('content')

{{-- Flash --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="mb-6 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-medium">
    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
    @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ─── Left: Add Category ─── --}}
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sticky top-24">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                Add Category
            </h2>
            <form method="POST" action="{{ route('admin.template-categories.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Category Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" autofocus
                           placeholder="e.g. Newsletter, Welcome…"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800
                                  border @error('name') border-red-400 @else border-slate-200 dark:border-slate-700 @enderror
                                  rounded-xl text-slate-800 dark:text-slate-200 placeholder-slate-400
                                  outline-none focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400 transition">
                    @error('name')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Category
                </button>
            </form>
        </div>
    </div>

    {{-- ─── Right: Categories List ─── --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                    All Categories
                    <span class="ml-2 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-semibold rounded-full">{{ $categories->count() }}</span>
                </h2>
                <a href="{{ route('admin.templates.index') }}"
                   class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline transition">
                    ← Back to Templates
                </a>
            </div>

            @if($categories->isEmpty())
                <div class="px-6 py-16 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-medium text-sm">No categories yet</p>
                    <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Add your first category from the left panel.</p>
                </div>
            @else
                <ul class="divide-y divide-slate-50 dark:divide-slate-800/60">
                    @foreach($categories as $cat)
                    <li x-data="{ editing: false, name: '{{ addslashes($cat->name) }}' }"
                        class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">

                        {{-- Icon --}}
                        <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>

                        {{-- Name / Edit input --}}
                        <div class="flex-1 min-w-0">
                            <span x-show="!editing" class="text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="name"></span>

                            <form x-show="editing" x-cloak
                                  method="POST" action="{{ route('admin.template-categories.update', $cat) }}"
                                  class="flex items-center gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="name" x-model="name"
                                       class="flex-1 px-3 py-1.5 text-sm bg-white dark:bg-slate-800 border border-brand-300 dark:border-brand-700
                                              rounded-lg text-slate-800 dark:text-slate-200 outline-none focus:ring-2 focus:ring-brand-400/30 transition">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-lg transition">
                                    Save
                                </button>
                                <button type="button" @click="editing = false; name = '{{ addslashes($cat->name) }}'"
                                        class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg transition">
                                    Cancel
                                </button>
                            </form>
                        </div>

                        {{-- Template count badge --}}
                        <span x-show="!editing"
                              class="flex-shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full
                                     {{ $cat->templates_count > 0
                                         ? 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-400'
                                         : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                            {{ $cat->templates_count }} template{{ $cat->templates_count !== 1 ? 's' : '' }}
                        </span>

                        {{-- Actions --}}
                        <div x-show="!editing" class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                            <button type="button" @click="editing = true"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400
                                           bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/30 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.template-categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($cat->name) }}\' category? Templates using it will have no category.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400
                                               bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>

                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

</div>

@endsection
