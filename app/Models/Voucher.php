<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Voucher extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'voucher_type', 'voucher_number', 'patient_id', 'expense_category',
        'amount', 'currency', 'voucher_date', 'paid_via', 'description',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Voucher $voucher) {
            if (empty($voucher->voucher_number)) {
                $prefix = $voucher->voucher_type === 'receipt' ? 'RCP' : 'PAY';
                $voucher->voucher_number = $prefix . '-' . strtoupper(Str::random(8));
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->voucher_type === 'receipt' ? 'سند قبض' : 'سند صرف';
    }
}
