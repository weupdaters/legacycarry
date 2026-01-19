<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $table = 'user_details';

    protected $fillable = [
        'user_id',
        'society_id',
        'flat_id',
        'floor_number',
        'full_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'occupation',
        'marital_status',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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
        // user_details.flat_number → flats.id
        return $this->belongsTo(Flat::class, 'flat_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Fetch a resident by user_id only if:
     * - belongs to the given society
     * - role = resident
     * - with relations: user, society, flat
     */
    public function scopeResidentInSociety($query, $residentUserId, $societyId)
    {
        return $query->where('user_id', $residentUserId)
            ->where('society_id', $societyId)
            ->whereHas('user', function ($q) {
                $q->where('role', 'resident');
            })
            ->with(['user', 'society', 'flat']);
    }

    public function pass()
    {
        return $this->hasOne(Pass::class, 'created_by');
    }


}
