<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalLab extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'patient_id', 'lab_name', 'patient_name_snapshot', 'tooth_number',
        'restoration_type', 'agreed_cost', 'sent_date', 'expected_receive_date',
        'actual_receive_date', 'status', 'notes',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'sent_date' => 'date',
        'expected_receive_date' => 'date',
        'actual_receive_date' => 'date',
        'agreed_cost' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'sent'                => 'تم الإرسال',
            'in_progress'         => 'قيد التجهيز',
            'received'            => 'تم الاستلام',
            'needs_modification'  => 'يحتاج تعديل',
            default               => $this->status,
        };
    }
}
