<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Society extends Model
{
    protected $fillable = ['name', 'address', 'country_id', 'city_id', 'state_id', 'pincode'];

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function flats()
    {
        return $this->hasMany(Flat::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function jobs()
    {
        return $this->hasMany(JobPost::class);
    }

    public function amenities()
    {
        return $this->hasMany(Amenity::class);
    }

    public function amenityBookings()
    {
        return $this->hasMany(AmenityBooking::class);
    }

    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    public function pollVotes()
    {
        return $this->hasMany(PollVote::class);
    }
}
