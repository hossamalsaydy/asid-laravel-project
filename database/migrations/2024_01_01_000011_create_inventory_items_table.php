<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول أصناف المخزن الطبي (Inventory Items)
 * المواد المستهلكة في العيادة (إبر، مواد حشوات، معقمات، قفازات...).
 * يحتوي على حد أدنى (minimum_quantity) لتفعيل تنبيهات نفاد المخزون.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');                                    // اسم الصنف
            $table->string('unit')->default('piece');                  // وحدة القياس (قطعة، علبة، عبوة...)
            $table->decimal('current_quantity', 12, 2)->default(0);    // الكمية الحالية
            $table->decimal('minimum_quantity', 12, 2)->default(0);    // الحد الأدنى للتنبيه
            $table->decimal('unit_cost', 12, 2)->nullable();           // تكلفة الوحدة الواحدة
            $table->text('notes')->nullable();

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
