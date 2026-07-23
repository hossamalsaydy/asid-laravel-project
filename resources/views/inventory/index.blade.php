@extends('layouts.admin')

@section('title', 'المخزن الطبي')
@section('page-title', 'المخزن الطبي المستهلك')

@section('content')

    @if($lowStockCount > 0)
        <div class="card" style="border-right:4px solid var(--danger-color); background:#FEF2F2;">
            ⚠️ يوجد <strong>{{ $lowStockCount }}</strong> صنف/أصناف وصلت إلى الحد الأدنى للمخزون ويجب إعادة تعبئتها قريباً.
        </div>
    @endif

    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title">📦 أصناف المخزن</h3>
            <a href="{{ route('inventory.create') }}" class="btn btn-primary">+ إضافة صنف جديد</a>
        </div>

        @if($items->isEmpty())
            <div class="empty-state">لا توجد أصناف مسجّلة بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>الصنف</th><th>الكمية الحالية</th><th>الحد الأدنى</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->current_quantity }} {{ $item->unit }}</td>
                            <td>{{ $item->minimum_quantity }} {{ $item->unit }}</td>
                            <td>
                                @if($item->is_low_stock)
                                    <span class="badge badge-red">قارب على النفاد</span>
                                @else
                                    <span class="badge badge-green">متوفر</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                <form action="{{ route('inventory.transactions.store', $item) }}" method="POST" style="display:flex; gap:4px;">
                                    @csrf
                                    <input type="number" step="0.01" name="quantity" placeholder="الكمية" class="form-control" style="width:90px;" required>
                                    <input type="hidden" name="transaction_date" value="{{ now()->toDateString() }}">
                                    <button type="submit" name="transaction_type" value="in" class="btn btn-primary btn-sm">توريد +</button>
                                    <button type="submit" name="transaction_type" value="out" class="btn btn-outline btn-sm">صرف -</button>
                                </form>
                                <a href="{{ route('inventory.edit', $item) }}" class="btn btn-outline btn-sm">تعديل</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-wrapper">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
