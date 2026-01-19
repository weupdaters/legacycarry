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
    public static function setValue($key, $value)
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
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
    public function getFormattedValueAttribute()
    {
        if ($this->type === 'amount') {
            return '₹ ' . number_format($this->value, 2);
        }
        return $this->value;
    }
}