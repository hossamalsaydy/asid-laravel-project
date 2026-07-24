<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder
 * ===============
 * نظام اسيد مصمم لمستخدم واحد فقط (مدير النظام / طبيب الأسنان).
 * هذا الـ Seeder ينشئ هذا الحساب الوحيد عند إعداد النظام لأول مرة.
 *
 * للتشغيل: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@asid.local'],
            [
                'name'     => 'مدير العيادة',
                'password' => Hash::make('12345678'), // غيّر كلمة المرور فوراً بعد أول تسجيل دخول
            ]
        );
    }
}
