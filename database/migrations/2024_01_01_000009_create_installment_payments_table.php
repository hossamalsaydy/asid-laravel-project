<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول دفعات الأقساط (Installment Payments)
 * جدول الزيارات والدفعات المرتبطة بخطة تقسيط معينة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('installment_id')->constrained('installments')->cascadeOnDelete();

            $table->date('due_date');                                  // تاريخ استحقاق الدفعة
            $table->decimal('amount', 14, 2);                          // قيمة القسط المستحق
            $table->decimal('paid_amount', 14, 2)->default(0);         // المبلغ المدفوع فعلياً
            $table->date('paid_at')->nullable();                       // تاريخ السداد الفعلي

            $table->enum('status', ['pending', 'paid', 'overdue'])
                  ->default('pending');                                // حالة الدفعة

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
    }
};
