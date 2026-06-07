<!DOCTYPE html>
<html lang="en"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed') || 'false'),
          darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'),
          init() {
              this.$watch('sidebarCollapsed', v => localStorage.setItem('sidebarCollapsed', v));
              this.$watch('darkMode', v => localStorage.setItem('darkMode', v));
          }
      }"
      :class="{ 'dark': darkMode }"
      x-cloak>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'MailAuto') — Admin</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..800;1,14..32,300..800&display=swap" rel="stylesheet" />

    {{-- Tailwind Play CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        brand: {
                            50:  '#f0f4ff',
                            100: '#e0e9ff',
                            200: '#c7d7fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.2s ease',
                        'slide-up': 'slideUp 0.25s ease',
                    },
                    keyframes: {
                        fadeIn: { from: { opacity: '0', transform: 'translateY(4px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
                        slideUp: { from: { opacity: '0', transform: 'translateY(8px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('head')

    <style>
        *, *::before, *::after { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }

        body { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        .sidebar-link { transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { transform: translateX(2px); }

        /* Collapsed sidebar tooltip */
        .nav-tooltip {
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: #0f172a;
            color: #f8fafc;
            font-size: 11.5px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 7px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s, transform 0.15s;
            z-index: 100;
            transform: translateY(-50%) translateX(-4px);
        }
        .nav-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #0f172a;
        }
        .dark .nav-tooltip { background: #1e293b; }
        .dark .nav-tooltip::before { border-right-color: #1e293b; }
        .nav-item:hover .nav-tooltip {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        /* Page fade-in */
        main { animation: fadeIn 0.22s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* Focus ring */
        input:focus, select:focus, textarea:focus, button:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        /* Badge pulse for running status */
        .status-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        /* Smooth all transitions */
        .transition-sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .transition-content { transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Dark mode form inputs */
        .dark input, .dark select, .dark textarea {
            color-scheme: dark;
        }

        /* Hover card lift */
        .card-hover { transition: box-shadow 0.2s ease, transform 0.2s ease; }
        .card-hover:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.08); transform: translateY(-1px); }
        .dark .card-hover:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.4); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f1a] text-slate-800 dark:text-slate-100 antialiased">

    {{-- ─── Mobile overlay ─── --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm lg:hidden"
    ></div>

    {{-- ─────────── SIDEBAR ─────────── --}}
    <aside
        :class="{
            'translate-x-0': sidebarOpen,
            '-translate-x-full': !sidebarOpen,
            'lg:w-[64px]': sidebarCollapsed,
            'lg:w-64': !sidebarCollapsed
        }"
        class="fixed top-0 left-0 z-30 h-full w-64 lg:translate-x-0
               bg-white dark:bg-[#111827]
               border-r border-slate-100 dark:border-slate-800/80
               shadow-xl lg:shadow-none
               transition-all duration-300 ease-in-out
               flex flex-col overflow-hidden"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-[18px] border-b border-slate-100 dark:border-slate-800/80 overflow-hidden flex-shrink-0">
            <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center shadow-md shadow-brand-300/40 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div x-show="!sidebarCollapsed"
                 x-transition:enter="transition-opacity duration-200 delay-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="overflow-hidden whitespace-nowrap">
                <p class="text-[15px] font-bold text-slate-900 dark:text-white tracking-tight leading-none">MailAuto</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-widest mt-0.5">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 transition-all duration-300"
             :class="sidebarCollapsed ? 'px-2' : 'px-3'">

            {{-- Section: Main --}}
            <div x-show="!sidebarCollapsed"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="px-3 pt-1 pb-1.5">
                <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-widest">Main</p>
            </div>
            <div x-show="sidebarCollapsed" class="py-2">
                <div class="h-px bg-slate-100 dark:bg-slate-800 mx-2"></div>
            </div>

            {{-- Dashboard --}}
            <div class="relative nav-item">
                <a href="/mailauto/admin/dashboard"
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                   class="sidebar-link flex items-center py-2.5 rounded-xl text-sm font-medium mb-0.5
                          {{ request()->is('*admin/dashboard')
                             ? 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-400'
                             : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="w-[34px] h-[34px] rounded-lg flex items-center justify-center flex-shrink-0
                                 {{ request()->is('*admin/dashboard') ? 'bg-brand-100 dark:bg-brand-900/40' : 'bg-slate-100 dark:bg-slate-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-3a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                        </svg>
                    </span>
                    <span x-show="!sidebarCollapsed"
                          x-transition:enter="transition-opacity duration-150"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          class="whitespace-nowrap">Dashboard</span>
                </a>
                <span x-show="sidebarCollapsed" class="nav-tooltip">Dashboard</span>
            </div>

            {{-- Section: Email --}}
            <div x-show="!sidebarCollapsed"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="px-3 pt-4 pb-1.5">
                <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-widest">Email</p>
            </div>
            <div x-show="sidebarCollapsed" class="py-2">
                <div class="h-px bg-slate-100 dark:bg-slate-800 mx-2"></div>
            </div>

            @php
                $navItems = [
                    ['href' => route('admin.campaigns.index'),    'label' => 'Campaigns',   'active' => request()->is('*admin/campaigns*'),
                     'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
                    ['href' => route('admin.email-lists.index'),  'label' => 'Email Lists', 'active' => request()->is('*admin/email-lists*'),
                     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['href' => route('admin.templates.index'),    'label' => 'Templates',   'active' => request()->is('*admin/templates*'),
                     'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                    ['href' => route('admin.email-logs.index'),   'label' => 'Email Logs',  'active' => request()->is('*admin/email-logs*'),
                     'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ];
            @endphp

            @foreach($navItems as $item)
            <div class="relative nav-item">
                <a href="{{ $item['href'] }}"
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                   class="sidebar-link flex items-center py-2.5 rounded-xl text-sm font-medium mb-0.5
                          {{ $item['active']
                             ? 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-400'
                             : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="w-[34px] h-[34px] rounded-lg flex items-center justify-center flex-shrink-0
                                 {{ $item['active'] ? 'bg-brand-100 dark:bg-brand-900/40' : 'bg-slate-100 dark:bg-slate-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                    </span>
                    <span x-show="!sidebarCollapsed"
                          x-transition:enter="transition-opacity duration-150"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          class="whitespace-nowrap">{{ $item['label'] }}</span>
                    @if($item['active'])
                    <span x-show="!sidebarCollapsed" class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-500 flex-shrink-0"></span>
                    @endif
                </a>
                <span x-show="sidebarCollapsed" class="nav-tooltip">{{ $item['label'] }}</span>
            </div>
            @endforeach

            {{-- Section: System --}}
            <div x-show="!sidebarCollapsed"
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="px-3 pt-4 pb-1.5">
                <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-widest">System</p>
            </div>
            <div x-show="sidebarCollapsed" class="py-2">
                <div class="h-px bg-slate-100 dark:bg-slate-800 mx-2"></div>
            </div>

            <div class="relative nav-item">
                <a href="{{ route('admin.settings.index') }}"
                   :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-3 px-3'"
                   class="sidebar-link flex items-center py-2.5 rounded-xl text-sm font-medium mb-0.5
                          {{ request()->is('*admin/settings*')
                             ? 'bg-brand-50 dark:bg-brand-900/25 text-brand-700 dark:text-brand-400'
                             : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="w-[34px] h-[34px] rounded-lg flex items-center justify-center flex-shrink-0
                                 {{ request()->is('*admin/settings*') ? 'bg-brand-100 dark:bg-brand-900/40' : 'bg-slate-100 dark:bg-slate-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <span x-show="!sidebarCollapsed"
                          x-transition:enter="transition-opacity duration-150"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          class="whitespace-nowrap">Settings</span>
                    @if(request()->is('*admin/settings*'))
                    <span x-show="!sidebarCollapsed" class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-500 flex-shrink-0"></span>
                    @endif
                </a>
                <span x-show="sidebarCollapsed" class="nav-tooltip">Settings</span>
            </div>
        </nav>

        {{-- Bottom: Collapse toggle + User profile --}}
        <div class="flex-shrink-0 border-t border-slate-100 dark:border-slate-800/80 p-3 space-y-2">

            {{-- Desktop collapse button --}}
            <button @click="sidebarCollapsed = !sidebarCollapsed"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'gap-2 px-3'"
                    class="hidden lg:flex w-full items-center py-2.5 rounded-xl text-xs font-semibold
                           text-slate-400 dark:text-slate-600
                           hover:bg-slate-50 dark:hover:bg-slate-800/60
                           hover:text-slate-600 dark:hover:text-slate-300
                           transition-all duration-200">
                <svg :class="sidebarCollapsed ? 'rotate-180' : ''"
                     class="w-4 h-4 transition-transform duration-300 flex-shrink-0"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span x-show="!sidebarCollapsed"
                      x-transition:enter="transition-opacity duration-150"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      class="whitespace-nowrap">Collapse</span>
            </button>

            {{-- User profile + logout --}}
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    :class="sidebarCollapsed ? 'justify-center' : 'gap-3'"
                    class="flex items-center w-full p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors overflow-hidden"
                >
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center text-white text-sm font-bold shadow flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div x-show="!sidebarCollapsed"
                         x-transition:enter="transition-opacity duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="flex-1 min-w-0 overflow-hidden text-left">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate leading-tight">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <svg x-show="!sidebarCollapsed" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </button>

                {{-- Logout dropdown --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    @click.outside="open = false"
                    :class="sidebarCollapsed ? 'left-14 bottom-0' : 'bottom-full mb-1'"
                    class="absolute left-0 right-0 bg-white dark:bg-[#1e293b] border border-slate-100 dark:border-slate-700 rounded-xl shadow-lg shadow-slate-200/60 dark:shadow-black/40 overflow-hidden z-50"
                    x-cloak
                >
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 w-full px-4 py-3 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- ─────────── MAIN WRAPPER ─────────── --}}
    <div :class="sidebarCollapsed ? 'lg:pl-[64px]' : 'lg:pl-64'"
         class="min-h-screen flex flex-col transition-all duration-300 ease-in-out">

        {{-- ─── TOP NAVBAR ─── --}}
        <header class="sticky top-0 z-10 bg-white/90 dark:bg-[#111827]/90 backdrop-blur-md border-b border-slate-100 dark:border-slate-800/80 shadow-sm">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">

                {{-- Left: hamburger + page title --}}
                <div class="flex items-center gap-4 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-[15px] sm:text-base font-bold text-slate-900 dark:text-white truncate leading-tight">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block truncate mt-px">
                            @yield('page-subtitle', 'Welcome back, Admin')
                        </p>
                    </div>
                </div>

                {{-- Right: search + actions --}}
                <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">

                    {{-- Search --}}
                    <div class="hidden md:flex items-center gap-2 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl px-3 py-2 w-48 lg:w-60 focus-within:ring-2 focus-within:ring-brand-300/60 focus-within:border-brand-400 transition-all">
                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" placeholder="Quick search…"
                               class="bg-transparent text-xs text-slate-600 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600 outline-none w-full" />
                    </div>

                    {{-- Dark mode toggle --}}
                    <button @click="darkMode = !darkMode"
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                        <svg x-show="!darkMode" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="darkMode" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>

                    {{-- Notification bell --}}
                    <button class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full ring-2 ring-white dark:ring-[#111827]"></span>
                    </button>

                    {{-- Divider --}}
                    <div class="w-px h-5 bg-slate-200 dark:bg-slate-700 hidden sm:block mx-0.5"></div>

                    {{-- Avatar --}}
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center text-white text-xs font-bold shadow cursor-pointer ring-2 ring-white dark:ring-slate-800 hover:ring-brand-300 transition">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                </div>
            </div>
        </header>

        {{-- APP_DEBUG production warning --}}
        @if(config('app.debug') && app()->isProduction())
        <div class="bg-red-600 text-white text-center text-sm font-semibold py-2 px-4 flex items-center justify-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            WARNING: APP_DEBUG is enabled in production. Set APP_DEBUG=false in your .env file immediately.
        </div>
        @elseif(config('app.debug') && !app()->isProduction())
        <div class="bg-amber-500 text-white text-center text-xs font-semibold py-1.5 px-4">
            Debug mode is ON — disable before deploying to production (APP_DEBUG=false)
        </div>
        @endif

        {{-- PAGE CONTENT --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            @yield('content')
        </main>

        {{-- FOOTER --}}
        <footer class="px-6 lg:px-8 py-4 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-400 dark:text-slate-600 flex items-center justify-between">
            <span>© {{ date('Y') }} MailAuto. All rights reserved.</span>
            <span class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                v1.0.0
            </span>
        </footer>
    </div>

    @stack('scripts')

    {{-- Global: form submit loading states --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]:not([data-no-loading])');
                if (!btn || btn.disabled) return;

                var spinner = '<svg class="w-4 h-4 animate-spin inline-block mr-1.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>';
                btn.innerHTML = spinner + 'Processing…';
                btn.disabled = true;
                btn.style.opacity = '0.75';
                btn.style.cursor = 'not-allowed';
            });
        });
    });
    </script>
</body>
</html>
