<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — MailAuto</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..800;1,14..32,300..800&display=swap" rel="stylesheet" />

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                    }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        body { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.4s ease both; }
        .fade-up-delay { animation: fadeUp 0.4s 0.08s ease both; }

        input:focus { outline: none; }
        input:focus-visible { outline: 2px solid #6366f1; outline-offset: 0; }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            box-shadow: 0 4px 20px rgba(99,102,241,0.45);
            transform: translateY(-1px);
        }
        .btn-primary:active { transform: translateY(0); }

        .input-field {
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .input-field:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4 sm:p-6">

    {{-- Background decoration --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-32 w-[600px] h-[600px] rounded-full bg-brand-100/60 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-32 w-[500px] h-[500px] rounded-full bg-indigo-100/50 blur-3xl"></div>
    </div>

    {{-- Card --}}
    <div class="w-full max-w-[420px] fade-up">

        {{-- Branding --}}
        <div class="text-center mb-8 fade-up">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 shadow-lg shadow-brand-300/40 mb-5">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">MailAuto</h1>
            <p class="text-sm text-slate-500 mt-1.5">Sign in to your admin panel</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-7 sm:p-8 fade-up-delay">

            {{-- Error banner --}}
            @if ($errors->any())
            <div class="mb-5 flex items-start gap-3 rounded-xl bg-red-50 border border-red-100 px-4 py-3.5">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-600 font-medium">{{ $errors->first('email') }}</p>
            </div>
            @endif

            {{-- Session status (e.g. logged out message) --}}
            @if (session('status'))
            <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3.5">
                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm text-emerald-700 font-medium">{{ session('status') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-semibold text-slate-700">
                        Email address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="admin@example.com"
                        class="input-field w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 @error('email') border-red-300 bg-red-50 @enderror"
                    />
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <label for="password" class="block text-sm font-semibold text-slate-700">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="input-field w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-11 text-sm text-slate-900 placeholder-slate-400"
                        />
                        {{-- Toggle password visibility --}}
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1"
                            tabindex="-1"
                        >
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-off-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded border-slate-300 text-brand-600 accent-brand-600 cursor-pointer"
                        />
                        <span class="text-sm text-slate-600">Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="btn-primary w-full rounded-xl py-3 text-sm font-semibold text-white shadow-sm shadow-brand-200"
                >
                    Sign in to Admin Panel
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-slate-400 mt-6">
            © {{ date('Y') }} MailAuto. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
