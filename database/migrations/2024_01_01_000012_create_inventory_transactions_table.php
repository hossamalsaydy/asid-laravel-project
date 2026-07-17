<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول حركات المخزن (Inventory Transactions)
 * يسجل كل عملية توريد (in) أو صرف/استهلاك (out) لصنف معين،
 * وتُستخدم هذه الحركات لتحديث current_quantity في جدول inventory_items.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();

            $table->enum('transaction_type', ['in', 'out']);           // نوع الحركة: وارد / صادر
            $table->decimal('quantity', 12, 2);                        // الكمية
            $table->date('transaction_date');                          // تاريخ الحركة
            $table->text('notes')->nullable();                         // سبب الحركة (شراء، استهلاك يومي...)

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
