<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\EmailListController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\UnsubscribeController;

Route::get('/', function () {
    return redirect()->route('login');
});

// ── Unsubscribe (public) ──────────────────────────────────────────────────────
Route::get('/unsubscribe/{token}',  [UnsubscribeController::class, 'show'])->name('unsubscribe.show');
Route::post('/unsubscribe/{token}', [UnsubscribeController::class, 'process'])->name('unsubscribe.process');

// ── Auth (guest only) ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Admin (auth protected) ────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Email Templates
    Route::resource('templates', TemplateController::class)->except(['show']);

    // Email Lists & Groups
    Route::get('/email-lists',          [EmailListController::class, 'index'])->name('email-lists.index');
    Route::get('/email-lists/import',   [EmailListController::class, 'showImport'])->name('email-lists.import');
    Route::post('/email-lists/import',  [EmailListController::class, 'import'])->name('email-lists.store');
    Route::delete('/email-lists/{emailList}', [EmailListController::class, 'destroy'])->name('email-lists.destroy');
    Route::get('/email-lists/sample',   [EmailListController::class, 'sampleCsv'])->name('email-lists.sample');

    // Email Logs
    Route::get('/email-logs',        [EmailLogController::class, 'index'])->name('email-logs.index');
    Route::get('/email-logs/export', [EmailLogController::class, 'export'])->name('email-logs.export');

    // Settings
    Route::get('/settings',                           [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/general',                  [SettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::post('/settings/email',                    [SettingsController::class, 'updateEmail'])->name('settings.email');
    Route::post('/settings/email/test',               [SettingsController::class, 'testEmail'])->name('settings.email.test');
    Route::post('/settings/ses',                      [SettingsController::class, 'updateSes'])->name('settings.ses');
    Route::post('/settings/ses/test',                 [SettingsController::class, 'testSes'])->name('settings.ses.test');
    Route::post('/settings/campaign',                 [SettingsController::class, 'updateCampaign'])->name('settings.campaign');
    Route::post('/settings/log',                      [SettingsController::class, 'updateLog'])->name('settings.log');
    Route::post('/settings/security',                 [SettingsController::class, 'updateSecurity'])->name('settings.security');
    Route::post('/settings/security/logout-devices',  [SettingsController::class, 'logoutDevices'])->name('settings.logout-devices');

    // Campaigns
    Route::get('/campaigns',                          [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create',                   [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns',                         [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}',               [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/edit',          [CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::put('/campaigns/{campaign}',               [CampaignController::class, 'update'])->name('campaigns.update');
    Route::post('/campaigns/{campaign}/start',        [CampaignController::class, 'start'])->name('campaigns.start');
    Route::post('/campaigns/{campaign}/pause',        [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('/campaigns/{campaign}/resume',       [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::get('/campaigns/{campaign}/progress',      [CampaignController::class, 'progress'])->name('campaigns.progress');
    Route::delete('/campaigns/{campaign}',            [CampaignController::class, 'destroy'])->name('campaigns.destroy');

});
