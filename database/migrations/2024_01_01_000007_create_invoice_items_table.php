<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول بنود الفاتورة (Invoice Items)
 * كل بند يمثل معالجة سنية واحدة مرتبطة بالفاتورة (أو بند حر بدون ربط بمعالجة).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('dental_treatment_id')->nullable()
                  ->constrained('dental_treatments')->nullOnDelete();

            $table->string('description');                             // وصف البند
            $table->decimal('amount', 14, 2);                          // قيمة البند

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
