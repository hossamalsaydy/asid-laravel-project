@extends('layouts.admin')

@section('title', 'السندات')
@section('page-title', 'السندات المالية')

@section('content')
    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title">💵 سجل السندات</h3>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('vouchers.create', ['type' => 'receipt']) }}" class="btn btn-primary">+ سند قبض</a>
                <a href="{{ route('vouchers.create', ['type' => 'payment']) }}" class="btn btn-danger">+ سند صرف</a>
            </div>
        </div>

        <form method="GET" class="form-row" style="margin-bottom:16px;">
            <select name="type" class="form-control" onchange="this.form.submit()">
                <option value="">كل الأنواع</option>
                <option value="receipt" @selected(request('type')==='receipt')>سندات قبض</option>
                <option value="payment" @selected(request('type')==='payment')>سندات صرف</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" onchange="this.form.submit()">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" onchange="this.form.submit()">
        </form>

        @if($vouchers->isEmpty())
            <div class="empty-state">لا توجد سندات مسجّلة بعد</div>
        @else
            <table class="data-table">
                <thead>
                    <tr><th>رقم السند</th><th>النوع</th><th>البيان</th><th>المريض / التصنيف</th><th>المبلغ</th><th>التاريخ</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($vouchers as $v)
                        <tr>
                            <td>{{ $v->voucher_number }}</td>
                            <td>
                                <span class="badge {{ $v->voucher_type === 'receipt' ? 'badge-green' : 'badge-red' }}">
                                    {{ $v->type_label }}
                                </span>
                            </td>
                            <td>{{ $v->description ?? '-' }}</td>
                            <td class="sensitive-data">{{ $v->patient->name ?? $v->expense_category ?? '-' }}</td>
                            <td>{{ number_format($v->amount, 0) }} {{ $v->currency }}</td>
                            <td>{{ $v->voucher_date->format('Y-m-d') }}</td>
                            <td>
                                <form action="{{ route('vouchers.destroy', $v) }}" method="POST" data-no-loader>
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="AlertModal.confirm({title:'حذف السند', message:'هل تريد حذف هذا السند؟', type:'danger', onConfirm: () => this.closest('form').submit()})">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrapper">{{ $vouchers->links() }}</div>
        @endif
    </div>
@endsection
