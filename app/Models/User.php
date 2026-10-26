<?php

namespace App\Models;

use App\Models\Flat;
use App\Models\JobPost;
use App\Models\ResidentAssignment;
use App\Models\Society;
use App\Models\UserDetail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    use HasFactory;

    protected $appends = ['avatar_url'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_society_id',
        'user_residential_status',
        'password',
        'role',
        'is_active',
        'verification_status',
        'aadhaar_number',
        'avatar',
        'region',
        'fcm_token',
        'language',
        'last_seen_at',
        'last_active_endpoint',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'last_seen_at' => 'datetime:Y-m-d H:i:s',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function isOnline(): bool
    {
        return \Illuminate\Support\Facades\Cache::has('user_online_' . $this->id);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /* ---------------------------------------------------------
        ROLE HELPERS
    --------------------------------------------------------- */

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isResident()
    {
        return $this->role === 'customer';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }


    /* ---------------------------------------------------------
        DETAILS (OLD SYSTEM) – Keep for backward support
    --------------------------------------------------------- */
    public function details()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }


    /* ---------------------------------------------------------
        EMPLOYEE RELATION
    --------------------------------------------------------- */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }


    /* ---------------------------------------------------------
        JOB POSTS BY USER
    --------------------------------------------------------- */
    public function jobs()
    {
        return $this->hasMany(JobPost::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(UserDocument::class, 'user_id');
    }


    /* ---------------------------------------------------------
        NEW SSTRUCTURE: RESIDENT → SOCIETY ASSIGNMENT
    --------------------------------------------------------- */

    // The assignment row (user → society)
    public function assignment()
    {
        return $this->hasOne(ResidentAssignment::class, 'user_id');
    }

    public function residentAssignment()
    {
        return $this->hasOne(ResidentAssignment::class, 'user_id');
    }

    public function society()
    {
        return $this->belongsTo(Society::class, 'user_society_id');
    }

    // Direct access to assigned society
    public function assignedSociety()
    {
        return $this->hasOneThrough(
            Society::class,
            ResidentAssignment::class,   // ResidentAssignment.user_id
            'id',          // Society.id
            'user_id',          // Users.id
            'name',        // ResidentAssignment.user_id
            'society_id'   // ResidentAssignment.society_id
        );
    }

    public function guardShifts()
    {
        return $this->hasMany(GuardShift::class, 'guard_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function vehicleLogs()
    {
        return $this->hasMany(VehicleLog::class);
    }

    public function userDetails()
    {
        // user_details.user_id → users.id
        return $this->hasOne(UserDetail::class, 'user_id', 'id');
    }

    public function amenityBookings()
    {
        return $this->hasMany(AmenityBooking::class);
    }

    public function pollVotes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return asset('storage/' . $this->avatar);
    }
}
