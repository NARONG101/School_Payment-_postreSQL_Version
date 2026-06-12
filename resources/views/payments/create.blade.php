@extends('layouts.app')
@section('title', 'New Payment')
@section('page-title', 'New Payment')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            id="backBtn"
            data-back-url="{{ route('payments.index') }}"
            onclick="goBack()">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection

@section('styles')
<style>
.auto-field {
    background: var(--bg-muted)!important;
    color: var(--text-secondary)!important;
    cursor: default;
    font-weight: 600;
}
.auto-field:focus {
    box-shadow: none!important;
    border-color: var(--border-input)!important;
}

#noStudentMsg {
    text-align: center;
    padding: 40px 24px;
    color: var(--text-muted);
    font-size: 14px;
    display: none;
}
#noStudentMsg i {
    font-size: 40px;
    display: block;
    margin-bottom: 14px;
}
#autoFields {
    display: block;
}
#autoFields.show {
    display: block;
    animation: fadeIn .3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.months-panel {
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 18px;
    border: 1px solid var(--border);
}
.months-panel-title {
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.month-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.month-chip {
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid var(--border);
    transition: all 0.15s;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    background: var(--bg-card);
    color: var(--text-primary);
}
.month-chip:hover {
    border-color: var(--primary);
}
.month-chip.selected {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.sel-month-box {
    background: var(--bg-muted);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 13px;
    color: var(--text-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title {
    font-size: 12px;
    font-weight: 800;
    color: var(--text-heading);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 22px 0 14px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-row {
    gap: 14px;
}

.payment-summary {
    background: var(--primary-50);
    border: 1px solid var(--primary-200);
    border-radius: var(--radius);
    padding: 18px 20px;
    margin-bottom: 20px;
}
.payment-summary-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}
.payment-fee-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.payment-fee-breakdown {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.fee-chip {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
.fee-chip span {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 400;
    display: block;
}
.fee-plus {
    color: var(--text-muted);
    font-size: 16px;
    font-weight: 700;
}
.payment-total-box {
    text-align: right;
}
.payment-total-label { font-size: 11px; color: var(--text-muted); font-weight: 600; }
.payment-total-val   { font-size: 32px; font-weight: 900; color: var(--primary); line-height: 1; }

/* Custom Multi-Select Dropdown */
.multi-select {
    position: relative;
    width: 100%;
}
.multi-select-trigger {
    width: 100%;
    padding: 10px 34px 10px 13px;
    border: 1.5px solid var(--border-input);
    border-radius: var(--radius-sm);
    font-size: 14px;
    color: var(--text-primary);
    background: var(--bg-input);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: border 0.15s, box-shadow 0.15s;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%2394a3b8' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
}
.multi-select-trigger:hover {
    border-color: var(--primary);
}
.multi-select-trigger.open {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}
.multi-select-trigger-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.multi-select-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
    z-index: 100;
    display: none;
    max-height: 250px;
    overflow-y: auto;
}
.multi-select-dropdown.open {
    display: block;
}
.multi-select-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 13px;
    cursor: pointer;
    transition: background 0.15s;
}
.multi-select-option:hover {
    background: var(--bg-hover);
}
.multi-select-option input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
    flex-shrink: 0;
}
.multi-select-option label {
    cursor: pointer;
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
}
</style>
@endsection

@section('content')
<div style="max-width: 740px; margin: 0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-receipt" style="color: var(--primary)" aria-hidden="true"></i>
            Record Payment
        </div>
    </div>
    <div class="card-body" style="padding: 26px;">

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <div>@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>
        </div>
        @endif

        {{-- Success flash when redirected back after partial payment --}}
        @if(session('success'))
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle" aria-hidden="true" style="font-size:18px;flex-shrink:0"></i>
            <div style="flex:1">
                <div style="font-weight:700;font-size:15px">{{ session('success') }}</div>
                @if(request('more'))
                <div style="font-size:12px;margin-top:4px;opacity:0.9">
                    More unpaid months below — keep paying or
                    <a href="{{ route('payments.index') }}" style="color:inherit;text-decoration:underline;font-weight:600">view all payments</a>.
                </div>
                @endif
            </div>
            @if(session('last_payment_id'))
            <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap">
                <a href="{{ route('payments.show', session('last_payment_id')) }}"
                   class="btn btn-sm"
                   style="background:rgba(255,255,255,0.25);color:inherit;border:1px solid rgba(255,255,255,0.4);font-size:12px"
                   title="View payment details">
                    <i class="fas fa-eye" aria-hidden="true"></i> View
                </a>
                <a href="{{ route('payments.receipt', session('last_payment_id')) }}"
                   target="_blank" rel="noopener"
                   class="btn btn-sm"
                   style="background:rgba(255,255,255,0.25);color:inherit;border:1px solid rgba(255,255,255,0.4);font-size:12px"
                   title="Print receipt">
                    <i class="fas fa-print" aria-hidden="true"></i> Print
                </a>
                <a href="{{ route('payments.receipt.download', session('last_payment_id')) }}"
                   class="btn btn-sm"
                   style="background:rgba(255,255,255,0.25);color:inherit;border:1px solid rgba(255,255,255,0.4);font-size:12px"
                   title="Download PDF">
                    <i class="fas fa-download" aria-hidden="true"></i> PDF
                </a>
            </div>
            @endif
        </div>
        @endif

        <form action="{{ route('payments.store') }}" method="POST" id="paymentForm"
              enctype="multipart/form-data">
            @csrf

            {{-- Student Selection --}}
            <div class="form-group">
                <label class="form-label" for="studentSelect">
                    <i class="fas fa-user-graduate" style="margin-right: 6px"></i>
                    Student <span style="color: var(--danger)">*</span>
                </label>
                <select id="studentSelect" name="student_id"
                        class="form-control @error('student_id') is-invalid @enderror" required>
                    <option value="">— Select a student —</option>
                    @foreach($students as $student)
                    @php
                        $lastPay  = $student->payments->first();
                        $payDay   = (int)($student->monthly_payment_day ?? 1);
                        if ($lastPay && $lastPay->next_payment_date) {
                            $firstOwed = $lastPay->next_payment_date->format('Y-m-d');
                        } elseif ($lastPay && $lastPay->payment_date) {
                            $firstOwed = \App\Models\Student::nextPaymentDateFrom(
                                \Carbon\Carbon::parse($lastPay->payment_date), $payDay
                            )->format('Y-m-d');
                        } else {
                            $firstOwed = $student->enrollment_date->format('Y-m-d');
                        }
                    @endphp
                    <option value="{{ $student->id }}"
                        data-fee="{{ $student->monthly_fee }}"
                        data-discount="{{ $student->discount ?? 0 }}"
                        data-payment-day="{{ $payDay }}"
                        data-time-types="{{ json_encode($student->time_types ?? []) }}"
                        data-name="{{ $student->full_name }}"
                        data-first-owed="{{ $firstOwed }}"
                        {{ (old('student_id') ?? $selectedStudentId ?? null) == $student->id ? 'selected' : '' }}>
                        {{ $student->full_name }} — Grade {{ $student->year_level }} ({{ ucfirst($student->gender ?? 'n/a') }})
                    </option>
                    @endforeach
                </select>
                @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div id="noStudentMsg">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                Select a student to see their payment status
            </div>

            <div id="autoFields">

                {{-- Month Selection --}}
                <div class="section-title">
                    <i class="fas fa-calendar-alt"></i> Month to Pay
                </div>
                <div id="monthsPanel" class="months-panel" style="display: none">
                    <div class="months-panel-title">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        <span id="monthsPanelText">Select month to pay</span>
                    </div>
                    <div class="month-chips" id="monthChips"></div>
                </div>

                <div class="sel-month-box" id="selMonthBox" style="display: none">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    Recording payment for: <strong id="selMonthLabel">—</strong>
                </div>

                {{-- Hidden: which month is being covered (YYYY-MM-01) --}}
                <input type="hidden" id="coveringMonthInput" name="covering_month"
                       value="{{ old('covering_month') }}">
                {{-- Hidden: next payment date (YYYY-MM-DD) --}}
                <input type="hidden" id="nextPaymentInput" name="next_payment_date"
                       value="{{ old('next_payment_date') }}">

                {{-- Payment Details --}}
                <div class="section-title">
                    <i class="fas fa-money-bill-wave"></i> Payment Details
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="paidDateInput">
                            <i class="fas fa-calendar-day" style="margin-right: 4px"></i>
                            Paid Date <span style="color: var(--danger)">*</span>
                        </label>
                        <input type="date" id="paidDateInput" name="payment_date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px">
                            Date the student physically paid
                        </div>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="monthsCoveredInput">
                            <i class="fas fa-calendar-alt" style="margin-right: 4px"></i>
                            Months to Cover <span style="color: var(--danger)">*</span>
                        </label>
                        <input type="number" id="monthsCoveredInput" name="months_covered"
                               class="form-control @error('months_covered') is-invalid @enderror"
                               value="{{ old('months_covered', 1) }}" min="1" max="12" required>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px">
                            How many months this payment covers
                        </div>
                        @error('months_covered')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="time_types">
                        <i class="fas fa-clock" style="margin-right: 4px"></i>
                        Time Slots <span style="color: var(--danger)">*</span>
                        <span style="font-size: 11px; color: var(--success); font-weight: 500">(auto-filled)</span>
                    </label>
                    <div class="multi-select" id="timeTypesMultiSelect">
                        <div class="multi-select-trigger" id="timeTypesTrigger">
                            <span class="multi-select-trigger-text" id="timeTypesTriggerText">Select time slots...</span>
                        </div>
                        <div class="multi-select-dropdown" id="timeTypesDropdown">
                            @php
                                $slots = [
                                    'mon-fri 7:00-9:00' => 'Mon-Fri: 7:00 - 9:00 AM',
                                    'mon-fri 9:00-11:00' => 'Mon-Fri: 9:00 - 11:00 AM',
                                    'mon-fri 1:00-3:00' => 'Mon-Fri: 1:00 - 3:00 PM',
                                    'mon-fri 3:00-5:00' => 'Mon-Fri: 3:00 - 5:00 PM',
                                    'mon-fri 5:30-7:30' => 'Mon-Fri: 5:30 - 7:30 PM',
                                    'sat-sun 7:00-11:00' => 'Sat-Sun: 7:00 - 11:00 AM',
                                    'sat-sun 1:00-5:00' => 'Sat-Sun: 1:00 - 5:00 PM',
                                ];
                            @endphp
                            @foreach($slots as $value => $label)
                                <div class="multi-select-option">
                                    <input type="checkbox" id="time_slot_{{ $loop->index }}" name="time_types[]" value="{{ $value }}"
                                           {{ is_array(old('time_types')) && in_array($value, old('time_types')) ? 'checked' : '' }}
                                           class="time-slot-checkbox">
                                    <label for="time_slot_{{ $loop->index }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Select all applicable time slots</small>
                    @error('time_types')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Amounts --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="amountDueInput">
                            <i class="fas fa-dollar-sign" style="margin-right: 4px"></i>
                            Monthly Fee
                            <span style="font-size: 11px; color: var(--success); font-weight: 500">(auto-filled)</span>
                        </label>
                        <input type="number" id="amountDueInput" name="amount_due" step="0.01"
                               class="form-control @error('amount_due') is-invalid @enderror"
                               value="{{ old('amount_due', 0) }}" min="0">
                        @error('amount_due')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="discountInput">
                            <i class="fas fa-percent" style="margin-right: 4px"></i>
                            Discount (%)
                            <span style="font-size: 11px; color: var(--success); font-weight: 500">(auto-filled)</span>
                        </label>
                        <input type="number" id="discountInput" name="discount" step="0.01"
                               class="form-control @error('discount') is-invalid @enderror"
                               value="{{ old('discount', 0) }}" min="0" max="100">
                        @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-arrow-right" style="margin-right: 4px"></i>
                            Next Payment Date
                            <span style="font-size: 11px; color: var(--success); font-weight: 500">(auto-calculated)</span>
                        </label>
                        <input type="text" id="nextDateDisplay" class="form-control auto-field"
                               value="—" readonly tabindex="-1">
                    </div>
                </div>

                {{-- Payment Method & Photo --}}
                <div class="section-title">
                    <i class="fas fa-credit-card"></i> Additional Info
                </div>
                <div class="form-group">
                    <label class="form-label" for="payment_method">
                        Payment Method <span style="color: var(--danger)">*</span>
                    </label>
                    <select id="payment_method" name="payment_method"
                            class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">— Select —</option>
                        <option value="cash"          {{ old('payment_method')==='cash'?'selected':'' }}>💵 Cash</option>
                        <option value="bank_transfer" {{ old('payment_method')==='bank_transfer'?'selected':'' }}>🏦 Bank Transfer</option>
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Photo --}}
                <div class="form-group">
                    <label class="form-label" for="photo">{{ __('app.payment_photo') }}</label>
                    <input type="file" id="photo" name="photo"
                           class="form-control @error('photo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewUpload(this)">
                    <div id="uploadPreview" style="display:none;margin-top:8px">
                        <img id="uploadPreviewImg" src="" alt="Preview"
                             style="max-width:180px;max-height:180px;border-radius:8px;border:1px solid var(--border)">
                    </div>
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:11px;color:var(--text-muted);margin-top:3px">
                        JPG, PNG, WebP — max 2MB
                    </div>
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label class="form-label" for="notes">
                        <i class="fas fa-sticky-note" style="margin-right: 4px"></i>
                        Notes
                    </label>
                    <textarea id="notes" name="notes" class="form-control"
                              placeholder="Optional notes…" maxlength="500">{{ old('notes') }}</textarea>
                </div>

                {{-- ── Payment Summary ──────────────────── --}}
                <div class="payment-summary">
                    <div class="payment-summary-title">
                        <i class="fas fa-receipt" aria-hidden="true"></i>
                        Payment Summary
                    </div>
                    <div class="payment-fee-row">
                        <div class="payment-fee-breakdown">
                            <div class="fee-chip">
                                <span>Monthly Fee</span>
                                <span id="sumFee">$0.00</span>
                            </div>
                            <span class="fee-plus">-</span>
                            <div class="fee-chip">
                                <span>Discount</span>
                                <span id="sumDiscount">$0.00</span>
                            </div>
                            <span class="fee-plus">=</span>
                        </div>
                        <div class="payment-total-box">
                            <div class="payment-total-label">Total to Pay</div>
                            <div class="payment-total-val" id="sumTotalDue">$0.00</div>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--primary-200); font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-calendar-check" style="color: var(--success)" aria-hidden="true"></i>
                        Next payment: <strong id="sumNext">—</strong>
                    </div>
                </div>

                <div style="display:flex;gap:10px;padding-top:8px;flex-wrap:wrap">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save" aria-hidden="true"></i> Record Payment
                    </button>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline">Cancel</a>
                </div>

            </div>
        </form>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
