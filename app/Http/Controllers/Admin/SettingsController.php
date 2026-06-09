<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\CampaignLog;
use App\Services\EmailProviders\EmailProviderManager;
use Aws\Ses\SesClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Resend;
use Throwable;

class SettingsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $settings = Setting::getAllKeyed();
        return view('admin.settings.index', compact('settings'));
    }

    // ── General Settings ──────────────────────────────────────────────────────

    public function updateGeneral(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'site_name'    => 'required|string|max:100',
            'site_tagline' => 'nullable|string|max:200',
            'admin_email'  => 'required|email',
            'timezone'     => 'required|string',
            'date_format'  => 'required|string',
            'app_url'      => 'nullable|url|max:255',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'favicon'      => 'nullable|image|mimes:jpg,jpeg,png,ico,svg|max:512',
        ]);

        if ($request->hasFile('logo')) {
            $old = Setting::get('logo');
            if ($old) Storage::disk('public')->delete($old);
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        } else {
            unset($data['logo']);
        }

        if ($request->hasFile('favicon')) {
            $old = Setting::get('favicon');
            if ($old) Storage::disk('public')->delete($old);
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        } else {
            unset($data['favicon']);
        }

        Setting::setMany($data);

        return back()->with('success_general', 'General settings saved successfully.');
    }

    // ── Queue Worker ──────────────────────────────────────────────────────────

    public function runQueueWorker(): \Illuminate\Http\JsonResponse
    {
        try {
            $pending = \Illuminate\Support\Facades\DB::table('jobs')->count();

            if (function_exists('exec')) {
                $php     = PHP_BINARY;
                $artisan = base_path('artisan');
                if (PHP_OS_FAMILY === 'Windows') {
                    pclose(popen("start /B \"{$php}\" \"{$artisan}\" queue:work --stop-when-empty --tries=3", 'r'));
                } else {
                    exec("nohup \"{$php}\" \"{$artisan}\" queue:work --stop-when-empty --tries=3 --timeout=60 > /dev/null 2>&1 &");
                }
                $note = 'Worker started in background.';
            } else {
                // Shared hosting: exec disabled — run synchronously
                set_time_limit(300);
                \Illuminate\Support\Facades\Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                    '--tries'           => 3,
                    '--timeout'         => 60,
                ]);
                $note = 'Jobs processed synchronously.';
            }

            return response()->json([
                'success' => true,
                'message' => "{$note} {$pending} job(s) were pending.",
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
    }

    // ── Email / SMTP Settings ─────────────────────────────────────────────────

    public function updateEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'mail_driver'     => 'required|in:smtp,ses,resend,sendmail,log',
            'smtp_host'       => 'nullable|string|max:255',
            'smtp_port'       => 'nullable|numeric|between:1,65535',
            'smtp_username'   => 'nullable|string|max:255',
            'smtp_password'   => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,starttls,none',
            'mail_from_email' => 'required|email',
            'mail_from_name'  => 'required|string|max:100',
        ]);

        Setting::setMany($data);

        return back()->with('success_email', 'Email settings saved successfully.');
    }

    public function testEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['test_to' => 'required|email']);

        Setting::applyMailConfig();

        try {
            Mail::raw('This is a test email from MailAuto. Your mail configuration is working correctly!', function ($msg) use ($request) {
                $msg->to($request->test_to)
                    ->subject('MailAuto — Test Email')
                    ->from(Setting::get('mail_from_email', config('mail.from.address')),
                           Setting::get('mail_from_name',  config('mail.from.name')));
            });

            return response()->json(['success' => true, 'message' => 'Test email sent successfully to ' . $request->test_to]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Active Email Provider (campaign sending engine) ──────────────────────

    public function updateProvider(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'active_email_provider' => 'required|in:ses,resend',
        ]);

        Setting::set('active_email_provider', $data['active_email_provider']);

        $label = $data['active_email_provider'] === 'ses' ? 'Amazon SES' : 'Resend';

        return response()->json([
            'success' => true,
            'message' => "{$label} is now the active campaign provider.",
            'active'  => $data['active_email_provider'],
        ]);
    }

    public function updateProviderFallback(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'email_fallback_enabled' => 'required|boolean',
            'backup_email_provider'  => 'nullable|in:ses,resend',
        ]);

        $enabled = $request->boolean('email_fallback_enabled');
        $primary = Setting::get('active_email_provider', 'ses');
        $backup  = $data['backup_email_provider'] ?? null;

        if ($enabled && (!$backup || $backup === $primary)) {
            return response()->json([
                'success' => false,
                'message' => 'Choose a backup provider that is different from the active provider to enable fallback.',
            ], 422);
        }

        Setting::setMany([
            'email_fallback_enabled' => $enabled ? '1' : '0',
            'backup_email_provider'  => $backup ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => $enabled
                ? 'Automatic fallback enabled — campaigns will retry through the backup provider if the active provider fails.'
                : 'Automatic fallback disabled.',
        ]);
    }

    public function testActiveProvider(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['test_to' => 'required|email']);

        $active = Setting::get('active_email_provider', 'ses');
        $label  = $active === 'ses' ? 'Amazon SES' : 'Resend';

        try {
            EmailProviderManager::send([
                'to'      => $request->test_to,
                'to_name' => '',
                'subject' => 'MailAuto — Test Email (Active Provider)',
                'html'    => "<p>This is a test email sent through your active campaign provider ({$label}). Your provider configuration is working correctly!</p>",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Test email sent successfully via {$label} to {$request->test_to}.",
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => "Send via {$label} failed: " . $e->getMessage()], 422);
        }
    }

    // ── Amazon SES Settings ───────────────────────────────────────────────────

    public function updateSes(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'ses_access_key'        => 'nullable|string|max:255',
            'ses_secret_key'        => 'nullable|string|max:255',
            'ses_region'            => 'nullable|string|max:50',
            'ses_verified_domain'   => 'nullable|string|max:255',
            'ses_sender_email'      => 'nullable|email',
            'ses_webhook_topic_arn' => 'nullable|string|max:255',
        ]);

        Setting::setMany($data);

        return back()->with('success_ses', 'Amazon SES settings saved successfully.');
    }

    public function testSes(): \Illuminate\Http\JsonResponse
    {
        $key    = Setting::get('ses_access_key');
        $secret = Setting::get('ses_secret_key');
        $region = Setting::get('ses_region', 'us-east-1');

        if (!$key || !$secret) {
            return response()->json(['success' => false, 'message' => 'AWS credentials are not configured.'], 422);
        }

        try {
            $client = new SesClient([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => ['key' => $key, 'secret' => $secret],
            ]);

            $quota = $client->getSendQuota();

            $max  = (float) $quota->get('Max24HourSend');
            $sent = (float) $quota->get('SentLast24Hours');

            return response()->json([
                'success' => true,
                'message' => "Connected to Amazon SES ({$region}). Sending quota: " . number_format($sent) . ' / ' . number_format($max) . ' used in the last 24 hours.',
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'SES connection failed: ' . $e->getMessage()], 422);
        }
    }

    // ── Resend Settings ───────────────────────────────────────────────────────

    public function updateResend(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'resend_api_key'        => 'nullable|string|max:255',
            'resend_sender_email'   => 'nullable|email',
            'resend_domain'         => 'nullable|string|max:255',
            'resend_webhook_secret' => 'nullable|string|max:255',
        ]);

        Setting::setMany($data);

        return back()->with('success_resend', 'Resend settings saved successfully.');
    }

    public function testResend(): \Illuminate\Http\JsonResponse
    {
        $apiKey = Setting::get('resend_api_key');

        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Resend API key is not configured.'], 422);
        }

        try {
            $domains = Resend::client($apiKey)->domains->list();
            $count   = count($domains['data'] ?? []);

            return response()->json([
                'success' => true,
                'message' => "Connected to Resend. {$count} domain(s) registered on this account.",
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Resend connection failed: ' . $e->getMessage()], 422);
        }
    }

    // ── Campaign Settings ─────────────────────────────────────────────────────

    public function updateCampaign(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'campaign_delay'            => 'required|integer|min:1|max:3600',
            'campaign_daily_limit'      => 'required|integer|min:1|max:100000',
            'campaign_max_per_campaign' => 'required|integer|min:1',
            'campaign_random_rotation'  => 'boolean',
            'campaign_retry_failed'     => 'boolean',
        ]);

        $data['campaign_random_rotation'] = $request->boolean('campaign_random_rotation') ? '1' : '0';
        $data['campaign_retry_failed']    = $request->boolean('campaign_retry_failed') ? '1' : '0';

        Setting::setMany($data);

        return back()->with('success_campaign', 'Campaign settings saved successfully.');
    }

    // ── Log Settings ──────────────────────────────────────────────────────────

    public function updateLog(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = [
            'log_enabled'        => $request->boolean('log_enabled') ? '1' : '0',
            'log_retention_days' => $request->validate(['log_retention_days' => 'required|integer|min:1|max:365'])['log_retention_days'],
            'log_auto_delete'    => $request->boolean('log_auto_delete') ? '1' : '0',
        ];

        Setting::setMany($data);

        return back()->with('success_log', 'Log settings saved successfully.');
    }

    // ── Security Settings ─────────────────────────────────────────────────────

    public function updateSecurity(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Password change
        if ($request->filled('password')) {
            $request->validate([
                'current_password'          => 'required|current_password',
                'password'                  => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
                'password_confirmation'      => 'required',
            ]);

            Auth::user()->update(['password' => Hash::make($request->password)]);
        }

        // 2FA toggle (future)
        Setting::set('enable_2fa', $request->boolean('enable_2fa') ? '1' : '0');

        return back()->with('success_security', 'Security settings saved.');
    }

    public function logoutDevices(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['confirm_logout' => 'accepted']);

        Auth::logoutOtherDevices($request->validate([
            'logout_password' => 'required|current_password',
        ])['logout_password']);

        return back()->with('success_security', 'All other sessions have been logged out.');
    }

}
