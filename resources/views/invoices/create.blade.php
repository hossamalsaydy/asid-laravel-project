@extends('layouts.admin')

@section('title', 'فاتورة جديدة')
@section('page-title', 'إصدار فاتورة جديدة')

@section('content')
    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">المريض</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">-- اختر المريض --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" @selected(($selectedPatient?->id ?? old('patient_id')) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
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
                    <label class="form-label">تاريخ الفاتورة</label>
                    <input type="date" name="invoice_date" value="{{ now()->toDateString() }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">الخصم</label>
                    <input type="number" step="0.01" name="discount" value="0" class="form-control" id="discountInput" oninput="recalcTotal()">
                </div>
            </div>

            {{-- بنود الفاتورة --}}
            <div class="form-group">
                <label class="form-label">بنود الفاتورة</label>
                <div id="itemsContainer"></div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addItemRow()">+ إضافة بند</button>
            </div>

            <div style="text-align:left; font-weight:800; margin:12px 0;">
                الإجمالي: <span id="totalDisplay">0</span> — بعد الخصم: <span id="finalDisplay" style="color:var(--primary-color);">0</span>
            </div>

            {{-- خيار التقسيط --}}
            <div class="card" style="background:#F9FAFB; box-shadow:none;">
                <label class="form-check">
                    <input type="checkbox" name="enable_installment" value="1" id="enableInstallment" onchange="document.getElementById('installmentFields').style.display = this.checked ? 'grid' : 'none'">
                    تفعيل نظام التقسيط لهذه الفاتورة
                </label>

                <div class="form-row" id="installmentFields" style="display:none; margin-top:12px;">
                    <div class="form-group">
                        <label class="form-label">الدفعة المقدمة</label>
                        <input type="number" step="0.01" name="down_payment" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">عدد الأقساط</label>
                        <input type="number" name="installments_count" class="form-control" value="1">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" rows="2" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 إصدار الفاتورة</button>
        </form>
    </div>
@endsection

@section('extra-scripts')
<script>
    let itemIndex = 0;

    function addItemRow(description = '', amount = '') {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('div');
        row.className = 'form-row';
        row.style.marginBottom = '8px';
        row.innerHTML = `
            <input type="text" name="items[${itemIndex}][description]" value="${description}" placeholder="وصف البند (مثال: حشوة سن 14)" class="form-control" required>
            <input type="number" step="0.01" name="items[${itemIndex}][amount]" value="${amount}" placeholder="المبلغ" class="form-control item-amount" oninput="recalcTotal()" required>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove(); recalcTotal();">حذف</button>
        `;
        container.appendChild(row);
        itemIndex++;
    }

    function recalcTotal() {
        const amounts = Array.from(document.querySelectorAll('.item-amount')).map(el => parseFloat(el.value) || 0);
        const total = amounts.reduce((a, b) => a + b, 0);
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        document.getElementById('totalDisplay').textContent = total.toLocaleString();
        document.getElementById('finalDisplay').textContent = Math.max(0, total - discount).toLocaleString();
    }

    // إضافة بند تلقائياً من معالجات المريض غير المفوترة (إن تم تمرير مريض محدد)
    @if($selectedPatient)
        @foreach($selectedPatient->dentalTreatments as $t)
            @if($t->payment_status !== 'paid')
                addItemRow("{{ $t->treatment_type }} - سن {{ $t->tooth_number }}", {{ $t->cost }});
            @endif
        @endforeach
    @else
        addItemRow();
    @endif

    recalcTotal();
</script>
@endsection
