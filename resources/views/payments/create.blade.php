@extends('layouts.app')
@section('title','New Payment')
@section('page-title','New Payment')
@section('content')
<div style="max-width:780px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-receipt" style="color:var(--primary)"></i> Record Payment</div>
    </div>
    <div class="card-body">
        <form action="{{ route('payments.store') }}" method="POST" id="paymentForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Student <span style="color:var(--danger)">*</span></label>
                <select name="student_id" id="studentSelect" class="form-control @error('student_id') is-invalid @enderror" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}"
                        data-fee="{{ $student->monthly_fee }}"
                        data-payment-day="{{ $student->monthly_payment_day }}"
                        data-time-type="{{ $student->time_type ?? '' }}"
                        data-enrollment-date="{{ $student->enrollment_date ? $student->enrollment_date->format('Y-m-d') : '' }}"
                        data-gender="{{ $student->gender }}"
                        {{ (old('student_id') ?? $selectedStudentId ?? null) == $student->id ? 'selected' : '' }}>
                        {{ $student->full_name }} (Grade {{ $student->year_level }}, {{ ucfirst($student->gender ?? 'n/a') }})
                    </option>
                    @endforeach
                </select>
                @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Monthly Fee <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="monthly_fee" id="feeInput" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                           value="{{ old('monthly_fee', 0) }}" readonly style="background:var(--gray-50);font-weight:700">
                    @error('monthly_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Paid Date <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="payment_date" id="paidDateInput" class="form-control @error('payment_date') is-invalid @enderror"
                           value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Time Type <span style="color:var(--danger)">*</span></label>
                    <select name="time_type" class="form-control @error('time_type') is-invalid @enderror" required>
                        <option value="">-- Select Time Slot --</option>
                        <option value="mon-fri 7:00-9:00" {{ old('time_type')=='mon-fri 7:00-9:00'?'selected':'' }}>Mon-Fri 7:00-9:00</option>
                        <option value="mon-fri 9:00-11:00" {{ old('time_type')=='mon-fri 9:00-11:00'?'selected':'' }}>Mon-Fri 9:00-11:00</option>
                        <option value="mon-fri 1:00-3:00" {{ old('time_type')=='mon-fri 1:00-3:00'?'selected':'' }}>Mon-Fri 1:00-3:00</option>
                        <option value="mon-fri 3:00-5:00" {{ old('time_type')=='mon-fri 3:00-5:00'?'selected':'' }}>Mon-Fri 3:00-5:00</option>
                        <option value="mon-fri 5:30-7:30" {{ old('time_type')=='mon-fri 5:30-7:30'?'selected':'' }}>Mon-Fri 5:30-7:30</option>
                        <option value="sat-sun 7:00-11:00" {{ old('time_type')=='sat-sun 7:00-11:00'?'selected':'' }}>Sat-Sun 7:00-11:00</option>
                        <option value="sat-sun 1:00-5:00" {{ old('time_type')=='sat-sun 1:00-5:00'?'selected':'' }}>Sat-Sun 1:00-5:00</option>
                    </select>
                    @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Next Payment Date (Auto)</label>
                    <input type="date" name="next_payment_date" id="nextPaymentInput" class="form-control" readonly style="background:var(--gray-50)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Payment Method <span style="color:var(--danger)">*</span></label>
                    <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="cash" {{ old('payment_method')=='cash'?'selected':'' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method')=='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Photo</label>
                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;gap:12px;padding-top:8px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Record Payment</button>
                <a href="{{ route('payments.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentSelect = document.getElementById('studentSelect');
    const feeInput = document.getElementById('feeInput');
    const paidDateInput = document.getElementById('paidDateInput');
    const nextPaymentInput = document.getElementById('nextPaymentInput');
    const timeTypeSelect = document.querySelector('select[name="time_type"]');

    function calculateTotalsAndNextPayment() {
        const selectedOption = studentSelect.options[studentSelect.selectedIndex];
        const fee = parseFloat(selectedOption.dataset.fee || 0);
        const paymentDay = selectedOption.dataset.paymentDay;
        const timeType = selectedOption.dataset.timeType || '';
        const enrollmentDate = selectedOption.dataset.enrollmentDate || '';
        const paidDate = paidDateInput.value;

        feeInput.value = fee.toFixed(2);

        if (timeType) {
            for (let i = 0; i < timeTypeSelect.options.length; i++) {
                if (timeTypeSelect.options[i].value === timeType) {
                    timeTypeSelect.selectedIndex = i;
                    break;
                }
            }
        }

        if (paymentDay && paidDate) {
            let date;
            if (enrollmentDate) {
                const enrollDateObj = new Date(enrollmentDate);
                const enrollDay = enrollDateObj.getDate();
                date = new Date(paidDate);
                date.setMonth(date.getMonth() + 1);
                const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                const day = Math.min(parseInt(paymentDay), lastDay);
                date.setDate(day);
            } else {
                date = new Date(paidDate);
                date.setMonth(date.getMonth() + 1);
                const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                const day = Math.min(parseInt(paymentDay), lastDay);
                date.setDate(day);
            }
            nextPaymentInput.value = date.toISOString().split('T')[0];
        }

        // Make fields readonly when student is selected
        if (studentSelect.value) {
            studentSelect.style.backgroundColor = 'var(--gray-50)';
            timeTypeSelect.style.backgroundColor = 'var(--gray-50)';
        } else {
            studentSelect.style.backgroundColor = 'white';
            timeTypeSelect.style.backgroundColor = 'white';
        }
    }

    studentSelect.addEventListener('change', calculateTotalsAndNextPayment);
    paidDateInput.addEventListener('change', calculateTotalsAndNextPayment);
    if (studentSelect.value) calculateTotalsAndNextPayment();
});
</script>
@endsection
@endsection