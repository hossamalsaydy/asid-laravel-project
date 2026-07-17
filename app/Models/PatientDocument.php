<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDocument extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'patient_id', 'file_path', 'document_type', 'description',
        'is_sensitive', 'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
