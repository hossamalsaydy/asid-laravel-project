@extends('layouts.admin')

@section('title', $lab->exists ? 'تعديل إرسالية' : 'إرسالية جديدة')
@section('page-title', $lab->exists ? 'تعديل إرسالية المختبر' : 'إرسالية مختبر جديدة')

@section('content')
    <div class="card" style="max-width:680px;">
        <form method="POST" action="{{ $lab->exists ? route('labs.update', $lab) : route('labs.store') }}">
            @csrf
            @if($lab->exists) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">اسم المختبر</label>
                    <input type="text" name="lab_name" value="{{ old('lab_name', $lab->lab_name) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">المريض (اختياري)</label>
                    <select name="patient_id" class="form-control">
                        <option value="">-- بدون ربط بمريض --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" @selected(old('patient_id', $lab->patient_id) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">رقم السن</label>
                    <input type="text" name="tooth_number" value="{{ old('tooth_number', $lab->tooth_number) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">نوع التركيبة</label>
                    <input type="text" name="restoration_type" value="{{ old('restoration_type', $lab->restoration_type) }}" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">التكلفة المتفق عليها</label>
                    <input type="number" step="0.01" name="agreed_cost" value="{{ old('agreed_cost', $lab->agreed_cost) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-control" required>
                        <option value="sent" @selected(old('status', $lab->status) === 'sent')>تم الإرسال</option>
                        <option value="in_progress" @selected(old('status', $lab->status) === 'in_progress')>قيد التجهيز</option>
                        <option value="received" @selected(old('status', $lab->status) === 'received')>تم الاستلام</option>
                        <option value="needs_modification" @selected(old('status', $lab->status) === 'needs_modification')>يحتاج تعديل</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">تاريخ الإرسال</label>
                    <input type="date" name="sent_date" value="{{ old('sent_date', optional($lab->sent_date)->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ الاستلام المتوقع</label>
                    <input type="date" name="expected_receive_date" value="{{ old('expected_receive_date', optional($lab->expected_receive_date)->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ الاستلام الفعلي</label>
                    <input type="date" name="actual_receive_date" value="{{ old('actual_receive_date', optional($lab->actual_receive_date)->format('Y-m-d')) }}" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $lab->notes) }}</textarea>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 حفظ</button>
                <a href="{{ route('labs.index') }}" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
