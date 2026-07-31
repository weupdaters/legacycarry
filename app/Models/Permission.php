<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['permission_code', 'module', 'name', 'label'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }
}
