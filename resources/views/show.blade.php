@extends('layouts.admin')

@section('title', 'فاتورة #' . $invoice->invoice_number)
@section('page-title', 'تفاصيل الفاتورة')

@section('content')
    <div class="card" style="max-width:720px;">
        <div class="card-header-row">
            <h3 class="card-title">🧾 {{ $invoice->invoice_number }}</h3>
            @if($invoice->installment)
                <a href="{{ route('installments.show', $invoice->installment) }}" class="btn btn-accent btn-sm">متابعة خطة التقسيط</a>
            @endif
        </div>

        <div class="form-row" style="margin-bottom:16px;">
            <div class="sensitive-data"><strong>المريض:</strong> {{ $invoice->patient->name }}</div>
            <div><strong>التاريخ:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</div>
            <div><strong>العملة:</strong> {{ $invoice->currency }}</div>
        </div>

        <table class="data-table">
            <thead><tr><th>البند</th><th>المبلغ</th></tr></thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr><td>{{ $item->description }}</td><td>{{ number_format($item->amount, 0) }}</td></tr>
                @endforeach
            </tbody>
        </table>

        <div style="text-align:left; margin-top:14px; font-size:14.5px; line-height:2;">
            <div>الإجمالي: {{ number_format($invoice->total_amount, 0) }}</div>
            <div>الخصم: {{ number_format($invoice->discount, 0) }}</div>
            <div style="font-weight:800; color:var(--primary-color); font-size:17px;">
                المبلغ النهائي: {{ number_format($invoice->final_amount, 0) }} {{ $invoice->currency }}
            </div>
        </div>

        @if($invoice->notes)
            <div style="margin-top:12px; color:var(--text-muted); font-size:13.5px;">ملاحظات: {{ $invoice->notes }}</div>
        @endif
    </div>
@endsection
