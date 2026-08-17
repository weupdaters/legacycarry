<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_type',
        'description',
        'email',
        'status',
        'reference',
        'admin_notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
