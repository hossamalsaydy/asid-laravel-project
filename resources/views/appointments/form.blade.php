@extends('layouts.admin')

@section('title', $appointment->exists ? 'تعديل موعد' : 'حجز موعد')
@section('page-title', $appointment->exists ? 'تعديل الموعد' : 'حجز موعد جديد')

@section('content')
    <div class="card" style="max-width:600px;">
        <form method="POST" action="{{ $appointment->exists ? route('appointments.update', $appointment) : route('appointments.store') }}">
            @csrf
            @if($appointment->exists) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">المريض</label>
                <select name="patient_id" class="form-control" required>
                    <option value="">-- اختر المريض --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" @selected(old('patient_id', $appointment->patient_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('patient_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">تاريخ الموعد</label>
                    <input type="date" name="appointment_date" value="{{ old('appointment_date', optional($appointment->appointment_date)->format('Y-m-d')) }}" class="form-control" required>
                    @error('appointment_date') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">وقت الموعد</label>
                    <input type="time" name="appointment_time" value="{{ old('appointment_time', $appointment->appointment_time) }}" class="form-control" required>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">🕐 ممنوع الحجز بين 1:00 ظهراً و4:00 عصراً</div>
                    @error('appointment_time') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الفترة</label>
                    <select name="period" class="form-control" required>
                        <option value="morning" @selected(old('period', $appointment->period) === 'morning')>صباحي</option>
                        <option value="evening" @selected(old('period', $appointment->period) === 'evening')>مسائي</option>
                    </select>
                </div>

                @if($appointment->exists)
                    <div class="form-group">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-control">
                            <option value="pending" @selected($appointment->status === 'pending')>قيد الانتظار</option>
                            <option value="arrived" @selected($appointment->status === 'arrived')>دخل العيادة</option>
                            <option value="completed" @selected($appointment->status === 'completed')>تم الانتهاء</option>
                            <option value="cancelled" @selected($appointment->status === 'cancelled')>ملغي</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 حفظ الموعد</button>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