function goBack() {
    const backBtn = document.getElementById('backBtn');
    if (history.length > 1) {
        history.back();
    } else if (backBtn) {
        window.location = backBtn.dataset.backUrl;
    }
}

(function () {
    'use strict';

    var sel              = document.getElementById('studentSelect');
    var autoFields       = document.getElementById('autoFields');
    var noMsg            = document.getElementById('noStudentMsg');
    var amountDueInput   = document.getElementById('amountDueInput');
    var discountInput    = document.getElementById('discountInput');
    var monthsCoveredInput = document.getElementById('monthsCoveredInput');
    var nextDisplay      = document.getElementById('nextDateDisplay');
    var ttSelect         = document.getElementById('timeTypesSelect');
    var sumFee           = document.getElementById('sumFee');
    var sumDiscount      = document.getElementById('sumDiscount');
    var sumTotalDue      = document.getElementById('sumTotalDue');
    var sumNext          = document.getElementById('sumNext');
    var monthsPanel      = document.getElementById('monthsPanel');
    var panelText        = document.getElementById('monthsPanelText');
    var monthChips       = document.getElementById('monthChips');
    var selMonthBox      = document.getElementById('selMonthBox');
    var selMonthLbl      = document.getElementById('selMonthLabel');
    var coverInput       = document.getElementById('coveringMonthInput');
    var nextHidden       = document.getElementById('nextPaymentInput');
    var submitBtn        = document.getElementById('submitBtn');

    var MF = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var ML = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    // Multi-select dropdown logic
    var timeTypesTrigger = document.getElementById('timeTypesTrigger');
    var timeTypesDropdown = document.getElementById('timeTypesDropdown');
    var timeTypesTriggerText = document.getElementById('timeTypesTriggerText');
    var timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');

    function updateTriggerText() {
        var selected = Array.from(timeSlotCheckboxes).filter(cb => cb.checked).map(cb => cb.nextElementSibling.textContent);
        if (selected.length === 0) {
            timeTypesTriggerText.textContent = 'Select time slots...';
        } else if (selected.length === 1) {
            timeTypesTriggerText.textContent = selected[0];
        } else {
            timeTypesTriggerText.textContent = selected.length + ' time slots selected';
        }
    }

    timeTypesTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        timeTypesTrigger.classList.toggle('open');
        timeTypesDropdown.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#timeTypesMultiSelect')) {
            timeTypesTrigger.classList.remove('open');
            timeTypesDropdown.classList.remove('open');
        }
    });

    timeSlotCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateTriggerText);
    });

    // Initialize trigger text
    updateTriggerText();

    /* ── Calculate total function ─────────────────────────── */
    function calculateTotal() {
        var baseFee = parseFloat(amountDueInput.value) || 0;
        var months = parseInt(monthsCoveredInput.value) || 1;
        var amountDue = baseFee * months;
        var discount = parseFloat(discountInput.value) || 0;
        var discountAmount = amountDue * (discount / 100);
        var totalDue = amountDue - discountAmount;

        sumFee.textContent = '$' + amountDue.toFixed(2);
        sumDiscount.textContent = '$' + discountAmount.toFixed(2);
        sumTotalDue.textContent = '$' + totalDue.toFixed(2);
    }

    /* ── Date helpers (no overflow) ─────────────────────────── */
    function addOneMonth(y, m) {
        return m === 11 ? {y: y+1, m: 0} : {y: y, m: m+1};
    }
    function addMonths(y, m, count) {
        var newY = y, newM = m;
        for (var i = 0; i < count; i++) {
            var nx = addOneMonth(newY, newM);
            newY = nx.y;
            newM = nx.m;
        }
        return {y: newY, m: newM};
    }
    function lastDay(y, m) {
        return new Date(y, m+1, 0).getDate();
    }
    function toISO(y, m, d) {
        return y + '-' + String(m+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
    }
    function parseISO(s) {
        var p = s.split('-');
        return {y:+p[0], m:+p[1]-1, d:+p[2]};
    }
    function fmtDate(iso) {
        if (!iso) return '—';
        var p = parseISO(iso);
        return p.d + ' ' + MF[p.m] + ' ' + p.y;
    }
    function fmtMonth(iso) {
        if (!iso) return '—';
        var p = parseISO(iso);
        return ML[p.m] + ' ' + p.y;
    }
    /* Next payment_day of month AFTER coverISO plus months covered (YYYY-MM-01) */
    function nextAfterCovering(coverISO, payDay, monthsCovered) {
        var p  = parseISO(coverISO);
        var nx = addMonths(p.y, p.m, monthsCovered);
        return toISO(nx.y, nx.m, Math.min(+payDay, lastDay(nx.y, nx.m)));
    }

    /* ── Build month chips ──────────────────────────────────── */
    function buildChips(firstOwedISO, payDay) {
        monthChips.innerHTML  = '';
        coverInput.value      = '';
        nextHidden.value      = '';
        selMonthBox.style.display = 'none';
        sumNext.textContent   = '—';
        nextDisplay.value     = '—';

        var today  = new Date();
        today.setHours(0,0,0,0);

        /* Walk months from firstOwed forward */
        var p      = parseISO(firstOwedISO);
        var cursor = new Date(p.y, p.m, 1);
        var unpaid = [], upcoming = [];
        var limit  = 60, count = 0;

        while (count < limit) {
            var cy   = cursor.getFullYear(), cm = cursor.getMonth();
            var iso  = toISO(cy, cm, 1);
            var fom  = new Date(cy, cm, 1);

            if (fom <= today) {
                unpaid.push(iso);
            } else {
                if (!upcoming.length) upcoming.push(iso);
                break;
            }
            var nx = addOneMonth(cy, cm);
            cursor = new Date(nx.y, nx.m, 1);
            count++;
        }

        /* If student is fully paid, show only upcoming */
        if (!unpaid.length && !upcoming.length) {
            /* Calculate next month from today */
            var nx2 = addOneMonth(today.getFullYear(), today.getMonth());
            upcoming.push(toISO(nx2.y, nx2.m, 1));
        }

        if (unpaid.length) {
            panelText.textContent = unpaid.length + ' unpaid month' + (unpaid.length>1?'s':'') + ' — select which to pay';
        } else {
            panelText.textContent = 'Student is up to date — select next upcoming month';
        }
        monthsPanel.style.display = 'block';

        unpaid.forEach(function(iso)   { appendChip(iso); });
        if (!unpaid.length) { // Only show upcoming if no unpaid months
            upcoming.forEach(function(iso) { appendChip(iso); });
        }

        /* Auto-select oldest unpaid (or upcoming if none) */
        var first = monthChips.querySelector('.month-chip');
        if (first) selectChip(first);
    }

    function appendChip(iso) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'month-chip';
        chip.dataset.iso = iso;
        chip.textContent = fmtMonth(iso);
        chip.addEventListener('click', function() { selectChip(this); });
        monthChips.appendChild(chip);
    }

    function selectChip(chip) {
        monthChips.querySelectorAll('.month-chip').forEach(function(c) { c.classList.remove('selected'); });
        chip.classList.add('selected');

        var opt           = sel.options[sel.selectedIndex];
        var iso           = chip.dataset.iso;
        var monthsCovered = parseInt(monthsCoveredInput.value) || 1;
        var nextISO       = nextAfterCovering(iso, opt.dataset.paymentDay, monthsCovered);

        /* Set hidden fields — these are what the server reads */
        coverInput.value = iso;
        nextHidden.value = nextISO;

        /* Update display */
        selMonthBox.style.display = 'flex';
        if (monthsCovered === 1) {
            selMonthLbl.textContent = fmtMonth(iso);
            submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Record Payment for ' + fmtMonth(iso);
        } else {
            var lastMonth = addMonths(parseISO(iso).y, parseISO(iso).m, monthsCovered - 1);
            selMonthLbl.textContent = fmtMonth(iso) + ' - ' + ML[lastMonth.m] + ' ' + lastMonth.y;
            submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Record Payment for ' + fmtMonth(iso) + ' - ' + ML[lastMonth.m] + ' ' + lastMonth.y;
        }
        sumNext.textContent = fmtDate(nextISO);
        nextDisplay.value = fmtDate(nextISO);
    }

    /* ── Update when student selected ──────────────────────── */
    function update() {
        if (!sel.value) {
            monthsPanel.style.display = 'none';
            return;
        }

        var opt        = sel.options[sel.selectedIndex];
        var fee        = parseFloat(opt.dataset.fee || 0);
        var discount   = parseFloat(opt.dataset.discount || 0);
        var timeTypes  = JSON.parse(opt.dataset.timeTypes || '[]');
        var name       = opt.dataset.name || opt.text;
        var firstOwed  = opt.dataset.firstOwed;

        amountDueInput.value = fee.toFixed(2);
        discountInput.value  = discount.toFixed(2);

        calculateTotal();

        /* Auto-fill time types */
        var timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');
        timeSlotCheckboxes.forEach(function(cb) {
            cb.checked = timeTypes.includes(cb.value);
        });
        updateTriggerText();

        buildChips(firstOwed, opt.dataset.paymentDay);
    }

    sel.addEventListener('change', update);
    amountDueInput.addEventListener('input', calculateTotal);
    discountInput.addEventListener('input', calculateTotal);
    monthsCoveredInput.addEventListener('input', function() {
        calculateTotal();
        if (coverInput.value) {
            // Re-calculate next payment date
            var opt = sel.options[sel.selectedIndex];
            var monthsCovered = parseInt(monthsCoveredInput.value) || 1;
            var nextISO = nextAfterCovering(coverInput.value, opt.dataset.paymentDay, monthsCovered);
            nextHidden.value = nextISO;
            sumNext.textContent = fmtDate(nextISO);
            nextDisplay.value = fmtDate(nextISO);
            
            // Update selected month label and submit button
            var iso = coverInput.value;
            selMonthBox.style.display = 'flex';
            if (monthsCovered === 1) {
                selMonthLbl.textContent = fmtMonth(iso);
                submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Record Payment for ' + fmtMonth(iso);
            } else {
                var lastMonth = addMonths(parseISO(iso).y, parseISO(iso).m, monthsCovered - 1);
                selMonthLbl.textContent = fmtMonth(iso) + ' - ' + ML[lastMonth.m] + ' ' + lastMonth.y;
                submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Record Payment for ' + fmtMonth(iso) + ' - ' + ML[lastMonth.m] + ' ' + lastMonth.y;
            }
        }
    });

    if (sel.value) update();
    else calculateTotal();

    /* ── Form submit validation ─────────────────────────────── */
    document.getElementById('paymentForm').addEventListener('submit', function (e) {
        /* Ensure covering_month is set */
        if (!coverInput.value) {
            e.preventDefault();
            monthsPanel.style.outline = '2px solid var(--danger)';
            monthsPanel.scrollIntoView({behavior:'smooth', block:'center'});
            alert('Please select which month you are paying for.');
            return;
        }
        if (!nextHidden.value) {
            /* Calculate fallback server-side — just let it through */
        }
        /* Show loading state */
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    });

}());
</script>

<script>
function previewUpload(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('uploadPreviewImg').src = e.target.result;
            document.getElementById('uploadPreview').style.display = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
