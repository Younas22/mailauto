<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected const ENCRYPTED_KEYS = [
        'smtp_password',
        'smtp_username',
        'ses_access_key',
        'ses_secret_key',
    ];

    private static function encrypt(string $key, mixed $value): mixed
    {
        if (in_array($key, self::ENCRYPTED_KEYS) && !empty($value)) {
            return Crypt::encryptString((string) $value);
        }
        return $value;
    }

    private static function decrypt(string $key, mixed $value): mixed
    {
        if (in_array($key, self::ENCRYPTED_KEYS) && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (DecryptException) {
                // Value stored in plaintext (pre-encryption) — return as-is
                return $value;
            }
        }
        return $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            $value   = $setting ? $setting->value : $default;
            return static::decrypt($key, $value);
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => static::encrypt($key, $value)]);
        Cache::forget("setting:{$key}");
        Cache::forget('settings:all_keyed');
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }

    public static function all($columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return parent::all($columns);
    }

    public static function getAllKeyed(): array
    {
        return Cache::remember('settings:all_keyed', 60, function () {
            return static::all()->mapWithKeys(function ($s) {
                return [$s->key => static::decrypt($s->key, $s->value)];
            })->toArray();
        });
    }

    public static function applyMailConfig(): void
    {
        $s = static::getAllKeyed();

        $driver = $s['mail_driver'] ?? 'smtp';

        config([
            'mail.default'                 => $driver,
            'mail.from.address'            => $s['mail_from_email']  ?? config('mail.from.address'),
            'mail.from.name'               => $s['mail_from_name']   ?? config('mail.from.name'),
            'mail.mailers.smtp.host'       => $s['smtp_host']        ?? null,
            'mail.mailers.smtp.port'       => (int) ($s['smtp_port'] ?? 587),
            'mail.mailers.smtp.username'   => $s['smtp_username']    ?? null,
            'mail.mailers.smtp.password'   => $s['smtp_password']    ?? null,
            'mail.mailers.smtp.encryption' => $s['smtp_encryption']  ?? 'tls',
            'services.ses.key'             => $s['ses_access_key']   ?? null,
            'services.ses.secret'          => $s['ses_secret_key']   ?? null,
            'services.ses.region'          => $s['ses_region']       ?? 'us-east-1',
        ]);

        // Clear any cached mailer instances so the new config takes effect immediately
        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    }
}
