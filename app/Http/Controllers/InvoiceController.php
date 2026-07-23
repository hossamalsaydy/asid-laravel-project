<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('patient')
            ->when($request->search, fn ($q) => $q->where('invoice_number', 'like', "%{$request->search}%"))
            ->latest('invoice_date')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();

        // في حال تم الدخول من صفحة مريض معين مع تحديد معالجات لفوترتها
        $selectedPatient = $request->patient_id ? Patient::with('dentalTreatments')->find($request->patient_id) : null;

        return view('invoices.create', compact('patients', 'selectedPatient'));
    }

    /**
     * إنشاء فاتورة جديدة مع بنودها، وربطها اختيارياً بخطة تقسيط.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'          => ['required', 'exists:patients,id'],
            'currency'            => ['required', 'in:YER,SAR,USD'],
            'invoice_date'        => ['required', 'date'],
            'discount'            => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.amount'      => ['required', 'numeric', 'min:0'],
            'items.*.dental_treatment_id' => ['nullable', 'exists:dental_treatments,id'],

            // بيانات التقسيط الاختيارية
            'enable_installment'    => ['nullable', 'boolean'],
            'down_payment'          => ['nullable', 'numeric', 'min:0'],
            'installments_count'    => ['nullable', 'integer', 'min:1'],
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])->sum('amount');
            $discount = $validated['discount'] ?? 0;
            $finalAmount = max(0, $totalAmount - $discount);

            $invoice = Invoice::create([
                'patient_id'    => $validated['patient_id'],
                'currency'      => $validated['currency'],
                'invoice_date'  => $validated['invoice_date'],
                'total_amount'  => $totalAmount,
                'discount'      => $discount,
                'final_amount'  => $finalAmount,
                'notes'         => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create($item);
            }

            // إنشاء خطة تقسيط تلقائياً إذا تم تفعيلها من الفورم
            if (!empty($validated['enable_installment'])) {
                $downPayment = $validated['down_payment'] ?? 0;

                $invoice->patient->installments()->create([
                    'invoice_id'          => $invoice->id,
                    'total_amount'        => $finalAmount,
                    'down_payment'        => $downPayment,
                    'remaining_amount'    => max(0, $finalAmount - $downPayment),
                    'installments_count'  => $validated['installments_count'] ?? 1,
                    'start_date'          => $validated['invoice_date'],
                    'status'              => 'active',
                ]);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم إصدار الفاتورة بنجاح.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['patient', 'items', 'installment.payments']);

        return view('invoices.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة.');
    }
}
