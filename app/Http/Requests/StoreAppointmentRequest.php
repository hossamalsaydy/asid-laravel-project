<?php

namespace App\Http\Requests;

use App\Models\Rules\NotDuringNapTime;
use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreAppointmentRequest
 * ========================
 * تُستخدم عند إنشاء أو تعديل موعد. تطبّق تلقائياً قاعدة منع الحجز
 * خلال فترة القيلولة اليمنية (1 ظهراً - 4 عصراً) عبر NotDuringNapTime.
 */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // مستخدم واحد فقط بصلاحية مطلقة، لا حاجة لأي فحص صلاحيات إضافي
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'        => ['required', 'exists:patients,id'],
            'appointment_date'  => ['required', 'date'],
            'appointment_time'  => ['required', 'date_format:H:i', new NotDuringNapTime()],
            'period'            => ['required', 'in:morning,evening'],
            'status'            => ['sometimes', 'in:pending,arrived,completed,cancelled'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required'       => 'يجب اختيار المريض.',
            'patient_id.exists'         => 'المريض المحدد غير موجود.',
            'appointment_date.required' => 'يجب تحديد تاريخ الموعد.',
            'appointment_time.required' => 'يجب تحديد وقت الموعد.',
            'appointment_time.date_format' => 'صيغة الوقت غير صحيحة.',
            'period.required'           => 'يجب تحديد الفترة (صباحي/مسائي).',
        ];
    }
}
