<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['unique_key','module','action'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }
}
