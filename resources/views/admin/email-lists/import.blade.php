@extends('layouts.admin')

@section('title', 'Import Contacts')
@section('page-title', 'Import Contacts')
@section('page-subtitle', 'Upload a CSV file to bulk-add email contacts')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm text-slate-400 dark:text-slate-500 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Dashboard</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('admin.email-lists.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Email Lists</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-600 dark:text-slate-300 font-medium">Import</span>
</nav>

@if($errors->any())
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 flex items-center gap-3">
    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-sm text-red-700 dark:text-red-400 font-medium">{{ $errors->first() }}</p>
</div>
@endif

<div
    x-data="{
        dragging: false,
        file: null,
        uploading: false,
        progress: 0,
        handleDrop(e) {
            this.dragging = false;
            const f = e.dataTransfer.files[0];
            if (f) { this.$refs.fileInput.files = e.dataTransfer.files; this.setFile(f); }
        },
        setFile(f) {
            if (!f.name.match(/\.(csv|txt)$/i)) { alert('Please upload a .csv or .txt file'); return; }
            this.file = {
                name: f.name,
                size: f.size < 1048576 ? (f.size / 1024).toFixed(1) + ' KB' : (f.size / 1048576).toFixed(2) + ' MB',
                raw: f
            };
        },
        handleChange(e) { const f = e.target.files[0]; if (f) this.setFile(f); },
        startUpload() {
            if (!this.file) return;
            this.uploading = true; this.progress = 0;
            let p = 0;
            const interval = setInterval(() => {
                p += Math.random() * 18 + 5;
                this.progress = Math.min(p, 90);
                if (this.progress >= 90) clearInterval(interval);
            }, 180);
            this.$refs.uploadForm.submit();
        },
        removeFile() { this.file = null; this.$refs.fileInput.value = ''; }
    }"
    class="grid grid-cols-1 lg:grid-cols-3 gap-6"
