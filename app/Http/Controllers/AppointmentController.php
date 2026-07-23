<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * عرض مواعيد يوم معين (افتراضياً اليوم الحالي) مقسّمة حسب الفترتين.
     */
    public function index(Request $request)
    {
        $date = $request->date ?? today()->toDateString();

        $appointments = Appointment::with('patient')
            ->forDate($date)
            ->orderBy('appointment_time')
            ->get();

        $morningAppointments = $appointments->where('period', 'morning');
        $eveningAppointments = $appointments->where('period', 'evening');

        return view('appointments.index', compact('date', 'morningAppointments', 'eveningAppointments'));
    }

    public function create()
    {
        $patients = Patient::orderBy('name')->get();

        return view('appointments.form', ['appointment' => new Appointment(), 'patients' => $patients]);
    }

    public function store(StoreAppointmentRequest $request)
    {
        Appointment::create($request->validated());

        return redirect()->route('appointments.index', ['date' => $request->appointment_date])
            ->with('success', 'تم حجز الموعد بنجاح.');
    }

    public function edit(Appointment $appointment)
    {
        $patients = Patient::orderBy('name')->get();

        return view('appointments.form', compact('appointment', 'patients'));
    }

    public function update(StoreAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        return redirect()->route('appointments.index', ['date' => $request->appointment_date])
            ->with('success', 'تم تحديث الموعد بنجاح.');
    }

    /**
     * تحديث سريع لحالة الموعد (قيد الانتظار / دخل العيادة / تم الانتهاء / ملغي)
     * يُستخدم عادة عبر زر سريع في شاشة المواعيد دون فتح صفحة تعديل كاملة.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate(['status' => ['required', 'in:pending,arrived,completed,cancelled']]);

        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'تم تحديث حالة الموعد.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return back()->with('success', 'تم إلغاء/حذف الموعد.');
    }
}
