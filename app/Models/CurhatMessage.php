<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurhatMessage extends Model
{
    protected $fillable = [
        'curhat_id',
        'user_id',
        'message',
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