>
    {{-- Left: upload form --}}
    <div class="lg:col-span-2 space-y-5">

        <form method="POST" action="{{ route('admin.email-lists.import') }}"
              enctype="multipart/form-data"
              x-ref="uploadForm"
              class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            @csrf

            <h3 class="text-[15px] font-bold text-slate-900 dark:text-white mb-1">Upload CSV File</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">
                Drag & drop your file or click to browse.
                Supports <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-xs font-mono text-slate-700 dark:text-slate-300">.csv</code>
                and <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-xs font-mono text-slate-700 dark:text-slate-300">.txt</code>
            </p>

            {{-- List Name --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    List Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="list_name" required
                       value="{{ old('list_name') }}"
                       placeholder="e.g. Newsletter June 2025"
                       list="existing-groups"
                       class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl
                              focus:outline-none focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400
                              bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200
                              placeholder-slate-400 dark:placeholder-slate-500 transition">
                <datalist id="existing-groups">
                    @foreach($groups as $g)
                    <option value="{{ $g->name }}">
                    @endforeach
                </datalist>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Give this import a name, or match an existing list to append to it.</p>
                @error('list_name')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Drop zone --}}
            <div
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="handleDrop($event)"
                @click="if (!file) $refs.fileInput.click()"
                :class="dragging
                    ? 'border-brand-400 bg-brand-50 dark:bg-brand-900/20 scale-[1.01]'
                    : file
                        ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/10'
                        : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 hover:border-brand-300 dark:hover:border-brand-700 hover:bg-brand-50/40 dark:hover:bg-brand-900/10'"
                class="relative border-2 border-dashed rounded-2xl transition-all duration-200 cursor-pointer select-none"
                style="min-height: 180px;"
            >
                <input type="file" name="csv_file" accept=".csv,.txt" x-ref="fileInput"
                       @change="handleChange($event)" class="hidden" />

                <div x-show="!file" class="flex flex-col items-center justify-center py-10 px-6 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-brand-500 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-1">Drop your CSV here</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mb-2">or <span class="text-brand-600 dark:text-brand-400 font-semibold">click to browse</span></p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Max file size: 5 MB</p>
                </div>

                <div x-show="file" x-cloak class="flex items-center gap-4 p-6">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-slate-800 dark:text-slate-200 truncate" x-text="file?.name"></p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5" x-text="file?.size"></p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            File ready to upload
                        </p>
                    </div>
                    <button type="button" @click.stop="removeFile()"
                            class="p-2 rounded-xl text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="dragging" class="absolute inset-0 flex items-center justify-center bg-brand-50/80 dark:bg-brand-900/60 rounded-2xl backdrop-blur-sm">
                    <div class="text-center">
                        <svg class="w-10 h-10 text-brand-500 dark:text-brand-400 mx-auto mb-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <p class="text-brand-700 dark:text-brand-300 font-bold">Drop to upload</p>
                    </div>
                </div>
            </div>

            {{-- Progress --}}
            <div x-show="uploading" x-cloak class="mt-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Uploading & processing…</span>
                    <span class="text-sm font-bold text-brand-600 dark:text-brand-400" x-text="Math.round(progress) + '%'"></span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-500 to-violet-500 rounded-full transition-all duration-300"
                         :style="'width: ' + progress + '%'"></div>
                </div>
                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Validating emails and removing duplicates…</p>
            </div>

            {{-- Submit --}}
            <div class="mt-5 flex items-center gap-3">
                <button type="button" @click="startUpload()"
                        :disabled="!file || uploading"
                        :class="file && !uploading
                            ? 'bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-300/30 cursor-pointer hover:-translate-y-0.5 hover:shadow-md'
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed'"
                        class="flex-1 flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold rounded-xl transition-all">
                    <span x-show="!uploading" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Import Contacts
                    </span>
                    <span x-show="uploading" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Processing…
                    </span>
                </button>
                <a href="{{ route('admin.email-lists.index') }}"
                   class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition border border-slate-200 dark:border-slate-700">
                    Cancel
                </a>
            </div>
        </form>

        {{-- CSV format guide --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                CSV Format Guide
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $formats = [
                        ['label' => 'With header row', 'sample' => "name,email\nJohn Smith,john@example.com\nJane Doe,jane@example.com"],
                        ['label' => 'Email only',      'sample' => "email@example.com\nanother@example.com\nthird@example.com"],
                    ];
                @endphp
                @foreach($formats as $fmt)
                <div class="rounded-xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-800 px-3 py-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $fmt['label'] }}</span>
                    </div>
                    <pre class="px-3 py-3 text-xs font-mono text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre bg-white dark:bg-slate-900">{{ $fmt['sample'] }}</pre>
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between">
                <p class="text-xs text-slate-400 dark:text-slate-500">Headers are auto-detected. Duplicate emails are silently skipped.</p>
                <a href="{{ route('admin.email-lists.sample') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Download Sample CSV
                </a>
            </div>
        </div>

    </div>

    {{-- Right: rules & info --}}
    <div class="space-y-5">

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">What happens on import</h3>
            <div class="space-y-3">
                @php
                    $rules = [
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'Valid emails are saved as <strong>Pending</strong>'],
                        ['icon' => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z', 'color' => 'text-amber-500 bg-amber-50 dark:bg-amber-900/20', 'text' => 'Duplicate emails are <strong>skipped silently</strong>'],
                        ['icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 'color' => 'text-red-500 bg-red-50 dark:bg-red-900/20', 'text' => 'Invalid emails are <strong>rejected</strong>'],
                        ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'text-brand-500 bg-brand-50 dark:bg-brand-900/20', 'text' => 'Name column is <strong>optional</strong>'],
                    ];
                @endphp
                @foreach($rules as $rule)
                <div class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-lg {{ $rule['color'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $rule['icon'] }}"/>
                        </svg>
                    </span>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-snug">{!! $rule['text'] !!}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gradient-to-br from-brand-50 to-violet-50 dark:from-brand-900/20 dark:to-violet-900/20 rounded-2xl border border-brand-100 dark:border-brand-800/50 p-5">
            <h4 class="text-sm font-bold text-brand-700 dark:text-brand-400 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Accepted Column Names
            </h4>
            <div class="space-y-3 text-xs text-brand-700 dark:text-brand-300">
                <div>
                    <p class="font-semibold mb-1.5">Email column:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach(['email', 'e-mail', 'email address', 'mail'] as $h)
                        <code class="bg-white/70 dark:bg-white/10 px-1.5 py-0.5 rounded font-mono">{{ $h }}</code>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="font-semibold mb-1.5">Name column:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach(['name', 'full name', 'firstname', 'contact'] as $h)
                        <code class="bg-white/70 dark:bg-white/10 px-1.5 py-0.5 rounded font-mono">{{ $h }}</code>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5">
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">File Limits</h4>
            <div class="space-y-2.5 text-sm">
                @foreach([['Max file size', '5 MB'], ['Accepted types', '.csv, .txt'], ['Encoding', 'UTF-8'], ['Delimiter', 'Comma (,)']] as [$key, $val])
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 dark:text-slate-400">{{ $key }}</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@endsection
