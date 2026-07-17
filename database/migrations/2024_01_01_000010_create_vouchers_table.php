<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول السندات (Vouchers)
 * جدول موحد لسندات القبض (المبالغ المستلمة من المرضى) وسندات الدفع
 * (المصاريف اليومية للعيادة مثل الإيجار، الكهرباء، الديزل).
 * التمييز بينهما عبر حقل voucher_type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            $table->enum('voucher_type', ['receipt', 'payment']);      // نوع السند: قبض / صرف
            $table->string('voucher_number')->unique();                 // رقم السند

            // مرتبط بمريض في حال كان سند قبض، ويكون فارغاً في سندات الصرف
            $table->foreignId('patient_id')->nullable()
                  ->constrained('patients')->nullOnDelete();

            // تصنيف المصروف في حال كان سند صرف (إيجار، كهرباء، ديزل، رواتب...)
            $table->string('expense_category')->nullable();

            $table->decimal('amount', 14, 2);                          // قيمة السند
            $table->enum('currency', ['YER', 'SAR', 'USD'])->default('YER');

            $table->date('voucher_date');                              // تاريخ السند
            $table->string('paid_via')->nullable();                    // طريقة الدفع (نقدي، تحويل...)
            $table->text('description')->nullable();                   // بيان السند

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->index(['voucher_type', 'voucher_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
