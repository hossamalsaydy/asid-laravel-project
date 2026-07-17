<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installment extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'invoice_id', 'patient_id', 'total_amount', 'down_payment',
        'remaining_amount', 'installments_count', 'start_date', 'status',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'total_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    /**
     * إعادة حساب المبلغ المتبقي وتحديث حالة الخطة بناءً على الدفعات المسددة.
     */
    public function recalculate(): void
    {
        $paidTotal = $this->payments()->sum('paid_amount');
        $remaining = max(0, $this->total_amount - $this->down_payment - $paidTotal);

        $this->update([
            'remaining_amount' => $remaining,
            'status' => $remaining <= 0 ? 'completed' : $this->status,
        ]);
    }
}
