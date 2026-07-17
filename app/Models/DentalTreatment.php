<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DentalTreatment extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'patient_id', 'tooth_number', 'dentition_type', 'treatment_type',
        'treatment_notes', 'treatment_date', 'cost', 'payment_status',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'cost' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
