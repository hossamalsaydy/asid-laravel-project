<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('clinic.admin_email', 'admin@asid.local');

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Clinic administrator {$email} already exists; no password was changed.");

            return;
        }

        $password = config('clinic.admin_password');

        if (! is_string($password) || strlen($password) < 16) {
            throw new RuntimeException(
                'Set a unique ADMIN_PASSWORD of at least 16 characters in .env before creating the clinic administrator.'
            );
        }

        User::create([
            'name' => 'مدير العيادة',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->command?->info("Clinic administrator {$email} was created.");
    }
}
