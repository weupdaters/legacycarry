<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentAssignment extends Model
{
    protected $fillable = [
        'user_id', 'society_id', 'flat_id', 'assigned_by', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function passes()
    {
        return $this->hasMany(Pass::class);
    }
}
