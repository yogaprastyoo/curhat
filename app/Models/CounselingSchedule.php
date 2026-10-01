<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselingSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'curhat_id',
        'counselor_id',
        'date',
        'start_time',
        'location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function curhat()
    {
        return $this->belongsTo(Curhat::class);
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
