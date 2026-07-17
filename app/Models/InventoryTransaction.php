<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'inventory_item_id', 'transaction_type', 'quantity', 'transaction_date',
        'notes', 'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'decimal:2',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // عند تسجيل أي حركة (وارد/صادر)، يتم تحديث الكمية الحالية للصنف تلقائياً
        static::created(function (InventoryTransaction $transaction) {
            $item = $transaction->inventoryItem;

            $newQuantity = $transaction->transaction_type === 'in'
                ? $item->current_quantity + $transaction->quantity
                : $item->current_quantity - $transaction->quantity;

            $item->update(['current_quantity' => max(0, $newQuantity)]);
        });
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
