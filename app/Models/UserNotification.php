<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id','title','message','type','pass_id','is_read'
    ];
}
