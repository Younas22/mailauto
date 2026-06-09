<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        try {
            Setting::applyMailConfig();

            // Override app URL for route() generation (important for email links)
            if ($appUrl = Setting::get('app_url')) {
                URL::forceRootUrl(rtrim($appUrl, '/'));
                if (str_starts_with($appUrl, 'https://')) {
                    URL::forceScheme('https');
                }
            }
        } catch (\Throwable) {
            // DB unavailable during fresh install / migrations — skip silently
        }
    }
}
