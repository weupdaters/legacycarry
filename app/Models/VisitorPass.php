<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VisitorPass extends Model
{
    protected $fillable = [
        'user_id','employer_id','purpose_id','visitor_name','contact_no','society',
        'valid_from','valid_until','status','completed_at','vehicle','photo_path','notes'
    ];

    protected $casts = [
        'vehicle' => 'array',
        'valid_from' => 'datetime:Y-m-d H:i:s',
        'valid_until' => 'datetime:Y-m-d H:i:s',
        'completed_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function purpose()
    {
        return $this->belongsTo(Purpose::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id');
    }

    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class);
    }
}
