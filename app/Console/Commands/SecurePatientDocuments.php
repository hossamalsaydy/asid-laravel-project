<?php

namespace App\Console\Commands;

use App\Models\PatientDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SecurePatientDocuments extends Command
{
    protected $signature = 'patients:documents-secure';

    protected $description = 'Move legacy patient documents from public storage to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');
        $migrated = 0;
        $issues = 0;

        foreach (PatientDocument::query()->orderBy('id')->cursor() as $document) {
            $path = str_replace('\\', '/', $document->file_path);
            $segments = explode('/', $path);

            if ($path === '' || str_starts_with($path, '/') || in_array('..', $segments, true)) {
                $this->error("Skipped unsafe document path for record {$document->id}.");
                $issues++;

                continue;
            }

            try {
                $sourceExists = $public->exists($path);
                $privateExists = $private->exists($path);

                if ($privateExists && ! $sourceExists) {
                    continue;
                }

                if (! $sourceExists) {
                    $this->error("Missing source file for document record {$document->id}.");
                    $issues++;

                    continue;
                }

                if (! $privateExists) {
                    $stream = $public->readStream($path);

                    if (! is_resource($stream)) {
                        $this->error("Could not read document record {$document->id}.");
                        $issues++;

                        continue;
                    }

                    try {
                        $written = $private->writeStream($path, $stream);
                    } finally {
                        fclose($stream);
                    }

                    if (! $written || ! $private->exists($path)) {
                        $private->delete($path);
                        $this->error("Could not securely copy document record {$document->id}.");
                        $issues++;

                        continue;
                    }
                }

                $sourceHash = hash_file('sha256', $public->path($path));
                $privateHash = hash_file('sha256', $private->path($path));

                if (! is_string($sourceHash) || ! is_string($privateHash) || ! hash_equals($sourceHash, $privateHash)) {
                    $this->error("Integrity check failed for document record {$document->id}; public copy was retained.");
                    $issues++;

                    continue;
                }

                if (! $public->delete($path)) {
                    $this->error("Private copy verified, but public copy could not be removed for record {$document->id}.");
                    $issues++;

                    continue;
                }

                $migrated++;
            } catch (Throwable $exception) {
                $this->error("Could not migrate document record {$document->id}: {$exception->getMessage()}");
                $issues++;
            }
        }

        $this->info("Secured {$migrated} patient document(s). Issues: {$issues}.");

        return $issues === 0 ? self::SUCCESS : self::FAILURE;
    }
}
