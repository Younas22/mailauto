<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainHealthCheck;
use App\Services\DnsCheckerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliverabilityController extends Controller
{
    public function __construct(private DnsCheckerService $dns) {}

    public function dnsCheck(Request $request): View
    {
        $domain  = null;
        $result  = null;
        $history = DomainHealthCheck::orderByDesc('checked_at')->limit(10)->get();

        if ($request->filled('domain')) {
            $domain = strtolower(trim($request->input('domain')));
            $force  = $request->boolean('force');

            $result = $this->dns->check($domain, $force);

            DomainHealthCheck::updateOrCreate(
                ['domain' => $domain],
                [
                    'spf_status'    => $result['spf']['status'],
                    'dkim_status'   => $result['dkim']['status'],
                    'dmarc_status'  => $result['dmarc']['status'],
                    'spf_record'    => $result['spf']['record'],
                    'dkim_record'   => $result['dkim']['record'],
                    'dmarc_record'  => $result['dmarc']['record'],
                    'dkim_selector' => $result['dkim']['selector'] ?? null,
                    'checked_at'    => now(),
                ]
            );

            $history = DomainHealthCheck::orderByDesc('checked_at')->limit(10)->get();
        }

        return view('admin.deliverability.dns-check', compact('domain', 'result', 'history'));
    }

    public function dnsCheckRecheck(Request $request): RedirectResponse
    {
        $request->validate(['domain' => 'required|string|max:253']);

        return redirect()->route('admin.deliverability.dns-check', [
            'domain' => $request->input('domain'),
            'force'  => 1,
        ]);
    }
}
