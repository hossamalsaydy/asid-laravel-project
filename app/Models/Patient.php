<?php

namespace App\Models;

use App\Traits\HasSyncFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory, HasSyncFields;

    protected $fillable = [
        'name', 'age', 'gender', 'phone', 'address',
        'has_diabetes', 'has_hypertension', 'has_allergy', 'allergy_details',
        'is_pregnant', 'medical_notes', 'is_active',
        'local_id', 'is_synced', 'synced_at',
    ];

    protected $casts = [
        'has_diabetes' => 'boolean',
        'has_hypertension' => 'boolean',
        'has_allergy' => 'boolean',
        'is_pregnant' => 'boolean',
        'is_active' => 'boolean',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    // ---------------------- العلاقات ----------------------

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dentalTreatments(): HasMany
    {
        return $this->hasMany(DentalTreatment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    // ---------------------- Accessors ----------------------

    /**
     * إجمالي المتبقي على المريض من كل فواتيره (غير مسدد).
     */
    public function getTotalDueAttribute(): float
    {
        return $this->invoices()->sum('final_amount') - $this->vouchers()
            ->where('voucher_type', 'receipt')->sum('amount');
    }
}
