<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unsubscribe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-md w-full text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>

        @if($contact->isUnsubscribed())
        <h1 class="text-xl font-bold text-slate-800 mb-2">Already Unsubscribed</h1>
        <p class="text-slate-500 text-sm">
            <strong>{{ $contact->email }}</strong> was already removed from our mailing list.
        </p>
        @else
        <h1 class="text-xl font-bold text-slate-800 mb-2">Unsubscribe from Emails</h1>
        <p class="text-slate-500 text-sm mb-6">
            You are about to unsubscribe <strong>{{ $contact->email }}</strong> from all future emails.
            This action cannot be undone automatically.
        </p>

        <form method="POST" action="{{ route('unsubscribe.process', $token) }}">
            @csrf
            <button type="submit"
                    class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition text-sm">
                Yes, unsubscribe me
            </button>
        </form>
        <p class="mt-4 text-xs text-slate-400">
            If you received this email by mistake, simply ignore this page.
        </p>
        @endif
    </div>
</body>
</html>
