<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\CampaignLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

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

    // ── Email / SMTP Settings ─────────────────────────────────────────────────

    public function updateEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'mail_driver'     => 'required|in:smtp,ses,sendmail,log',
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

    // ── Amazon SES Settings ───────────────────────────────────────────────────

    public function updateSes(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'ses_access_key'      => 'nullable|string|max:255',
            'ses_secret_key'      => 'nullable|string|max:255',
            'ses_region'          => 'nullable|string|max:50',
            'ses_verified_domain' => 'nullable|string|max:255',
            'ses_sender_email'    => 'nullable|email',
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

        // Future: use AWS SDK to call SES GetSendQuota
        return response()->json(['success' => true, 'message' => 'AWS credentials are present. Deploy the AWS SDK to verify the live connection.']);
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
