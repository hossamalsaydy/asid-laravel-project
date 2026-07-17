@extends('layouts.admin')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

    {{-- ملخص سريع --}}
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ $stats['patients_count'] }}</div>
            <div class="stat-label">إجمالي المرضى</div>
        </div>
        <div class="stat-box accent">
            <div class="stat-value">{{ $stats['today_appointments'] }}</div>
            <div class="stat-label">مواعيد اليوم</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($stats['today_receipts'], 0) }}</div>
            <div class="stat-label">مقبوضات اليوم</div>
        </div>
        <div class="stat-box danger">
            <div class="stat-value">{{ number_format($stats['today_expenses'], 0) }}</div>
            <div class="stat-label">مصروفات اليوم</div>
        </div>
    </div>

    <div class="form-row" style="align-items:start;">

        {{-- مواعيد اليوم --}}
        <div class="card">
            <div class="card-header-row">
                <h3 class="card-title">🗓️ مواعيد اليوم</h3>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">عرض الكل</a>
            </div>

            @if($todayAppointments->isEmpty())
                <div class="empty-state">لا توجد مواعيد لهذا اليوم</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr><th>الوقت</th><th>المريض</th><th>الفترة</th><th>الحالة</th></tr>
                    </thead>
                    <tbody>
                        @foreach($todayAppointments as $appt)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($appt->appointment_time)->format('h:i A') }}</td>
                                <td class="sensitive-data">{{ $appt->patient->name }}</td>
                                <td>{{ $appt->period_label }}</td>
                                <td>
                                    <span class="badge {{ $appt->status === 'completed' ? 'badge-green' : ($appt->status === 'cancelled' ? 'badge-red' : 'badge-gold') }}">
                                        {{ $appt->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- تنبيهات المخزون --}}
        <div class="card">
            <div class="card-header-row">
                <h3 class="card-title">📦 تنبيهات نفاد المخزون</h3>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline btn-sm">عرض المخزن</a>
            </div>

            @if($lowStockItems->isEmpty())
                <div class="empty-state">لا توجد أصناف قاربت على النفاد 👍</div>
            @else
                <table class="data-table">
                    <thead><tr><th>الصنف</th><th>الكمية الحالية</th><th>الحد الأدنى</th></tr></thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td><span class="badge badge-red">{{ $item->current_quantity }} {{ $item->unit }}</span></td>
                                <td>{{ $item->minimum_quantity }} {{ $item->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- خطط تقسيط متأخرة --}}
    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title">⏰ خطط تقسيط بها دفعات متأخرة</h3>
            <a href="{{ route('installments.index') }}" class="btn btn-outline btn-sm">عرض الكل</a>
        </div>

        @if($overdueInstallments->isEmpty())
            <div class="empty-state">لا توجد دفعات متأخرة حالياً 👍</div>
        @else
            <table class="data-table">
                <thead><tr><th>المريض</th><th>المتبقي</th><th>إجراء</th></tr></thead>
                <tbody>
                    @foreach($overdueInstallments as $inst)
                        <tr>
                            <td class="sensitive-data">{{ $inst->patient->name }}</td>
                            <td>{{ number_format($inst->remaining_amount, 0) }}</td>
                            <td><a href="{{ route('installments.show', $inst) }}" class="btn btn-accent btn-sm">متابعة</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
