@extends('layouts.admin')

@section('title', 'المواعيد')
@section('page-title', 'جدولة المواعيد')

@section('content')

    <div class="card">
        <div class="card-header-row">
            <form method="GET" style="display:flex; gap:10px; align-items:center;">
                <label class="form-label" style="margin:0;">التاريخ:</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()">
            </form>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary">+ حجز موعد جديد</a>
        </div>
        <p style="font-size:13px; color:var(--text-muted); margin:0;">
            🕐 تنبيه: لا يمكن حجز مواعيد بين الساعة 1:00 ظهراً و4:00 عصراً (وقت القيلولة).
        </p>
    </div>

    <div class="form-row" style="align-items:start;">
        {{-- الفترة الصباحية --}}
        <div class="card">
            <h3 class="card-title" style="margin-bottom:14px;">☀️ الفترة الصباحية</h3>
            @include('appointments._table', ['appointments' => $morningAppointments])
        </div>

        {{-- الفترة المسائية --}}
        <div class="card">
            <h3 class="card-title" style="margin-bottom:14px;">🌙 الفترة المسائية</h3>
            @include('appointments._table', ['appointments' => $eveningAppointments])
        </div>
    </div>

@endsection
