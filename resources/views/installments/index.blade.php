@extends('layouts.admin')

@section('title', 'الأقساط')
@section('page-title', 'خطط الأقساط')

@section('content')
    <div class="card">
        <div class="card-header-row"><h3 class="card-title">💳 خطط التقسيط</h3></div>

        <form method="GET" style="margin-bottom:16px;">
            <select name="status" class="form-control" style="max-width:220px;" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status')==='active')>نشطة</option>
                <option value="completed" @selected(request('status')==='completed')>مكتملة</option>
                <option value="defaulted" @selected(request('status')==='defaulted')>متعثرة</option>
            </select>
        </form>

        @if($installments->isEmpty())
            <div class="empty-state">لا توجد خطط تقسيط بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>المريض</th><th>الإجمالي</th><th>المتبقي</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                    @foreach($installments as $inst)
                        <tr>
                            <td class="sensitive-data">{{ $inst->patient->name }}</td>
                            <td>{{ number_format($inst->total_amount, 0) }}</td>
                            <td>{{ number_format($inst->remaining_amount, 0) }}</td>
                            <td>
                                <span class="badge {{ $inst->status === 'completed' ? 'badge-green' : ($inst->status === 'defaulted' ? 'badge-red' : 'badge-gold') }}">
                                    {{ $inst->status === 'completed' ? 'مكتملة' : ($inst->status === 'defaulted' ? 'متعثرة' : 'نشطة') }}
                                </span>
                            </td>
                            <td><a href="{{ route('installments.show', $inst) }}" class="btn btn-outline btn-sm">متابعة</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrapper">{{ $installments->links() }}</div>
        @endif
    </div>
@endsection
