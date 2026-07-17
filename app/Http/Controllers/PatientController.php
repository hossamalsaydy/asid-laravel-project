<?php

namespace App\Http\Controllers;

use App\Models\Http\Requests\StorePatientRequest;
use App\Models\Models\Patient;
use App\Models\Models\PatientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.form', ['patient' => new Patient()]);
    }

    public function store(StorePatientRequest $request)
    {
        Patient::create($request->validated());

        return redirect()->route('patients.index')->with('success', 'تم إضافة المريض بنجاح.');
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'appointments' => fn ($q) => $q->latest('appointment_date'),
            'dentalTreatments' => fn ($q) => $q->latest('treatment_date'),
            'invoices' => fn ($q) => $q->latest('invoice_date'),
            'documents',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.form', compact('patient'));
    }

    public function update(StorePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return redirect()->route('patients.index')->with('success', 'تم تحديث بيانات المريض بنجاح.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'تم حذف المريض بنجاح.');
    }

    /**
     * رفع مستند/صورة أشعة للمريض (يُحفظ ضمن أرشيف المريض، ويخضع لوضع الستر في الواجهة).
     */
    public function uploadDocument(Request $request, Patient $patient)
    {
        $request->validate([
            'file'          => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'document_type' => ['required', 'in:xray,before,after,other'],
            'description'   => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('file')->store('patients/' . $patient->id . '/documents', 'public');

        PatientDocument::create([
            'patient_id'    => $patient->id,
            'file_path'     => $path,
            'document_type' => $request->document_type,
            'description'   => $request->description,
        ]);

        return back()->with('success', 'تم رفع المستند بنجاح.');
    }
}
