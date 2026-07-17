<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rule: NotDuringNapTime
 * =======================
 * تمنع حجز أي موعد ضمن فترة القيلولة اليمنية المعتادة (من 1:00 ظهراً حتى 4:00 عصراً).
 * تُستخدم على حقل الوقت (appointment_time) بصيغة H:i مثل "13:30".
 */
class NotDuringNapTime implements ValidationRule
{
    /**
     * بداية فترة القيلولة (بالساعة، نظام 24 ساعة).
     */
    private int $napStartHour = 13; // الساعة 1:00 ظهراً

    /**
     * نهاية فترة القيلولة (بالساعة، نظام 24 ساعة).
     */
    private int $napEndHour = 16; // الساعة 4:00 عصراً

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // استخراج الساعة من قيمة الوقت المرسلة (مثال: "13:45" => 13)
        $hour = (int) date('H', strtotime($value));

        if ($hour >= $this->napStartHour && $hour < $this->napEndHour) {
            $fail('لا يمكن حجز موعد خلال فترة القيلولة (من الساعة 1:00 ظهراً وحتى 4:00 عصراً).');
        }
    }
}
