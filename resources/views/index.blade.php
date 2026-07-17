@extends('layouts.admin')

@section('title', 'الفواتير')
@section('page-title', 'الفواتير')

@section('content')
    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title">🧾 سجل الفواتير</h3>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">+ فاتورة جديدة</a>
        </div>

        <form method="GET" style="margin-bottom:16px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث برقم الفاتورة..." class="form-control" style="max-width:280px;">
        </form>

        @if($invoices->isEmpty())
            <div class="empty-state">لا توجد فواتير بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>رقم الفاتورة</th><th>المريض</th><th>التاريخ</th><th>المبلغ النهائي</th><th></th></tr></thead>
                <tbody>
                    @foreach($invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td class="sensitive-data">{{ $inv->patient->name }}</td>
                            <td>{{ $inv->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($inv->final_amount, 0) }} {{ $inv->currency }}</td>
                            <td><a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline btn-sm">عرض</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrapper">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection
