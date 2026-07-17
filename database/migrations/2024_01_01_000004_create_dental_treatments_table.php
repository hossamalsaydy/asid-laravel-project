<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول المعالجات السنية (Dental Treatments)
 * يسجل كل معالجة تمت على سن معين من مخطط الأسنان التفاعلي.
 * ترقيم الأسنان: نستخدم الترقيم العالمي (Universal Numbering):
 *   - البالغين: من 1 إلى 32
 *   - الأطفال: من A إلى T (أو 1-20 حسب الترميز الذي تعتمده الواجهة)
 * لذلك نخزن رقم السن كنص (string) ليدعم الحالتين معاً.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            $table->string('tooth_number');                            // رقم السن (يدعم البالغين والأطفال)
            $table->enum('dentition_type', ['adult', 'child'])
                  ->default('adult');                                  // نوع الأسنان (بالغ/طفل)

            $table->string('treatment_type');                          // نوع المعالجة (حشوة، سحب عصب، قلع...)
            $table->text('treatment_notes')->nullable();                // ملاحظات المعالجة
            $table->date('treatment_date');                            // تاريخ إجراء المعالجة

            $table->decimal('cost', 12, 2)->default(0);                // تكلفة المعالجة
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])
                  ->default('unpaid');                                 // حالة السداد لهذه المعالجة

            // ---------- حقول المزامنة الإجبارية ----------
            $table->uuid('local_id')->unique();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'tooth_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_treatments');
    }
};
