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
            $checkoutUrl = url('/cashfree-checkout');
        } else {
            $appId = static::getValue('cashfree_sandbox_app_id')
                ?: static::getValue('cashfree_app_id')
                ?: config('services.cashfree.app_id');
            $secretKey = static::getValue('cashfree_sandbox_secret_key')
                ?: static::getValue('cashfree_secret_key')
                ?: config('services.cashfree.secret_key');
            $baseUrl = 'https://sandbox.cashfree.com/pg/orders';
            $checkoutUrl = url('/cashfree-checkout');
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

    /**
     * Get Withdrawal configuration
     */
    public static function getWithdrawalConfig(): array
    {
        $enabledVal = static::getValue('withdrawal_enabled', '1');
        $autoVal = static::getValue('auto_withdrawal_enabled', '1');

        return [
            'enabled' => $enabledVal === '1' || $enabledVal === true || $enabledVal === 'true',
            'auto_payout_enabled' => $autoVal === '1' || $autoVal === true || $autoVal === 'true',
            'auto_payout_max_limit' => (float) (static::getValue('auto_withdrawal_max_limit', 1000) ?: 1000),
            'min_amount' => (float) (static::getValue('min_withdrawal_amount', 100) ?: 100),
            'max_amount' => (float) (static::getValue('max_withdrawal_amount', 50000) ?: 50000),
        ];
    }

    /**
     * Get dynamic Cashfree Payout configuration
     */
    public static function getCashfreePayoutConfig(): array
    {
        $environment = static::getValue('cashfree_payout_environment')
            ?: config('services.cashfree_payout.environment')
            ?: env('CASHFREE_PAYOUT_ENV', 'production');
        $isLive = in_array(strtolower((string) $environment), ['production', 'live']);

        if ($isLive) {
            $clientId = static::getValue('cashfree_payout_live_client_id')
                ?: static::getValue('cashfree_payout_client_id')
                ?: config('services.cashfree_payout.client_id')
                ?: env('CASHFREE_PAYOUT_CLIENT_ID');
            $clientSecret = static::getValue('cashfree_payout_live_client_secret')
                ?: static::getValue('cashfree_payout_client_secret')
                ?: config('services.cashfree_payout.client_secret')
                ?: env('CASHFREE_PAYOUT_CLIENT_SECRET');
            $baseUrl = static::getValue('cashfree_payout_live_base_url')
                ?: config('services.cashfree_payout.base_url')
                ?: env('CASHFREE_PAYOUT_BASE_URL')
                ?: 'https://payout-api.cashfree.com';
        } else {
            $clientId = static::getValue('cashfree_payout_sandbox_client_id')
                ?: static::getValue('cashfree_payout_client_id')
                ?: config('services.cashfree_payout.client_id')
                ?: env('CASHFREE_PAYOUT_CLIENT_ID');
            $clientSecret = static::getValue('cashfree_payout_sandbox_client_secret')
                ?: static::getValue('cashfree_payout_client_secret')
                ?: config('services.cashfree_payout.client_secret')
                ?: env('CASHFREE_PAYOUT_CLIENT_SECRET');
            $baseUrl = static::getValue('cashfree_payout_sandbox_base_url')
                ?: config('services.cashfree_payout.base_url')
                ?: env('CASHFREE_PAYOUT_BASE_URL')
                ?: 'https://payout-gamma.cashfree.com';
        }

        $fundSourceId = static::getValue('cashfree_payout_fundsource_id')
            ?: env('CASHFREE_PAYOUT_FUNDSOURCE_ID', 'LEGACY CARRY PRIVATE LIMITED');

        return [
            'environment'    => $isLive ? 'production' : 'sandbox',
            'is_live'        => $isLive,
            'client_id'      => (string) $clientId,
            'client_secret'  => (string) $clientSecret,
            'base_url'       => (string) $baseUrl,
            'fundsource_id'  => (string) $fundSourceId,
        ];
    }
}