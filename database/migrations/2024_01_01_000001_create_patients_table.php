<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول المرضى (Patients)
 * يحتوي على السجل الطبي الرقمي الأساسي لكل مريض.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // ---------- بيانات المريض الأساسية ----------
            $table->string('name');                                   // اسم المريض
            $table->unsignedTinyInteger('age')->nullable();            // العمر
            $table->enum('gender', ['male', 'female']);                 // الجنس
            $table->string('phone')->nullable();                       // رقم الهاتف
            $table->string('address')->nullable();                     // السكن / العنوان

            // ---------- التاريخ الطبي العام ----------
            $table->boolean('has_diabetes')->default(false);           // مصاب بالسكري
            $table->boolean('has_hypertension')->default(false);       // مصاب بضغط الدم
            $table->boolean('has_allergy')->default(false);            // لديه حساسية
            $table->string('allergy_details')->nullable();             // تفاصيل الحساسية
            $table->boolean('is_pregnant')->nullable();                // حامل (للإناث فقط)
            $table->text('medical_notes')->nullable();                 // ملاحظات طبية إضافية

            $table->boolean('is_active')->default(true);               // هل المريض نشط في العيادة

            // ---------- حقول المزامنة الإجبارية (Offline-First Sync) ----------
            $table->uuid('local_id')->unique();                        // معرف فريد محلي لمنع تضارب البيانات
            $table->boolean('is_synced')->default(false);              // هل تمت مزامنته مع السيرفر
            $table->timestamp('synced_at')->nullable();                // وقت آخر مزامنة

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
