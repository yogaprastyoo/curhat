<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurhatAccessLog extends Model
{
    protected $fillable = [
        'curhat_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    public function curhat()
    {
        return $this->belongsTo(Curhat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
