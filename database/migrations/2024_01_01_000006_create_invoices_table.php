<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول الفواتير (Invoices)
 * فاتورة تصدر لكل مريض بناءً على المعالجات التي تمت، وتدعم تعدد العملات
 * (مناسب لتقلبات السوق اليمني بين الريال اليمني والريال السعودي والدولار).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            $table->string('invoice_number')->unique();                // رقم الفاتورة
            $table->enum('currency', ['YER', 'SAR', 'USD'])
                  ->default('YER');                                    // عملة الفاتورة

            $table->decimal('total_amount', 14, 2)->default(0);        // إجمالي المبلغ قبل الخصم
            $table->decimal('discount', 14, 2)->default(0);            // قيمة الخصم
            $table->decimal('final_amount', 14, 2)->default(0);        // المبلغ النهائي بعد الخصم

            $table->date('invoice_date');                              // تاريخ الفاتورة
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
        Schema::dropIfExists('invoices');
    }
};
