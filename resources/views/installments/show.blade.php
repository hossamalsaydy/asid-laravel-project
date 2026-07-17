@extends('layouts.admin')

@section('title', 'متابعة التقسيط')
@section('page-title', 'خطة تقسيط - ' . $installment->patient->name)

@section('content')

    <div class="card">
        <div class="stats-grid">
            <div class="stat-box"><div class="stat-value">{{ number_format($installment->total_amount, 0) }}</div><div class="stat-label">المبلغ الإجمالي</div></div>
            <div class="stat-box accent"><div class="stat-value">{{ number_format($installment->down_payment, 0) }}</div><div class="stat-label">الدفعة المقدمة</div></div>
            <div class="stat-box danger"><div class="stat-value">{{ number_format($installment->remaining_amount, 0) }}</div><div class="stat-label">المتبقي</div></div>
        </div>
    </div>

    {{-- جدول الزيارات والدفعات --}}
    <div class="card">
        <div class="card-header-row"><h3 class="card-title">📋 جدول الزيارات والدفعات</h3></div>

        @if($installment->payments->isEmpty())
            <div class="empty-state">لم تُضَف مواعيد دفعات بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>تاريخ الاستحقاق</th><th>المبلغ المستحق</th><th>المدفوع</th><th>الحالة</th><th>إجراء</th></tr></thead>
                <tbody>
                    @foreach($installment->payments as $payment)
                        <tr>
                            <td>{{ $payment->due_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($payment->amount, 0) }}</td>
                            <td>{{ number_format($payment->paid_amount, 0) }}</td>
                            <td>
                                <span class="badge {{ $payment->status === 'paid' ? 'badge-green' : ($payment->status === 'overdue' ? 'badge-red' : 'badge-gold') }}">
                                    {{ $payment->status_label }}
                                </span>
                            </td>
                            <td>
                                @if($payment->status !== 'paid')
                                    <form action="{{ route('installments.payments.pay', [$installment, $payment]) }}" method="POST" style="display:flex; gap:6px;">
                                        @csrf
                                        <input type="number" step="0.01" name="paid_amount" placeholder="المبلغ المستلم" class="form-control" style="width:130px;" required>
                                        <button class="btn btn-primary btn-sm">تسديد + سند قبض</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <form action="{{ route('installments.schedule.store', $installment) }}" method="POST" class="form-row" style="margin-top:16px;">
            @csrf
            <input type="date" name="due_date" class="form-control" required placeholder="تاريخ الاستحقاق">
            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="مبلغ القسط">
            <button class="btn btn-outline">+ إضافة موعد دفعة</button>
        </form>
    </div>

@endsection
