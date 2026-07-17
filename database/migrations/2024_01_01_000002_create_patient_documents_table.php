<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول أرشيف مستندات المريض (Patient Documents)
 * لتخزين صور الأشعة السينية وصور الفك قبل/بعد المعالجة.
 * ملاحظة: هذه المستندات تخضع لـ "وضع الستر" في الواجهة (class="sensitive-data").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            $table->string('file_path');                               // مسار الملف المخزن
            $table->enum('document_type', ['xray', 'before', 'after', 'other'])
                  ->default('other');                                  // نوع المستند
            $table->string('description')->nullable();                 // وصف مختصر
            $table->boolean('is_sensitive')->default(true);            // يخضع لوضع الستر افتراضياً

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
    }
};
