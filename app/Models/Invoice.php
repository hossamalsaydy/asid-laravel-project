<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'patient_id', 'invoice_number', 'currency', 'total_amount',
        'discount', 'final_amount', 'invoice_date', 'notes',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // توليد رقم فاتورة تلقائي عند عدم تحديده يدوياً (مثال: INV-7F3A2C1B)
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . strtoupper(Str::random(8));
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function installment(): HasOne
    {
        return $this->hasOne(Installment::class);
    }

    /**
     * إجمالي المدفوع فعلياً على هذه الفاتورة عبر سندات القبض المرتبطة بنفس المريض
     * (يُستخدم كتقدير عام؛ الربط الدقيق يتم عبر خطة التقسيط installment إن وجدت).
     */
    public function getRemainingAmountAttribute(): float
    {
        if ($this->installment) {
            return (float) $this->installment->remaining_amount;
        }

        return (float) $this->final_amount;
    }
}
