@extends('layouts.app')
@section('title', 'Enroll Student')
@section('page-title', 'Enroll New Student')
@section('content')
<div style="max-width:650px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-user-plus" style="color:var(--primary)"></i> Student Enrollment Form</div>
    </div>
    <div class="card-body">
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Student ID</label>
                    <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror"
                           value="{{ old('student_id', App\Models\Student::generateStudentId()) }}" placeholder="e.g. STU-2024-001">
                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Enrollment Date <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="enrollment_date" id="enrollmentDate" class="form-control @error('enrollment_date') is-invalid @enderror"
                           value="{{ old('enrollment_date', date('Y-m-d')) }}" required>
                    @error('enrollment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                           value="{{ old('first_name') }}" required>
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                           value="{{ old('last_name') }}" required>
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Come From (Previous School)</label>
                    <input type="text" name="come_from" class="form-control" value="{{ old('come_from') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Subject <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject') }}" required>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Grade <span style="color:var(--danger)">*</span></label>
                    <select name="year_level" class="form-control @error('year_level') is-invalid @enderror" required>
                        @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}" {{ old('year_level')==$i?'selected':'' }}>Grade {{ $i }}</option>
                        @endfor
                    </select>
                    @error('year_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
                        <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
                        <option value="other" {{ old('gender')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Payment Day <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="monthly_payment_day" id="paymentDay" class="form-control @error('monthly_payment_day') is-invalid @enderror"
                           value="{{ old('monthly_payment_day', 1) }}" min="1" max="31" required>
                    @error('monthly_payment_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Fee <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="monthly_fee" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                           value="{{ old('monthly_fee', 0) }}" required>
                    @error('monthly_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <label class="form-label">Payment Method <span style="color:var(--danger)">*</span></label>
                    <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="cash" {{ old('payment_method')=='cash'?'selected':'' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method')=='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Photo</label>
                <input type="file" name="payment_photo" class="form-control @error('payment_photo') is-invalid @enderror" accept="image/*">
                @error('payment_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">First Payment Date (Based on Enrollment)</label>
                <input type="date" id="firstPaymentDate" class="form-control" readonly style="background:var(--gray-50)">
            </div>
            <div style="background:var(--primary-50);border:1px solid var(--primary-200);border-radius:10px;padding:20px;margin-bottom:20px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <div style="font-size:13px;color:var(--gray-500);font-weight:600">Total to Pay</div>
                        <div style="display:flex;gap:8px;align-items:center;margin-top:4px">
                            <span style="font-size:12px;color:var(--gray-400)">Monthly Fee</span>
                            <span style="font-size:14px;font-weight:700;color:var(--gray-700)" id="monthlyFeeDisplay">$0.00</span>
                            <span style="font-size:12px;color:var(--gray-400)">+ Admin Fee</span>
                            <span style="font-size:14px;font-weight:700;color:var(--warning)">$20.00</span>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:32px;font-weight:900;color:var(--primary)" id="enrollTotalDisplay">$20.00</div>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:12px;padding-top:8px">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enroll Student
                </button>
                <a href="{{ route('students.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentDate = document.getElementById('enrollmentDate');
    const paymentDay = document.getElementById('paymentDay');
    const firstPaymentDate = document.getElementById('firstPaymentDate');
    const monthlyFeeInput = document.querySelector('input[name="monthly_fee"]');
    const monthlyFeeDisplay = document.getElementById('monthlyFeeDisplay');
    const enrollTotalDisplay = document.getElementById('enrollTotalDisplay');

    function updateFromEnrollmentDate() {
        if (enrollmentDate.value) {
            const date = new Date(enrollmentDate.value);
            const day = date.getDate();
            paymentDay.value = day;
            firstPaymentDate.value = enrollmentDate.value;
        }
    }

    function updateTotal() {
        const monthlyFee = parseFloat(monthlyFeeInput.value) || 0;
        const adminFee = 20;
        const total = monthlyFee + adminFee;
        monthlyFeeDisplay.textContent = '$' + monthlyFee.toFixed(2);
        enrollTotalDisplay.textContent = '$' + total.toFixed(2);
    }

    enrollmentDate.addEventListener('change', updateFromEnrollmentDate);
    monthlyFeeInput.addEventListener('input', updateTotal);
    updateFromEnrollmentDate();
    updateTotal();
});
</script>
@endsection
@endsection
