<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_refuses_to_create_admin_without_a_strong_password(): void
    {
        config([
            'clinic.admin_email' => 'admin@asid.local',
            'clinic.admin_password' => null,
        ]);

        try {
            app(DatabaseSeeder::class)->run();
            $this->fail('The seeder should require a strong administrator password.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('ADMIN_PASSWORD', $exception->getMessage());
            $this->assertDatabaseCount('users', 0);
        }
    }

    public function test_seeder_hashes_the_configured_password_and_does_not_reset_existing_admin(): void
    {
        $originalPassword = 'unique-test-only-secret-1';
        config([
            'clinic.admin_email' => 'admin@asid.local',
            'clinic.admin_password' => $originalPassword,
        ]);

        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@asid.local')->firstOrFail();

        $this->assertTrue(Hash::check($originalPassword, $admin->password));

        config(['clinic.admin_password' => 'a-different-test-only-secret']);
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Hash::check($originalPassword, $admin->fresh()->password));
        $this->assertSame(1, User::where('email', 'admin@asid.local')->count());
    }
}
