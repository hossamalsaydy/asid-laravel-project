@extends('layouts.admin')

@section('title', 'المرضى')
@section('page-title', 'إدارة المرضى')

@section('content')
    <div class="card">
        <div class="card-header-row">
            <h3 class="card-title">🧑‍⚕️ سجل المرضى</h3>
            <a href="{{ route('patients.create') }}" class="btn btn-primary">+ إضافة مريض جديد</a>
        </div>

        <form method="GET" style="margin-bottom:16px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو رقم الهاتف..." class="form-control" style="max-width:320px;">
        </form>

        @if($patients->isEmpty())
            <div class="empty-state">لا يوجد مرضى مسجّلون بعد</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الاسم</th><th>العمر</th><th>الجنس</th><th>الهاتف</th><th>ملاحظات طبية</th><th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patients as $patient)
                        <tr>
                            <td class="sensitive-data">{{ $patient->name }}</td>
                            <td>{{ $patient->age ?? '-' }}</td>
                            <td>{{ $patient->gender === 'male' ? 'ذكر' : 'أنثى' }}</td>
                            <td class="sensitive-data">{{ $patient->phone ?? '-' }}</td>
                            <td>
                                @if($patient->has_diabetes) <span class="badge badge-red">سكري</span> @endif
                                @if($patient->has_hypertension) <span class="badge badge-gold">ضغط</span> @endif
                                @if($patient->has_allergy) <span class="badge badge-blue">حساسية</span> @endif
                                @if($patient->is_pregnant) <span class="badge badge-gray">حامل</span> @endif
                            </td>
                            <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline btn-sm">عرض</a>
                                <a href="{{ route('dental.chart', $patient) }}" class="btn btn-accent btn-sm">🦷 المخطط</a>
                                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline btn-sm">تعديل</a>
                                <form action="{{ route('patients.destroy', $patient) }}" method="POST" data-no-loader>
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="AlertModal.confirm({
                                            title: 'تأكيد الحذف',
                                            message: 'هل أنت متأكد من حذف بيانات هذا المريض؟ لا يمكن التراجع عن هذا الإجراء.',
                                            type: 'danger',
                                            confirmText: 'حذف',
                                            onConfirm: () => this.closest('form').submit()
                                        })">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper">{{ $patients->links() }}</div>
        @endif
    </div>
@endsection
