<?php

namespace App\Http\Controllers;

use App\Models\Models\DentalLab;
use App\Models\Models\Patient;
use Illuminate\Http\Request;

class DentalLabController extends Controller
{
    public function index(Request $request)
    {
        $labs = DentalLab::with('patient')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('sent_date')
            ->paginate(15)
            ->withQueryString();

        return view('labs.index', compact('labs'));
    }

    public function create()
    {
        $patients = Patient::orderBy('name')->get();

        return view('labs.form', ['lab' => new DentalLab(), 'patients' => $patients]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        if (!empty($validated['patient_id'])) {
            $validated['patient_name_snapshot'] = Patient::find($validated['patient_id'])->name;
        }

        DentalLab::create($validated);

        return redirect()->route('labs.index')->with('success', 'تم تسجيل إرسالية المختبر بنجاح.');
    }

    public function edit(DentalLab $lab)
    {
        $patients = Patient::orderBy('name')->get();

        return view('labs.form', compact('lab', 'patients'));
    }

    public function update(Request $request, DentalLab $lab)
    {
        $lab->update($this->validateRequest($request));

        return redirect()->route('labs.index')->with('success', 'تم تحديث بيانات المختبر بنجاح.');
    }

    public function destroy(DentalLab $lab)
    {
        $lab->delete();

        return back()->with('success', 'تم حذف السجل.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'patient_id'             => ['nullable', 'exists:patients,id'],
            'lab_name'               => ['required', 'string', 'max:255'],
            'tooth_number'           => ['nullable', 'string', 'max:10'],
            'restoration_type'       => ['required', 'string', 'max:255'],
            'agreed_cost'            => ['required', 'numeric', 'min:0'],
            'sent_date'              => ['required', 'date'],
            'expected_receive_date'  => ['nullable', 'date'],
            'actual_receive_date'    => ['nullable', 'date'],
            'status'                 => ['required', 'in:sent,in_progress,received,needs_modification'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
