<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'patient_id', 'appointment_date', 'appointment_time', 'period',
        'status', 'notes', 'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    // ---------------------- Scopes ----------------------

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    // ---------------------- Accessors ----------------------

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'قيد الانتظار',
            'arrived'   => 'دخل العيادة',
            'completed' => 'تم الانتهاء',
            'cancelled' => 'ملغي',
            default     => $this->status,
        };
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->period === 'morning' ? 'صباحي' : 'مسائي';
    }
}
