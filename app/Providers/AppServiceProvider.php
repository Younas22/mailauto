<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        try {
            Setting::applyMailConfig();
        } catch (\Throwable) {
            // DB unavailable during fresh install / migrations — skip silently
        }
    }
}
