@extends('layouts.admin')

@section('title', $patient->exists ? 'تعديل مريض' : 'إضافة مريض')
@section('page-title', $patient->exists ? 'تعديل بيانات المريض' : 'إضافة مريض جديد')

@section('content')
    <div class="card" style="max-width:720px;">
        <form method="POST" action="{{ $patient->exists ? route('patients.update', $patient) : route('patients.store') }}">
            @csrf
            @if($patient->exists) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $patient->name) }}" class="form-control" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">العمر</label>
                    <input type="number" name="age" value="{{ old('age', $patient->age) }}" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">الجنس</label>
                    <select name="gender" class="form-control" required>
                        <option value="male" @selected(old('gender', $patient->gender) === 'male')>ذكر</option>
                        <option value="female" @selected(old('gender', $patient->gender) === 'female')>أنثى</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">السكن / العنوان</label>
                <input type="text" name="address" value="{{ old('address', $patient->address) }}" class="form-control">
            </div>

            <div class="card" style="background:#F9FAFB; box-shadow:none;">
                <label class="form-label">التاريخ الطبي العام</label>
                <div class="form-row">
                    <label class="form-check"><input type="checkbox" name="has_diabetes" value="1" @checked(old('has_diabetes', $patient->has_diabetes))> مصاب بالسكري</label>
                    <label class="form-check"><input type="checkbox" name="has_hypertension" value="1" @checked(old('has_hypertension', $patient->has_hypertension))> مصاب بضغط الدم</label>
                    <label class="form-check"><input type="checkbox" name="has_allergy" value="1" id="hasAllergyCheck" @checked(old('has_allergy', $patient->has_allergy))> لديه حساسية</label>
                    @if($patient->gender !== 'male')
                        <label class="form-check"><input type="checkbox" name="is_pregnant" value="1" @checked(old('is_pregnant', $patient->is_pregnant))> حامل</label>
                    @endif
                </div>

                <div class="form-group" style="margin-top:12px;">
                    <label class="form-label">تفاصيل الحساسية (إن وجدت)</label>
                    <input type="text" name="allergy_details" value="{{ old('allergy_details', $patient->allergy_details) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">ملاحظات طبية إضافية</label>
                    <textarea name="medical_notes" rows="3" class="form-control">{{ old('medical_notes', $patient->medical_notes) }}</textarea>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn-primary">💾 حفظ البيانات</button>
                <a href="{{ route('patients.index') }}" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
