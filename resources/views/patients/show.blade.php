@extends('layouts.admin')

@section('title', 'ملف المريض')
@section('page-title', 'ملف المريض')

@section('content')

    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title sensitive-data">{{ $patient->name }}</h3>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('dental.chart', $patient) }}" class="btn btn-accent btn-sm">🦷 مخطط الأسنان</a>
                <a href="{{ route('invoices.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">🧾 فاتورة جديدة</a>
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline btn-sm">تعديل البيانات</a>
            </div>
        </div>

        <div class="form-row">
            <div><strong>العمر:</strong> {{ $patient->age ?? '-' }}</div>
            <div><strong>الجنس:</strong> {{ $patient->gender === 'male' ? 'ذكر' : 'أنثى' }}</div>
            <div class="sensitive-data"><strong>الهاتف:</strong> {{ $patient->phone ?? '-' }}</div>
            <div><strong>السكن:</strong> {{ $patient->address ?? '-' }}</div>
        </div>

        @if($patient->has_diabetes || $patient->has_hypertension || $patient->has_allergy || $patient->is_pregnant)
            <div style="margin-top:12px;">
                @if($patient->has_diabetes) <span class="badge badge-red">سكري</span> @endif
                @if($patient->has_hypertension) <span class="badge badge-gold">ضغط</span> @endif
                @if($patient->has_allergy) <span class="badge badge-blue">حساسية: {{ $patient->allergy_details }}</span> @endif
                @if($patient->is_pregnant) <span class="badge badge-gray">حامل</span> @endif
            </div>
        @endif
    </div>

    {{-- أرشيف المستندات --}}
    <div class="card">
        <div class="card-header-row"><h3 class="card-title">📁 أرشيف الأشعة والصور</h3></div>

        <form action="{{ route('patients.documents.upload', $patient) }}" method="POST" enctype="multipart/form-data" style="margin-bottom:16px;" class="form-row">
            @csrf
            <input type="file" name="file" class="form-control" required>
            <select name="document_type" class="form-control" required>
                <option value="xray">صورة أشعة</option>
                <option value="before">قبل المعالجة</option>
                <option value="after">بعد المعالجة</option>
                <option value="other">أخرى</option>
            </select>
            <button type="submit" class="btn btn-primary">رفع المستند</button>
        </form>

        <div style="display:flex; gap:14px; flex-wrap:wrap;">
            @forelse($patient->documents as $doc)
                <div class="sensitive-data" style="width:150px; text-align:center;">
                    <img src="{{ asset('storage/' . $doc->file_path) }}" style="width:150px; height:110px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">{{ $doc->description ?? $doc->document_type }}</div>
                </div>
            @empty
                <div class="empty-state">لا توجد مستندات مرفوعة بعد</div>
            @endforelse
        </div>
    </div>

    {{-- سجل المعالجات --}}
    <div class="card">
        <div class="card-header-row"><h3 class="card-title">🦷 سجل المعالجات</h3></div>
        @if($patient->dentalTreatments->isEmpty())
            <div class="empty-state">لا توجد معالجات مسجّلة بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>السن</th><th>نوع المعالجة</th><th>التاريخ</th><th>التكلفة</th><th>حالة السداد</th></tr></thead>
                <tbody>
                    @foreach($patient->dentalTreatments as $t)
                        <tr>
                            <td>{{ $t->tooth_number }}</td>
                            <td>{{ $t->treatment_type }}</td>
                            <td>{{ $t->treatment_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($t->cost, 0) }}</td>
                            <td>
                                <span class="badge {{ $t->payment_status === 'paid' ? 'badge-green' : ($t->payment_status === 'partial' ? 'badge-gold' : 'badge-red') }}">
                                    {{ $t->payment_status === 'paid' ? 'مسدد' : ($t->payment_status === 'partial' ? 'جزئي' : 'غير مسدد') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- سجل الفواتير --}}
    <div class="card">
        <div class="card-header-row"><h3 class="card-title">🧾 سجل الفواتير</h3></div>
        @if($patient->invoices->isEmpty())
            <div class="empty-state">لا توجد فواتير مسجّلة بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>المبلغ النهائي</th><th></th></tr></thead>
                <tbody>
                    @foreach($patient->invoices as $inv)
                        <tr>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($inv->final_amount, 0) }} {{ $inv->currency }}</td>
                            <td><a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline btn-sm">عرض</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
