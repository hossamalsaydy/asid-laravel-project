<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'name', 'unit', 'current_quantity', 'minimum_quantity', 'unit_cost',
        'notes', 'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * هل الكمية الحالية وصلت أو اقتربت من الحد الأدنى؟ (لتنبيهات نفاد المخزون)
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_quantity <= $this->minimum_quantity;
    }
}
