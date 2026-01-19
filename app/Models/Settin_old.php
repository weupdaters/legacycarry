<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settingss extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group_name'];

    // automatically cast JSON type
    protected $casts = [
        'value' => 'string', // treat as string by default; controller will json_decode if type=json
    ];

    public function getValueAttribute($val)
    {
        if ($this->attributes['type'] === 'json') {
            return json_decode($val, true);
        }
        if ($this->attributes['type'] === 'boolean') {
            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->attributes['type'] === 'integer') {
            return (int) $val;
        }
        return $val;
    }

    public function setValueAttribute($val)
    {
        if (($this->attributes['type'] ?? null) === 'json') {
            $this->attributes['value'] = is_string($val) ? $val : json_encode($val);
        } else {
            $this->attributes['value'] = is_array($val) ? json_encode($val) : (string)$val;
        }
    }
}
