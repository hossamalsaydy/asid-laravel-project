@extends('layouts.admin')

@section('title', 'سند جديد')
@section('page-title', $type === 'receipt' ? 'إصدار سند قبض' : 'إصدار سند صرف')

@section('content')
    <div class="card" style="max-width:600px;">
        <form method="POST" action="{{ route('vouchers.store') }}">
            @csrf
            <input type="hidden" name="voucher_type" id="voucherTypeInput" value="{{ $type }}">

            <div class="form-group">
                <label class="form-label">نوع السند</label>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn {{ $type === 'receipt' ? 'btn-primary' : 'btn-outline' }}" onclick="setType('receipt')" id="btnReceipt">سند قبض (من مريض)</button>
                    <button type="button" class="btn {{ $type === 'payment' ? 'btn-danger' : 'btn-outline' }}" onclick="setType('payment')" id="btnPayment">سند صرف (مصروف عيادة)</button>
                </div>
            </div>

            <div class="form-group" id="patientField" style="{{ $type === 'payment' ? 'display:none' : '' }}">
                <label class="form-label">المريض</label>
                <select name="patient_id" class="form-control">
                    <option value="">-- اختر المريض --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="categoryField" style="{{ $type === 'receipt' ? 'display:none' : '' }}">
                <label class="form-label">تصنيف المصروف</label>
                <select name="expense_category" class="form-control">
                    <option value="إيجار">إيجار</option>
                    <option value="كهرباء">كهرباء</option>
                    <option value="ديزل المولد">ديزل المولد</option>
                    <option value="رواتب">رواتب</option>
                    <option value="مستلزمات">مستلزمات طبية</option>
                    <option value="أخرى">أخرى</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">المبلغ</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">العملة</label>
                    <select name="currency" class="form-control" required>
                        <option value="YER">ريال يمني (YER)</option>
                        <option value="SAR">ريال سعودي (SAR)</option>
                        <option value="USD">دولار أمريكي (USD)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">تاريخ السند</label>
                    <input type="date" name="voucher_date" value="{{ now()->toDateString() }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">طريقة الدفع</label>
                    <input type="text" name="paid_via" placeholder="نقدي، تحويل..." class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">البيان</label>
                <textarea name="description" rows="2" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 حفظ السند</button>
        </form>
    </div>
@endsection

@section('extra-scripts')
<script>
    function setType(type) {
        document.getElementById('voucherTypeInput').value = type;
        document.getElementById('patientField').style.display = type === 'receipt' ? 'block' : 'none';
        document.getElementById('categoryField').style.display = type === 'payment' ? 'block' : 'none';

        document.getElementById('btnReceipt').className = 'btn ' + (type === 'receipt' ? 'btn-primary' : 'btn-outline');
        document.getElementById('btnPayment').className = 'btn ' + (type === 'payment' ? 'btn-danger' : 'btn-outline');
    }
</script>
@endsection
