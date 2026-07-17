<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول خطط الأقساط (Installments)
 * يمثل خطة تقسيط مرتبطة بفاتورة معينة (المبلغ الإجمالي، الدفعة المقدمة، المتبقي).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            $table->decimal('total_amount', 14, 2);                    // المبلغ الإجمالي
            $table->decimal('down_payment', 14, 2)->default(0);        // الدفعة المقدمة
            $table->decimal('remaining_amount', 14, 2);                // المبلغ المتبقي
            $table->unsignedInteger('installments_count')->default(1); // عدد الأقساط

            $table->date('start_date');                                // تاريخ بداية خطة التقسيط
            $table->enum('status', ['active', 'completed', 'defaulted'])
                  ->default('active');                                 // حالة خطة التقسيط

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
