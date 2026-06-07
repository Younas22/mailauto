<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unsubscribed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-md w-full text-center">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-800 mb-2">Successfully Unsubscribed</h1>
        <p class="text-slate-500 text-sm">
            <strong>{{ $contact->email }}</strong> has been removed from our mailing list.
            You will no longer receive emails from us.
        </p>
        <p class="mt-5 text-xs text-slate-400">
            Per GDPR compliance, your removal is effective immediately.
        </p>
    </div>
</body>
</html>
