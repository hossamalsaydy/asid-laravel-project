<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
    public function index(Request $request)
    {
        $installments = Installment::with('patient')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('installments.index', compact('installments'));
    }

    /**
     * عرض جدول الزيارات والدفعات الخاص بخطة تقسيط معينة.
     */
    public function show(Installment $installment)
    {
        $installment->load('payments', 'patient', 'invoice');

        return view('installments.show', compact('installment'));
    }

    /**
     * إضافة موعد دفعة جديدة إلى جدول التقسيط.
     */
    public function storePaymentSchedule(Request $request, Installment $installment)
    {
        $validated = $request->validate([
            'due_date' => ['required', 'date'],
            'amount'   => ['required', 'numeric', 'min:0'],
        ]);

        $installment->payments()->create([
            'due_date' => $validated['due_date'],
            'amount'   => $validated['amount'],
            'status'   => 'pending',
        ]);

        return back()->with('success', 'تمت إضافة موعد دفعة جديد.');
    }

    /**
     * تسجيل سداد دفعة قسط، وينشئ تلقائياً سند قبض مرتبط بالمريض،
     * ثم يعيد حساب المبلغ المتبقي في خطة التقسيط.
     */
    public function recordPayment(Request $request, Installment $installment, \App\Models\InstallmentPayment $payment)
    {
        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
            'paid_via'    => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $installment, $payment) {
            $payment->update([
                'paid_amount' => $payment->paid_amount + $validated['paid_amount'],
                'paid_at'     => now(),
                'status'      => 'paid',
            ]);

            // إصدار سند قبض تلقائي لهذه الدفعة
            $installment->patient->vouchers()->create([
                'voucher_type' => 'receipt',
                'amount'       => $validated['paid_amount'],
                'currency'     => 'YER',
                'voucher_date' => now()->toDateString(),
                'paid_via'     => $validated['paid_via'] ?? null,
                'description'  => 'دفعة قسط رقم ' . $payment->id . ' - فاتورة #' . $installment->invoice_id,
            ]);

            $installment->recalculate();
        });

        return back()->with('success', 'تم تسجيل الدفعة وإصدار سند القبض بنجاح.');
    }
}
