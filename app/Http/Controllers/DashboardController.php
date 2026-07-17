<?php

namespace App\Http\Controllers;

use App\Models\Models\Appointment;
use App\Models\Models\InventoryItem;
use App\Models\Models\Installment;
use App\Models\Models\Patient;
use App\Models\Models\Voucher;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // مواعيد اليوم الحالي مرتبة حسب الوقت
        $todayAppointments = Appointment::with('patient')
            ->forDate(today())
            ->orderBy('appointment_time')
            ->get();

        // أصناف المخزن التي وصلت للحد الأدنى
        $lowStockItems = InventoryItem::whereColumn('current_quantity', '<=', 'minimum_quantity')->get();

        // خطط تقسيط متأخرة السداد (دفعات متأخرة)
        $overdueInstallments = Installment::with('patient')
            ->where('status', 'active')
            ->whereHas('payments', fn ($q) => $q->where('status', 'overdue'))
            ->get();

        // ملخص مالي سريع لليوم
        $todayReceipts = Voucher::where('voucher_type', 'receipt')->whereDate('voucher_date', today())->sum('amount');
        $todayExpenses = Voucher::where('voucher_type', 'payment')->whereDate('voucher_date', today())->sum('amount');

        $stats = [
            'patients_count'     => Patient::count(),
            'today_appointments' => $todayAppointments->count(),
            'today_receipts'     => $todayReceipts,
            'today_expenses'     => $todayExpenses,
        ];

        return view('dashboard.index', compact(
            'todayAppointments', 'lowStockItems', 'overdueInstallments', 'stats'
        ));
    }
}
