<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientDocumentPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_documents_require_authentication(): void
    {
        [$patient, $document] = $this->createPatientDocument();

        $this->get(route('patients.documents.show', [$patient, $document]))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_document_for_its_patient(): void
    {
        Storage::fake('private');
        [$patient, $document] = $this->createPatientDocument('patients/1/documents/xray.jpg');
        Storage::disk('private')->put($document->file_path, 'private-xray');

        $response = $this->actingAs(User::factory()->create())
            ->get(route('patients.documents.show', [$patient, $document]))
            ->assertOk();

        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_document_cannot_be_fetched_through_another_patient_route(): void
    {
        Storage::fake('private');
        [$patient, $document] = $this->createPatientDocument();
        $otherPatient = Patient::create(['name' => 'مريض آخر', 'gender' => 'male']);
        Storage::disk('private')->put($document->file_path, 'private-xray');

        $this->actingAs(User::factory()->create())
            ->get(route('patients.documents.show', [$otherPatient, $document]))
            ->assertNotFound();
    }

    public function test_uploads_are_saved_to_private_storage(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $patient = Patient::create(['name' => 'مريض تجريبي', 'gender' => 'female']);

        $this->actingAs(User::factory()->create())
            ->post(route('patients.documents.upload', $patient), [
                'file' => UploadedFile::fake()->create('xray.jpg', 10, 'image/jpeg'),
                'document_type' => 'xray',
            ])
            ->assertRedirect();

        $document = PatientDocument::query()->firstOrFail();
        Storage::disk('private')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
    }

    public function test_legacy_document_command_moves_files_and_removes_public_copies(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        [$patient, $document] = $this->createPatientDocument('patients/1/documents/legacy.jpg');
        Storage::disk('public')->put($document->file_path, 'legacy-xray');

        $this->assertSame(0, Artisan::call('patients:documents-secure'));
        Storage::disk('private')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
    }

    public function test_public_registration_and_destructive_seed_route_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Attacker',
            'email' => 'attacker@example.test',
            'password' => 'a-very-long-password',
            'password_confirmation' => 'a-very-long-password',
        ])->assertNotFound();
        $this->get('/run-seed')->assertNotFound();
    }

    /** @return array{Patient, PatientDocument} */
    private function createPatientDocument(string $path = 'patients/1/documents/xray.jpg'): array
    {
        $patient = Patient::create(['name' => 'مريض تجريبي', 'gender' => 'female']);
        $document = $patient->documents()->create([
            'file_path' => $path,
            'document_type' => 'xray',
        ]);

        return [$patient, $document];
    }
}
