@extends('layouts.admin')

@section('title', $item->exists ? 'تعديل صنف' : 'إضافة صنف')
@section('page-title', $item->exists ? 'تعديل صنف المخزن' : 'إضافة صنف جديد')

@section('content')
    <div class="card" style="max-width:600px;">
        <form method="POST" action="{{ $item->exists ? route('inventory.update', $item) : route('inventory.store') }}">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">اسم الصنف</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" class="form-control" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">وحدة القياس</label>
                    <input type="text" name="unit" value="{{ old('unit', $item->unit ?? 'قطعة') }}" class="form-control" required>
                </div>

                @if(!$item->exists)
                    <div class="form-group">
                        <label class="form-label">الكمية الافتتاحية</label>
                        <input type="number" step="0.01" name="current_quantity" value="{{ old('current_quantity', 0) }}" class="form-control" required>
                    </div>
                @endif
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الحد الأدنى للتنبيه</label>
                    <input type="number" step="0.01" name="minimum_quantity" value="{{ old('minimum_quantity', $item->minimum_quantity) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">تكلفة الوحدة (اختياري)</label>
                    <input type="number" step="0.01" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost) }}" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $item->notes) }}</textarea>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
