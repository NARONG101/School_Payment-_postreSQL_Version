@extends('layouts.app')
@section('title', 'Enroll Student')
@section('page-title', 'Enroll New Student')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            onclick="history.length>1?history.back():window.location='{{ route('students.index') }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('styles')
<style>
.enroll-summary {
    background: var(--primary-50);
    border: 1px solid var(--primary-200);
    border-radius: var(--radius);
    padding: 18px 20px;
    margin-bottom: 20px;
}
.enroll-summary-title {
    font-size: 12px; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;
}
.enroll-fee-row {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.enroll-fee-breakdown {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.fee-chip {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 8px; padding: 6px 12px;
    font-size: 13px; font-weight: 600; color: var(--text-primary);
}
.fee-chip span { font-size: 11px; color: var(--text-muted); font-weight: 400; display: block; }
.fee-plus { color: var(--text-muted); font-size: 16px; font-weight: 700; }
.enroll-total-box {
    text-align: right;
}
.enroll-total-label { font-size: 11px; color: var(--text-muted); font-weight: 600; }
.enroll-total-val   { font-size: 32px; font-weight: 900; color: var(--primary); line-height: 1; }

.next-payment-info {
    margin-top: 10px; padding-top: 10px;
    border-top: 1px solid var(--primary-200);
    font-size: 13px; color: var(--text-secondary);
    display: flex; align-items: center; gap: 8px;
}
.next-payment-info strong { color: var(--text-primary); }
</style>
@endsection
@section('content')
<div style="max-width:680px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-user-plus" style="color:var(--primary)" aria-hidden="true"></i>
            Student Enrollment Form
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            {{-- Student ID + Enrollment Date --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id"
                           class="form-control @error('student_id') is-invalid @enderror"
                           value="{{ old('student_id', App\Models\Student::generateStudentId()) }}"
                           placeholder="Auto-generated" autocomplete="off">
                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="enrollmentDate">
                        Enrollment Date <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="date" id="enrollmentDate" name="enrollment_date"
                           class="form-control @error('enrollment_date') is-invalid @enderror"
                           value="{{ old('enrollment_date', date('Y-m-d')) }}" required>
                    @error('enrollment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Name --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="first_name" name="first_name"
                           class="form-control @error('first_name') is-invalid @enderror"
                           value="{{ old('first_name') }}" required autocomplete="given-name">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="last_name" name="last_name"
                           class="form-control @error('last_name') is-invalid @enderror"
                           value="{{ old('last_name') }}" required autocomplete="family-name">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Phone + Come From --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="{{ old('phone') }}" autocomplete="tel" inputmode="tel">
                </div>
                <div class="form-group">
                    <label class="form-label" for="come_from">Come From (Previous School)</label>
                    <input type="text" id="come_from" name="come_from" class="form-control"
                           value="{{ old('come_from') }}">
                </div>
            </div>

            {{-- Subject + Grade --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="subject">Subject <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="subject" name="subject"
                           class="form-control auto-field @error('subject') is-invalid @enderror"
                           value="{{ old('subject', 'គណិតវិទ្យា') }}" readonly required>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="year_level">Grade <span style="color:var(--danger)">*</span></label>
                    <select id="year_level" name="year_level"
                            class="form-control @error('year_level') is-invalid @enderror" required>
                        @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}" {{ old('year_level')==$i?'selected':'' }}>Grade {{ $i }}</option>
                        @endfor
                    </select>
                    @error('year_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Gender + Payment Day + Monthly Fee + Discount --}}
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label" for="gender">{{ __('app.gender') }}</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="male"   {{ old('gender')==='male'?'selected':'' }}>{{ __('app.male') }}</option>
                        <option value="female" {{ old('gender')==='female'?'selected':'' }}>{{ __('app.female') }}</option>
                        <option value="other"  {{ old('gender')==='other'?'selected':'' }}>{{ __('app.other') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="paymentDay">
                        {{ __('app.payment_day') }} <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="number" id="paymentDay" name="monthly_payment_day"
                           class="form-control @error('monthly_payment_day') is-invalid @enderror"
                           value="{{ old('monthly_payment_day', 1) }}" min="1" max="31" required>
                    @error('monthly_payment_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="monthly_fee">
                        {{ __('app.monthly_fee') }} <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="number" id="monthly_fee" name="monthly_fee" step="0.01"
                           class="form-control @error('monthly_fee') is-invalid @enderror"
                           value="{{ old('monthly_fee', 0) }}" min="0" required>
                    @error('monthly_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Discount --}}
            <div class="form-group">
                <label class="form-label" for="discount">
                    Discount (%) <span style="color:var(--text-muted)">(0-100)</span>
                </label>
                <input type="number" id="discount" name="discount" step="0.01"
                       class="form-control @error('discount') is-invalid @enderror"
                       value="{{ old('discount', 0) }}" min="0" max="100">
                @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Study Status --}}
            <div class="form-group">
                <label class="form-label" for="study_status">{{ __('app.status') }}</label>
                <select id="study_status" name="study_status" class="form-control">
                    <option value="studying" {{ old('study_status', 'studying')==='studying' ? 'selected' : '' }}>
                        🟢 {{ __('app.studying') }}
                    </option>
                    <option value="stopped" {{ old('study_status')==='stopped' ? 'selected' : '' }}>
                        🔴 {{ __('app.stopped') }}
                    </option>
                </select>
            </div>

            {{-- Time Type + Payment Method --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="time_type">Time Type <span style="color:var(--danger)">*</span></label>
                    <select id="time_type" name="time_type"
                            class="form-control @error('time_type') is-invalid @enderror" required>
                        <option value="">— Select Time Slot —</option>
                        @foreach(['mon-fri 7:00-9:00','mon-fri 9:00-11:00','mon-fri 1:00-3:00','mon-fri 3:00-5:00','mon-fri 5:30-7:30','sat-sun 7:00-11:00','sat-sun 1:00-5:00'] as $slot)
                        <option value="{{ $slot }}" {{ old('time_type')===$slot?'selected':'' }}>{{ $slot }}</option>
                        @endforeach
                    </select>
                    @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method <span style="color:var(--danger)">*</span></label>
                    <select id="payment_method" name="payment_method"
                            class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">— Select —</option>
                        <option value="cash"          {{ old('payment_method')==='cash'?'selected':'' }}>💵 Cash</option>
                        <option value="bank_transfer" {{ old('payment_method')==='bank_transfer'?'selected':'' }}>🏦 Bank Transfer</option>
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Payment Photo --}}
            <div class="form-group">
                <label class="form-label" for="payment_photo">Payment Photo</label>
                <input type="file" id="payment_photo" name="payment_photo"
                       class="form-control @error('payment_photo') is-invalid @enderror"
                       accept="image/jpeg,image/png,image/webp">
                @error('payment_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ── Enrollment Payment Summary ──────────────────── --}}
            <div class="enroll-summary">
                <div class="enroll-summary-title">
                    <i class="fas fa-receipt" aria-hidden="true"></i>
                    Enrollment Payment — Today
                </div>
                <div class="enroll-fee-row">
                    <div class="enroll-fee-breakdown">
                        <div class="fee-chip">
                            <span>Monthly Fee</span>
                            <span id="monthlyFeeDisplay">$0.00</span>
                        </div>
                        <span class="fee-plus">+</span>
                        <div class="fee-chip">
                            <span>Admin Fee (once)</span>
                            <span style="color:var(--warning)">$20.00</span>
                        </div>
                        <span class="fee-plus">-</span>
                        <div class="fee-chip">
                            <span>Discount</span>
                            <span style="color:var(--success)" id="discountAmountDisplay">$0.00</span>
                        </div>
                        <span class="fee-plus">=</span>
                    </div>
                    <div class="enroll-total-box">
                        <div class="enroll-total-label">Total to Pay Today</div>
                        <div class="enroll-total-val" id="enrollTotalDisplay">$20.00</div>
                    </div>
                </div>
                <div class="next-payment-info">
                    <i class="fas fa-calendar-check" style="color:var(--success)" aria-hidden="true"></i>
                    Next payment: <strong id="nextPaymentDisplay">—</strong>
                    &nbsp;·&nbsp; Amount: <strong id="nextAmountDisplay">$0.00</strong>
                    <span style="font-size:11px;color:var(--text-muted)">(monthly fee only, no admin fee)</span>
                </div>
            </div>

            <div style="display:flex;gap:10px;padding-top:4px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save" aria-hidden="true"></i> Enroll Student
                </button>
                <a href="{{ route('students.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ADMIN_FEE = 20; // $20 admin fee on enrollment only

    var enrollDate        = document.getElementById('enrollmentDate');
    var payDay            = document.getElementById('paymentDay');
    var feeInput          = document.getElementById('monthly_fee');
    var discountInput     = document.getElementById('discount');
    var feeDisplay        = document.getElementById('monthlyFeeDisplay');
    var discountAmtDisplay = document.getElementById('discountAmountDisplay');
    var totalDisplay      = document.getElementById('enrollTotalDisplay');
    var nextDisplay       = document.getElementById('nextPaymentDisplay');
    var nextAmount        = document.getElementById('nextAmountDisplay');

    function calcNextDate(enrollDateStr, paymentDay) {
        if (!enrollDateStr) return null;
        var day  = parseInt(paymentDay) || 1;
        var paid = new Date(enrollDateStr + 'T00:00:00');

        // Try same month first
        var lastDaySame   = new Date(paid.getFullYear(), paid.getMonth() + 1, 0).getDate();
        var daySame       = Math.min(day, lastDaySame);
        var candidateSame = new Date(paid.getFullYear(), paid.getMonth(), daySame);

        // Use same-month candidate ONLY if strictly AFTER the paid date
        if (candidateSame > paid) return candidateSame;

        // Next month — explicit year/month to avoid JS date overflow
        var ny = paid.getMonth() === 11 ? paid.getFullYear() + 1 : paid.getFullYear();
        var nm = paid.getMonth() === 11 ? 0 : paid.getMonth() + 1;
        var lastDayNext = new Date(ny, nm + 1, 0).getDate();
        var dayNext     = Math.min(day, lastDayNext);
        return new Date(ny, nm, dayNext);
    }

    function formatDate(d) {
        if (!d) return '—';
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function updateSummary() {
        var fee      = parseFloat(feeInput.value) || 0;
        var discount = parseFloat(discountInput.value) || 0;
        var day      = parseInt(payDay.value) || null;
        var next     = calcNextDate(enrollDate.value, day);

        // Update payment day from enrollment date automatically
        if (enrollDate.value) {
            var ed = new Date(enrollDate.value + 'T00:00:00');
            payDay.value = ed.getDate();
            day = ed.getDate();
            next = calcNextDate(enrollDate.value, day);
        }

        var subtotal = fee + ADMIN_FEE;
        var discountAmt = subtotal * (discount / 100);
        var total = subtotal - discountAmt;

        feeDisplay.textContent        = '$' + fee.toFixed(2);
        discountAmtDisplay.textContent = '$' + discountAmt.toFixed(2);
        totalDisplay.textContent      = '$' + total.toFixed(2);
        nextDisplay.textContent       = formatDate(next);
        nextAmount.textContent        = '$' + fee.toFixed(2);
    }

    enrollDate.addEventListener('change', updateSummary);
    feeInput.addEventListener('input', updateSummary);
    discountInput.addEventListener('input', updateSummary);
    payDay.addEventListener('input', updateSummary);

    // Run on load
    updateSummary();
});
</script>
@endsection
