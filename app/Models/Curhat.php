<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curhat extends Model
{
    use HasFactory;

    /**
     * Label status yang ditampilkan ke pengguna.
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        'submitted' => 'Menunggu Review',
        'reviewed' => 'Sudah Dibaca',
        'responded' => 'Sudah Ditanggapi',
        'scheduled' => 'Konseling Dijadwalkan',
        'in_counseling' => 'Dalam Pendampingan',
        'completed' => 'Selesai',
    ];

    /**
     * Warna badge AdminLTE untuk setiap status.
     *
     * @var array<string, string>
     */
    public const STATUS_BADGES = [
        'submitted' => 'warning',
        'reviewed' => 'info',
        'responded' => 'primary',
        'scheduled' => 'secondary',
        'in_counseling' => 'dark',
        'completed' => 'success',
    ];

    protected $fillable = [
        'curhat_number',
        'student_id',
        'category_id',
        'title',
        'message',
        'privacy_scope',
        'counseling_request',
        'status',
        'assigned_to',
        'reviewed_at',
        'completed_at',
    ];

    /**
     * Status yang boleh dituju dari setiap status.
     *
     * @var array<string, list<string>>
     */
    public const STATUS_TRANSITIONS = [
        'submitted' => ['reviewed'],
        'reviewed' => ['responded', 'scheduled', 'in_counseling'],
        'responded' => ['scheduled', 'in_counseling', 'completed'],
        'scheduled' => ['in_counseling', 'completed'],
        'in_counseling' => ['scheduled', 'completed'],
        'completed' => [],
    ];

    /**
     * Generate a yearly curhat number (e.g. CR-2026-000001) that contains no student data.
     */
    protected static function booted(): void
    {
        static::creating(function (Curhat $curhat) {
            $prefix = 'CR-'.now()->year.'-';

            $lastNumber = static::where('curhat_number', 'like', $prefix.'%')
                ->orderByDesc('curhat_number')
                ->value('curhat_number');

            $sequence = $lastNumber ? ((int) substr($lastNumber, strlen($prefix))) + 1 : 1;

            $curhat->curhat_number = $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
        });
    }

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'light';
    }

    /**
     * Daftar status yang boleh dipilih dari status saat ini.
     *
     * @return list<string>
     */
    public function allowedNextStatuses(): array
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? [];
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, $this->allowedNextStatuses(), true);
    }

    /**
     * Ubah status sesuai transition map, sekaligus isi reviewed_at / completed_at.
     */
    public function transitionTo(string $newStatus): void
    {
        if (! $this->canTransitionTo($newStatus)) {
            throw new \DomainException("Status tidak bisa diubah dari '{$this->status}' ke '{$newStatus}'.");
        }

        $this->status = $newStatus;

        if ($newStatus === 'reviewed') {
            $this->reviewed_at = now();
        }

        if ($newStatus === 'completed') {
            $this->completed_at = now();
        }

        $this->save();
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function category()
    {
        return $this->belongsTo(CurhatCategory::class, 'category_id');
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(CurhatMessage::class);
    }

    public function histories()
    {
        return $this->hasMany(CurhatHistory::class);
    }

    public function schedules()
    {
        return $this->hasMany(CounselingSchedule::class);
    }
}
