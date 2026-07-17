<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentPayment extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'installment_id', 'due_date', 'amount', 'paid_amount', 'paid_at',
        'status', 'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'مستحق',
            'paid'    => 'مسدد',
            'overdue' => 'متأخر',
            default   => $this->status,
        };
    }
}
