{{-- Quill Rich Editor CDN --}}
@once
@push('head')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet"/>
<style>
    /* Quill editor — light mode */
    .ql-toolbar.ql-snow {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-color: #e2e8f0 !important;
        background: #f8fafc;
        padding: 10px 12px;
    }
    .ql-container.ql-snow {
        border-radius: 0 0 0.75rem 0.75rem !important;
        border-color: #e2e8f0 !important;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        min-height: 260px;
    }
    .ql-editor { min-height: 260px; line-height: 1.75; }
    .ql-editor.ql-blank::before { color: #94a3b8; font-style: normal; }
    .ql-snow .ql-stroke { stroke: #64748b; }
    .ql-snow .ql-fill  { fill:  #64748b; }
    .ql-snow.ql-toolbar button:hover .ql-stroke,
    .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #4f46e5; }
    .ql-snow.ql-toolbar button:hover .ql-fill,
    .ql-snow.ql-toolbar button.ql-active .ql-fill  { fill: #4f46e5; }
    .ql-snow.ql-toolbar .ql-picker-label:hover,
    .ql-snow.ql-toolbar .ql-picker-item:hover { color: #4f46e5; }

    /* Quill editor — dark mode */
    .dark .ql-toolbar.ql-snow {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    .dark .ql-container.ql-snow {
        border-color: #334155 !important;
        background: #0f172a;
        color: #e2e8f0;
    }
    .dark .ql-editor { color: #e2e8f0; }
    .dark .ql-editor.ql-blank::before { color: #64748b; }
    .dark .ql-snow .ql-stroke { stroke: #94a3b8; }
    .dark .ql-snow .ql-fill  { fill: #94a3b8; }
    .dark .ql-snow .ql-picker-label { color: #94a3b8; }
    .dark .ql-snow.ql-toolbar button:hover .ql-stroke,
    .dark .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #818cf8; }
    .dark .ql-snow.ql-toolbar button:hover .ql-fill,
    .dark .ql-snow.ql-toolbar button.ql-active .ql-fill  { fill: #818cf8; }
    .dark .ql-snow .ql-picker-options {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    .dark .ql-snow .ql-picker-item { color: #94a3b8; }
</style>
@endpush
@endonce

{{-- Validation errors --}}
@if($errors->any())
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-red-700 dark:text-red-400">Please fix the following errors:</p>
        <ul class="mt-1 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li class="text-sm text-red-600 dark:text-red-400">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ─── LEFT: Main Fields ─── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Title + Category --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Template Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $template->title ?? '') }}"
                       placeholder="e.g. Welcome Email, Monthly Newsletter…"
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800
                              border @error('title') border-red-400 dark:border-red-700 bg-red-50 dark:bg-red-900/20 @else border-slate-200 dark:border-slate-700 @enderror
                              rounded-xl text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 outline-none
                              focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400 transition" />
                @error('title')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Category</label>
                    <a href="{{ route('admin.template-categories.index') }}"
                       class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline transition">
                        + Manage Categories
                    </a>
                </div>
                @php $dbCategories = \App\Models\TemplateCategory::orderBy('name')->pluck('name'); @endphp
                <div class="relative">
                    <select name="category"
                            class="appearance-none w-full px-4 py-2.5 pr-10 text-sm bg-slate-50 dark:bg-slate-800
                                   border border-slate-200 dark:border-slate-700 rounded-xl
                                   text-slate-800 dark:text-slate-200 outline-none cursor-pointer
                                   focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400 transition">
                        <option value="">— No Category —</option>
                        @foreach($dbCategories as $opt)
                            <option value="{{ $opt }}" {{ old('category', $template->category ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Optional — used to filter and group templates.</p>
                @error('category')
                    <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Subject --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Email Subject <span class="text-red-500">*</span>
            </label>
            <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}"
                   placeholder="e.g. Welcome to MailAuto! 🎉"
                   class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800
                          border @error('subject') border-red-400 dark:border-red-700 bg-red-50 dark:bg-red-900/20 @else border-slate-200 dark:border-slate-700 @enderror
                          rounded-xl text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 outline-none
                          focus:ring-2 focus:ring-brand-400/30 focus:border-brand-400 transition" />
            <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">This is what recipients see in their inbox preview.</p>
            @error('subject')
                <p class="mt-1 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Body (Quill Editor) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Email Body <span class="text-red-500">*</span>
            </label>
            <div id="quill-editor">{!! old('body', $template->body ?? '') !!}</div>
            <input type="hidden" name="body" id="body-input" />
            @error('body')
                <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">Supports rich text, links, lists, and inline code.</p>
        </div>

    </div>

    {{-- ─── RIGHT: Settings sidebar ─── --}}
    <div class="space-y-5">

        {{-- Settings: Status --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 lg:p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-4">Settings</h3>

            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2.5">Status</label>
            <div class="space-y-2">
                @foreach(['active' => ['label' => 'Active', 'desc' => 'Template is usable in campaigns'],
                           'inactive' => ['label' => 'Inactive', 'desc' => 'Hidden from campaign selection']] as $val => $opt)
                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition
                    {{ old('status', $template->status ?? 'active') === $val
                        ? 'border-brand-300 dark:border-brand-700 bg-brand-50 dark:bg-brand-900/20'
                        : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                    <input type="radio" name="status" value="{{ $val }}"
                           {{ old('status', $template->status ?? 'active') === $val ? 'checked' : '' }}
                           class="mt-0.5 accent-brand-600" />
                    <div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $opt['label'] }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $opt['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Merge Tags --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Merge Tags
            </h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">Click a tag to insert at cursor (subject or body).</p>
            <div class="flex flex-wrap gap-2">
                @php
                    $mergeTags = [
                        ['tag' => '{{name}}',  'desc' => "Recipient's name"],
                        ['tag' => '{{email}}', 'desc' => "Recipient's email address"],
                    ];
                @endphp
                @foreach($mergeTags as $mt)
                <button type="button"
                        onclick="insertMergeTag('{{ $mt['tag'] }}')"
                        title="{{ $mt['desc'] }}"
                        class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-semibold
                               bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300
                               border border-brand-200 dark:border-brand-700 rounded-lg
                               hover:bg-brand-100 dark:hover:bg-brand-800/50 transition cursor-pointer">
                    {{ $mt['tag'] }}
                </button>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">Replaced with real values when email is sent.</p>
        </div>

        {{-- Tips card --}}
        <div class="bg-gradient-to-br from-brand-50 to-violet-50 dark:from-brand-900/20 dark:to-violet-900/20 rounded-2xl border border-brand-100 dark:border-brand-800/50 p-5">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h4 class="text-sm font-bold text-brand-700 dark:text-brand-400">Tips</h4>
            </div>
            <ul class="space-y-1.5 text-xs text-brand-700 dark:text-brand-300">
                @foreach(['Use a clear, concise subject line', 'Keep body under 200 words for best open rates', 'Always include an unsubscribe link', 'Test on mobile before sending'] as $tip)
                <li class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 mt-0.5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $tip }}
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Submit --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5">
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-brand-600 hover:bg-brand-700
                           text-white text-sm font-bold rounded-xl transition shadow-sm shadow-brand-300/30 hover:-translate-y-0.5 hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                @isset($template->id) Update Template @else Save Template @endisset
            </button>
            <a href="{{ route('admin.templates.index') }}"
               class="mt-2.5 w-full flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400
                      bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition border border-slate-200 dark:border-slate-700">
                Cancel
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your email body here…',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Merge tag insertion
    let _lastField = 'quill';
    const _subjectInput = document.querySelector('input[name="subject"]');
    _subjectInput.addEventListener('focus', () => _lastField = 'subject');
    quill.on('selection-change', range => { if (range) _lastField = 'quill'; });

    function insertMergeTag(tag) {
        if (_lastField === 'subject') {
            const s = _subjectInput.selectionStart ?? _subjectInput.value.length;
            const e = _subjectInput.selectionEnd   ?? _subjectInput.value.length;
            _subjectInput.value = _subjectInput.value.slice(0, s) + tag + _subjectInput.value.slice(e);
            _subjectInput.setSelectionRange(s + tag.length, s + tag.length);
            _subjectInput.focus();
        } else {
            const range = quill.getSelection(true);
            quill.insertText(range.index, tag, 'user');
            quill.setSelection(range.index + tag.length);
        }
    }

    document.getElementById('quill-editor').closest('form').addEventListener('submit', function () {
        document.getElementById('body-input').value = quill.root.innerHTML;
    });
</script>
@endpush
