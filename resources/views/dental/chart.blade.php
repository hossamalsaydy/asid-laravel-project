@extends('layouts.admin')

@section('title', 'مخطط الأسنان')
@section('page-title', 'مخطط الأسنان - ' . $patient->name)

@section('extra-styles')
    .dentition-tabs { display:flex; gap:8px; margin-bottom:16px; }
    .dentition-tab-btn { padding:8px 18px; border-radius:20px; border:1px solid var(--border-color); background:#fff; cursor:pointer; font-family:'Tajawal',sans-serif; font-weight:700; font-size:13.5px; }
    .dentition-tab-btn.active { background: var(--primary-color); color:#fff; border-color: var(--primary-color); }
    .arch-row { display:flex; justify-content:center; gap:6px; flex-wrap:wrap; margin:14px 0; }
    .tooth-btn {
        width:38px; height:38px; border-radius:8px; border:1.5px solid var(--border-color);
        background:#fff; cursor:pointer; font-size:12px; font-weight:700; color:var(--text-color);
        display:flex; align-items:center; justify-content:center; transition:all .15s ease;
    }
    .tooth-btn:hover { border-color: var(--accent-color); }
    .tooth-btn.selected { background: var(--accent-color); color:#fff; border-color:var(--accent-color); }
    .tooth-btn.has-treatment { background:#DCFCE7; border-color:#15803D; }
@endsection

@section('content')

    <div class="card">
        <div class="dentition-tabs">
            <button type="button" class="dentition-tab-btn active" onclick="switchDentition('adult')">🦷 بالغين (32 سناً)</button>
            <button type="button" class="dentition-tab-btn" onclick="switchDentition('child')">🍼 أطفال (20 سناً)</button>
        </div>

        {{-- ---------------- مخطط البالغين (32 سناً) ---------------- --}}
        <div id="adult-chart">
            <div class="arch-row" id="adult-upper"></div>
            <div style="text-align:center; color:var(--text-muted); font-size:12px;">— الفك العلوي / السفلي —</div>
            <div class="arch-row" id="adult-lower"></div>
        </div>

        {{-- ---------------- مخطط الأطفال (20 سناً) ---------------- --}}
        <div id="child-chart" style="display:none;">
            <div class="arch-row" id="child-upper"></div>
            <div style="text-align:center; color:var(--text-muted); font-size:12px;">— الفك العلوي / السفلي —</div>
            <div class="arch-row" id="child-lower"></div>
        </div>
    </div>

    {{-- نموذج تسجيل معالجة للسن المختار --}}
    <div class="card" id="treatment-form-card" style="display:none;">
        <h3 class="card-title" style="margin-bottom:14px;">تسجيل معالجة للسن رقم <span id="selectedToothLabel" style="color:var(--accent-color);"></span></h3>

        <form method="POST" action="{{ route('dental.treatments.store', $patient) }}">
            @csrf
            <input type="hidden" name="tooth_number" id="tooth_number_input">
            <input type="hidden" name="dentition_type" id="dentition_type_input" value="adult">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">نوع المعالجة</label>
                    <select name="treatment_type" class="form-control" required>
                        <option value="حشوة تجميلية">حشوة تجميلية</option>
                        <option value="سحب عصب">سحب عصب</option>
                        <option value="قلع">قلع</option>
                        <option value="تركيب جسر">تركيب جسر</option>
                        <option value="تبييض">تبييض</option>
                        <option value="أخرى">أخرى</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">تاريخ المعالجة</label>
                    <input type="date" name="treatment_date" value="{{ now()->toDateString() }}" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">التكلفة</label>
                    <input type="number" step="0.01" name="cost" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">حالة السداد</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="unpaid">غير مسدد</option>
                        <option value="partial">جزئي</option>
                        <option value="paid">مسدد</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ملاحظات المعالجة</label>
                <textarea name="treatment_notes" rows="2" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 حفظ المعالجة</button>
        </form>
    </div>

    {{-- سجل المعالجات السابقة --}}
    <div class="card">
        <h3 class="card-title" style="margin-bottom:14px;">سجل المعالجات</h3>
        @if($treatments->isEmpty())
            <div class="empty-state">لا توجد معالجات مسجّلة بعد</div>
        @else
            <table class="data-table">
                <thead><tr><th>السن</th><th>النوع</th><th>التاريخ</th><th>التكلفة</th><th>السداد</th><th></th></tr></thead>
                <tbody>
                    @foreach($treatments as $t)
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
                            <td>
                                <form action="{{ route('dental.treatments.destroy', [$patient, $t]) }}" method="POST" data-no-loader>
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="AlertModal.confirm({title:'حذف المعالجة', message:'هل تريد حذف سجل هذه المعالجة؟', type:'danger', onConfirm: () => this.closest('form').submit()})">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection

@section('extra-scripts')
<script>
    // قائمة الأسنان المعالجَة مسبقاً لتلوينها في المخطط
    const treatedTeeth = @json($treatments->pluck('tooth_number')->unique()->values());

    // ترقيم عالمي مبسّط: البالغين 1-16 علوي (من اليمين لليسار) و17-32 سفلي
    const adultUpper = Array.from({length:16}, (_, i) => i + 1);
    const adultLower = Array.from({length:16}, (_, i) => 32 - i);

    // الأطفال: نستخدم الحروف A-J علوي وK-T سفلي (ترقيم عالمي للأسنان اللبنية)
    const childUpper = ['A','B','C','D','E','F','G','H','I','J'];
    const childLower = ['T','S','R','Q','P','O','N','M','L','K'];

    function renderArch(containerId, teeth) {
        const container = document.getElementById(containerId);
        container.innerHTML = teeth.map(num => {
            const isTreated = treatedTeeth.includes(String(num));
            return `<button type="button" class="tooth-btn ${isTreated ? 'has-treatment' : ''}" data-tooth="${num}" onclick="selectTooth('${num}', this)">${num}</button>`;
        }).join('');
    }

    renderArch('adult-upper', adultUpper);
    renderArch('adult-lower', adultLower);
    renderArch('child-upper', childUpper);
    renderArch('child-lower', childLower);

    function switchDentition(type) {
        document.getElementById('adult-chart').style.display = type === 'adult' ? 'block' : 'none';
        document.getElementById('child-chart').style.display = type === 'child' ? 'block' : 'none';
        document.querySelectorAll('.dentition-tab-btn').forEach(b => b.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('dentition_type_input').value = type;
        document.getElementById('treatment-form-card').style.display = 'none';
    }

    function selectTooth(toothNumber, btn) {
        document.querySelectorAll('.tooth-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('tooth_number_input').value = toothNumber;
        document.getElementById('selectedToothLabel').textContent = toothNumber;
        document.getElementById('treatment-form-card').style.display = 'block';
    }
</script>
@endsection
