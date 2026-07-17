<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'invoice_id', 'dental_treatment_id', 'description', 'amount',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }
}
