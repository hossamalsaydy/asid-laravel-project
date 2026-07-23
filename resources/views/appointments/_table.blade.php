@if($appointments->isEmpty())
    <div class="empty-state">لا توجد مواعيد في هذه الفترة</div>
@else
    <table class="data-table">
        <thead><tr><th>الوقت</th><th>المريض</th><th>الحالة</th><th>إجراءات</th></tr></thead>
        <tbody>
            @foreach($appointments as $appt)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($appt->appointment_time)->format('h:i A') }}</td>
                    <td class="sensitive-data">{{ $appt->patient->name }}</td>
                    <td>
                        <span class="badge {{ $appt->status === 'completed' ? 'badge-green' : ($appt->status === 'cancelled' ? 'badge-red' : ($appt->status === 'arrived' ? 'badge-blue' : 'badge-gold')) }}">
                            {{ $appt->status_label }}
                        </span>
                    </td>
                    <td style="display:flex; gap:5px; flex-wrap:wrap;">
                        @if($appt->status === 'pending')
                            <form action="{{ route('appointments.updateStatus', $appt) }}" method="POST" data-no-loader>
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="arrived">
                                <button class="btn btn-accent btn-sm">دخل العيادة</button>
                            </form>
                        @elseif($appt->status === 'arrived')
                            <form action="{{ route('appointments.updateStatus', $appt) }}" method="POST" data-no-loader>
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button class="btn btn-primary btn-sm">إنهاء</button>
                            </form>
                        @endif
                        <a href="{{ route('appointments.edit', $appt) }}" class="btn btn-outline btn-sm">تعديل</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
