<?php

namespace App\Http\Controllers;

use App\Models\Models\Patient;
use App\Models\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = Voucher::with('patient')
            ->when($request->type, fn ($q) => $q->where('voucher_type', $request->type))
            ->when($request->from, fn ($q) => $q->whereDate('voucher_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('voucher_date', '<=', $request->to))
            ->latest('voucher_date')
            ->paginate(20)
            ->withQueryString();

        return view('vouchers.index', compact('vouchers'));
    }

    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();
        $type = $request->type ?? 'receipt'; // receipt = قبض | payment = صرف

        return view('vouchers.form', ['voucher' => new Voucher(), 'patients' => $patients, 'type' => $type]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_type'     => ['required', 'in:receipt,payment'],
            'patient_id'       => ['nullable', 'required_if:voucher_type,receipt', 'exists:patients,id'],
            'expense_category' => ['nullable', 'required_if:voucher_type,payment', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'currency'         => ['required', 'in:YER,SAR,USD'],
            'voucher_date'     => ['required', 'date'],
            'paid_via'         => ['nullable', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
        ]);

        Voucher::create($validated);

        return redirect()->route('vouchers.index')->with('success', 'تم إصدار السند بنجاح.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return back()->with('success', 'تم حذف السند.');
    }
}
