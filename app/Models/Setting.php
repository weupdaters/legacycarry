<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
        'description',
        'type',
        'options'
    ];

    protected $casts = [
        'options' => 'array',
        'value' => 'string'
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value, $group = null)
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        if (empty($setting->label)) {
            $setting->label = \Illuminate\Support\Str::headline($key);
        }
        if (empty($setting->type)) {
            $setting->type = 'text';
        }
        if ($group && (empty($setting->group) || $setting->group === 'general')) {
            $setting->group = $group;
        }
        return $setting->save();
    }

    /**
     * Get all settings grouped by group
     */
    public static function grouped()
    {
        return static::all()->groupBy('group');
    }

    /**
     * Get Indian currency formatted value
     */
    /**
     * Get dynamic Cashfree configuration based on environment mode
     */
    public static function getCashfreeConfig(): array
    {
        $environment = static::getValue('cashfree_environment') ?: config('services.cashfree.environment', 'sandbox');
        $isLive = in_array(strtolower($environment), ['production', 'live']);

        if ($isLive) {
            $appId = static::getValue('cashfree_live_app_id')
                ?: static::getValue('cashfree_app_id')
                ?: config('services.cashfree.app_id');
            $secretKey = static::getValue('cashfree_live_secret_key')
                ?: static::getValue('cashfree_secret_key')
                ?: config('services.cashfree.secret_key');
            $baseUrl = 'https://api.cashfree.com/pg/orders';
            $checkoutUrl = 'https://payments.cashfree.com/pg/view';
        } else {
            $appId = static::getValue('cashfree_sandbox_app_id')
                ?: static::getValue('cashfree_app_id')
                ?: config('services.cashfree.app_id');
            $secretKey = static::getValue('cashfree_sandbox_secret_key')
                ?: static::getValue('cashfree_secret_key')
                ?: config('services.cashfree.secret_key');
            $baseUrl = 'https://sandbox.cashfree.com/pg/orders';
            $checkoutUrl = 'https://sandbox.cashfree.com/pg/view';
        }

        return [
            'environment'  => $isLive ? 'production' : 'sandbox',
            'is_live'      => $isLive,
            'app_id'       => $appId,
            'secret_key'   => $secretKey,
            'base_url'     => $baseUrl,
            'checkout_url' => $checkoutUrl,
        ];
    }
}