@extends('layouts.admin')

@section('title', 'المعامل الخارجية')
@section('page-title', 'إدارة المعامل الخارجية')

@section('content')
    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title">🏭 إرساليات المعامل الخارجية</h3>
            <a href="{{ route('labs.create') }}" class="btn btn-primary">+ إرسالية جديدة</a>
        </div>

        <form method="GET" style="margin-bottom:16px;">
            <select name="status" class="form-control" style="max-width:220px;" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="sent" @selected(request('status')==='sent')>تم الإرسال</option>
                <option value="in_progress" @selected(request('status')==='in_progress')>قيد التجهيز</option>
                <option value="received" @selected(request('status')==='received')>تم الاستلام</option>
                <option value="needs_modification" @selected(request('status')==='needs_modification')>يحتاج تعديل</option>
            </select>
        </form>

        @if($labs->isEmpty())
            <div class="empty-state">لا توجد إرساليات مسجّلة بعد</div>
        @else
            <table class="data-table">
                <thead>
                    <tr><th>المختبر</th><th>المريض</th><th>التركيبة</th><th>التكلفة</th><th>تاريخ الإرسال</th><th>الاستلام المتوقع</th><th>الحالة</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($labs as $lab)
                        <tr>
                            <td>{{ $lab->lab_name }}</td>
                            <td class="sensitive-data">{{ $lab->patient->name ?? $lab->patient_name_snapshot ?? '-' }}</td>
                            <td>{{ $lab->restoration_type }} @if($lab->tooth_number) (سن {{ $lab->tooth_number }}) @endif</td>
                            <td>{{ number_format($lab->agreed_cost, 0) }}</td>
                            <td>{{ $lab->sent_date->format('Y-m-d') }}</td>
                            <td>{{ optional($lab->expected_receive_date)->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $lab->status === 'received' ? 'badge-green' : ($lab->status === 'needs_modification' ? 'badge-red' : 'badge-gold') }}">
                                    {{ $lab->status_label }}
                                </span>
                            </td>
                            <td><a href="{{ route('labs.edit', $lab) }}" class="btn btn-outline btn-sm">تعديل</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrapper">{{ $labs->links() }}</div>
        @endif
    </div>
@endsection
