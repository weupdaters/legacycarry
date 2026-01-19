<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flat extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id',
        'building_no',
        'owner_name',
        'status',
    ];

    /**
     * Relationship: Flat belongs to a Society
     */
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function userDetails()
    {
        return $this->hasMany(UserDetail::class, 'flat_id', 'id');
    }
}
