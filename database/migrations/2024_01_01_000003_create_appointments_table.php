<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول المواعيد (Appointments)
 * يعتمد نظام الفترتين (صباحي/مسائي) مع منع الحجز في وقت القيلولة (1 ظهراً - 4 عصراً).
 * ملاحظة: منع الحجز في وقت القيلولة يُطبّق برمجياً عبر Form Request / Validation Rule
 * عند إنشاء الموعد، وليس عبر قيد في قاعدة البيانات.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            $table->date('appointment_date');                          // تاريخ الموعد
            $table->time('appointment_time');                          // وقت الموعد
            $table->enum('period', ['morning', 'evening']);            // الفترة: صباحي / مسائي

            $table->enum('status', ['pending', 'arrived', 'completed', 'cancelled'])
                  ->default('pending');                                // حالة الموعد

            $table->text('notes')->nullable();                         // ملاحظات الموعد

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            // فهرس لتسريع البحث عن مواعيد يوم معين
            $table->index(['appointment_date', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
