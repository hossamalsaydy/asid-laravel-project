<?php

namespace App\Http\Controllers;

use App\Models\DentalTreatment;
use App\Models\Patient;
use Illuminate\Http\Request;

class DentalTreatmentController extends Controller
{
    /**
     * عرض مخطط الأسنان التفاعلي لمريض معين مع سجل معالجاته.
     */
    public function chart(Patient $patient)
    {
        $treatments = $patient->dentalTreatments()->latest('treatment_date')->get();

        return view('dental.chart', compact('patient', 'treatments'));
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'tooth_number'     => ['required', 'string', 'max:10'],
            'dentition_type'   => ['required', 'in:adult,child'],
            'treatment_type'   => ['required', 'string', 'max:255'],
            'treatment_notes'  => ['nullable', 'string', 'max:1000'],
            'treatment_date'   => ['required', 'date'],
            'cost'             => ['required', 'numeric', 'min:0'],
            'payment_status'   => ['required', 'in:paid,partial,unpaid'],
        ]);

        $patient->dentalTreatments()->create($validated);

        return back()->with('success', 'تم تسجيل المعالجة بنجاح.');
    }

    public function destroy(Patient $patient, DentalTreatment $treatment)
    {
        $treatment->delete();

        return back()->with('success', 'تم حذف المعالجة.');
    }
}
