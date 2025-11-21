<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_type',
        'status',
        'description',
        'additional_data'
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    // İlişkiler
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
