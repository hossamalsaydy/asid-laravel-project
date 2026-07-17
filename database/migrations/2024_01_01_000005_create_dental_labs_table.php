<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول معامل ومختبرات الأسنان الخارجية (Dental Labs)
 * لمتابعة التركيبات والتقويم المُرسل للمعامل الخارجية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_labs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->nullable()
                  ->constrained('patients')->nullOnDelete();

            $table->string('lab_name');                                // اسم المختبر
            $table->string('patient_name_snapshot')->nullable();       // اسم المريض (نسخة احتياطية وقت الإرسال)
            $table->string('tooth_number')->nullable();                // رقم السن
            $table->string('restoration_type');                        // نوع التركيبة المطلوبة

            $table->decimal('agreed_cost', 12, 2)->default(0);         // التكلفة المتفق عليها مع المختبر

            $table->date('sent_date');                                 // تاريخ الإرسال
            $table->date('expected_receive_date')->nullable();         // تاريخ الاستلام المتوقع
            $table->date('actual_receive_date')->nullable();           // تاريخ الاستلام الفعلي

            $table->enum('status', ['sent', 'in_progress', 'received', 'needs_modification'])
                  ->default('sent');                                   // حالة العمل

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
        Schema::dropIfExists('dental_labs');
    }
};
