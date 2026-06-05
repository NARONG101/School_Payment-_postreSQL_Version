@extends('layouts.app')
@section('title','New Payment')
@section('page-title','New Payment')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            onclick="history.length>1?history.back():window.location='{{ route('payments.index') }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection

@section('styles')
<style>
.auto-field{background:var(--bg-muted)!important;color:var(--text-secondary)!important;cursor:default;font-weight:600;}
.auto-field:focus{box-shadow:none!important;border-color:var(--border-input)!important;}

.pay-summary{
    background:var(--primary-50);border:1px solid var(--primary-200);
    border-radius:var(--radius);padding:16px 20px;margin-bottom:18px;
    display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:12px;
}
.pay-summary-label{font-size:10px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;}
.pay-summary-val{font-size:17px;font-weight:900;color:var(--primary);}

#noStudentMsg{text-align:center;padding:32px 20px;color:var(--text-muted);font-size:14px;}
#noStudentMsg i{font-size:32px;display:block;margin-bottom:10px;}
#autoFields{display:none;}
#autoFields.show{display:block;}

.months-panel{border-radius:var(--radius);padding:14px 16px;margin-bottom:14px;border:1px solid var(--border);}
.months-panel.has-overdue{background:var(--danger-light);border-color:var(--danger);}
.months-panel.up-to-date{background:var(--success-light);border-color:var(--success);}
.months-panel-title{font-size:13px;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:6px;}
.months-panel.has-overdue .months-panel-title{color:var(--danger);}
.months-panel.up-to-date  .months-panel-title{color:var(--success);}

.month-chips{display:flex;flex-wrap:wrap;gap:6px;}
.month-chip{
    padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;
    cursor:pointer;border:2px solid transparent;transition:all .15s;
    user-select:none;-webkit-tap-highlight-color:transparent;
}
.month-chip.overdue {background:var(--danger-light);color:var(--danger);border-color:var(--danger);}
.month-chip.upcoming{background:var(--primary-light);color:var(--primary);border-color:var(--primary);}
.month-chip.paid    {background:var(--success-light);color:var(--success);border-color:var(--success);cursor:default;opacity:0.7;}
.month-chip.selected{color:#fff;}
.month-chip.overdue.selected {background:var(--danger);}
.month-chip.upcoming.selected{background:var(--primary);}

.sel-month-box{
    background:var(--warning-light);border:1px solid var(--warning);
    border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:16px;
    font-size:13px;color:var(--warning);font-weight:600;
    display:flex;align-items:center;gap:8px;
}
</style>
@endsection

@section('content')
<div style="max-width:700px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-receipt" style="color:var(--primary)" aria-hidden="true"></i>
            Record Payment
        </div>
    </div>
    <div class="card-body">

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

            {{-- Student --}}
            <div class="form-group">
                <label class="form-label" for="studentSelect">
                    Student <span style="color:var(--danger)">*</span>
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
                        data-payment-day="{{ $payDay }}"
                        data-time-type="{{ strtolower($student->time_type ?? '') }}"
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

                {{-- Summary banner --}}
                <div class="pay-summary">
                    <div>
                        <div class="pay-summary-label">Student</div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-heading)" id="sumName">—</div>
                    </div>
                    <div>
                        <div class="pay-summary-label">Monthly Fee</div>
                        <div class="pay-summary-val" id="sumFee">$0.00</div>
                    </div>
                    <div>
                        <div class="pay-summary-label">Paying For</div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-primary)" id="sumFor">—</div>
                    </div>
                    <div>
                        <div class="pay-summary-label">Next Due After</div>
                        <div style="font-size:14px;font-weight:700;color:var(--primary)" id="sumNext">—</div>
                    </div>
                </div>

                {{-- Month chips --}}
                <div id="monthsPanel" class="months-panel" style="display:none">
                    <div class="months-panel-title">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        <span id="monthsPanelText">Select month to pay</span>
                    </div>
                    <div class="month-chips" id="monthChips"></div>
                </div>

                <div class="sel-month-box" id="selMonthBox" style="display:none">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    Recording payment for: <strong id="selMonthLabel">—</strong>
                </div>

                {{-- Hidden: which month is being covered (YYYY-MM-01) --}}
                <input type="hidden" id="coveringMonthInput" name="covering_month"
                       value="{{ old('covering_month') }}">
                {{-- Hidden: next payment date (YYYY-MM-DD) --}}
                <input type="hidden" id="nextPaymentInput" name="next_payment_date"
                       value="{{ old('next_payment_date') }}">

                {{-- Paid Date + Time Type --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="paidDateInput">
                            Actual Paid Date <span style="color:var(--danger)">*</span>
                        </label>
                        <input type="date" id="paidDateInput" name="payment_date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:3px">
                            Date the student physically paid
                        </div>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="timeTypeSelect">
                            Time Type <span style="color:var(--danger)">*</span>
                            <span style="font-size:11px;color:var(--success);font-weight:500">(auto-filled)</span>
                        </label>
                        <select id="timeTypeSelect" name="time_type"
                                class="form-control auto-field @error('time_type') is-invalid @enderror" required>
                            <option value="">—</option>
                            @foreach(['mon-fri 7:00-9:00','mon-fri 9:00-11:00','mon-fri 1:00-3:00','mon-fri 3:00-5:00','mon-fri 5:30-7:30','sat-sun 7:00-11:00','sat-sun 1:00-5:00'] as $slot)
                            <option value="{{ $slot }}" {{ old('time_type')===$slot?'selected':'' }}>{{ $slot }}</option>
                            @endforeach
                        </select>
                        @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Fee display + next date display (display-only, not submitted) --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Monthly Fee
                            <span style="font-size:11px;color:var(--success);font-weight:500">(auto-filled)</span>
                        </label>
                        <input type="text" id="feeDisplay" class="form-control auto-field"
                               value="$0.00" readonly tabindex="-1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Next Payment Date
                            <span style="font-size:11px;color:var(--success);font-weight:500">(auto-calculated)</span>
                        </label>
                        <input type="text" id="nextDateDisplay" class="form-control auto-field"
                               value="—" readonly tabindex="-1">
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid var(--border);margin:4px 0 18px">

                {{-- Payment Method --}}
                <div class="form-group">
                    <label class="form-label" for="payment_method">
                        Payment Method <span style="color:var(--danger)">*</span>
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
                    <label class="form-label" for="photo">Payment Photo</label>
                    <input type="file" id="photo" name="photo"
                           class="form-control @error('photo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:11px;color:var(--text-muted);margin-top:3px">Optional — JPG, PNG, WebP, max 2MB</div>
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control"
                              placeholder="Optional notes…" maxlength="500">{{ old('notes') }}</textarea>
                </div>

                <div style="display:flex;gap:10px;padding-top:4px;flex-wrap:wrap">
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
(function () {
    'use strict';

    var sel          = document.getElementById('studentSelect');
    var autoFields   = document.getElementById('autoFields');
    var noMsg        = document.getElementById('noStudentMsg');
    var feeDisplay   = document.getElementById('feeDisplay');
    var nextDisplay  = document.getElementById('nextDateDisplay');
    var ttSelect     = document.getElementById('timeTypeSelect');
    var sumName      = document.getElementById('sumName');
    var sumFee       = document.getElementById('sumFee');
    var sumFor       = document.getElementById('sumFor');
    var sumNext      = document.getElementById('sumNext');
    var monthsPanel  = document.getElementById('monthsPanel');
    var panelText    = document.getElementById('monthsPanelText');
    var monthChips   = document.getElementById('monthChips');
    var selMonthBox  = document.getElementById('selMonthBox');
    var selMonthLbl  = document.getElementById('selMonthLabel');
    var coverInput   = document.getElementById('coveringMonthInput');
    var nextHidden   = document.getElementById('nextPaymentInput');
    var submitBtn    = document.getElementById('submitBtn');

    var MF = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var ML = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    /* ── Date helpers (no overflow) ─────────────────────────── */
    function addOneMonth(y, m) {
        return m === 11 ? {y: y+1, m: 0} : {y: y, m: m+1};
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
    /* Next payment_day of month AFTER coverISO (YYYY-MM-01) */
    function nextAfterCovering(coverISO, payDay) {
        var p  = parseISO(coverISO);
        var nx = addOneMonth(p.y, p.m);
        return toISO(nx.y, nx.m, Math.min(+payDay, lastDay(nx.y, nx.m)));
    }

    /* ── Build month chips ──────────────────────────────────── */
    function buildChips(firstOwedISO, payDay) {
        monthChips.innerHTML  = '';
        coverInput.value      = '';
        nextHidden.value      = '';
        selMonthBox.style.display = 'none';
        sumFor.textContent    = '—';
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
            monthsPanel.className = 'months-panel has-overdue';
            panelText.textContent = unpaid.length + ' unpaid month' + (unpaid.length>1?'s':'') + ' — select which to pay';
        } else {
            monthsPanel.className = 'months-panel up-to-date';
            panelText.textContent = 'Student is up to date — select next upcoming month';
        }
        monthsPanel.style.display = 'block';

        unpaid.forEach(function(iso)   { appendChip(iso, 'overdue'); });
        upcoming.forEach(function(iso) { appendChip(iso, 'upcoming'); });

        /* Auto-select oldest unpaid (or upcoming if none) */
        var first = monthChips.querySelector('.month-chip');
        if (first) selectChip(first);
    }

    function appendChip(iso, type) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'month-chip ' + type;
        chip.dataset.iso = iso;
        chip.textContent = fmtMonth(iso);
        chip.addEventListener('click', function() { selectChip(this); });
        monthChips.appendChild(chip);
    }

    function selectChip(chip) {
        monthChips.querySelectorAll('.month-chip').forEach(function(c) { c.classList.remove('selected'); });
        chip.classList.add('selected');

        var opt     = sel.options[sel.selectedIndex];
        var iso     = chip.dataset.iso;
        var nextISO = nextAfterCovering(iso, opt.dataset.paymentDay);

        /* Set hidden fields — these are what the server reads */
        coverInput.value = iso;
        nextHidden.value = nextISO;

        /* Update display */
        selMonthBox.style.display = 'flex';
        selMonthLbl.textContent   = fmtMonth(iso);
        sumFor.textContent        = fmtMonth(iso);
        sumNext.textContent       = fmtDate(nextISO);
        nextDisplay.value         = fmtDate(nextISO);

        submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Record Payment for ' + fmtMonth(iso);
    }

    /* ── Update when student selected ──────────────────────── */
    function update() {
        if (!sel.value) {
            autoFields.classList.remove('show');
            noMsg.style.display = '';
            monthsPanel.style.display = 'none';
            return;
        }
        autoFields.classList.add('show');
        noMsg.style.display = 'none';

        var opt       = sel.options[sel.selectedIndex];
        var fee       = parseFloat(opt.dataset.fee || 0);
        var tt        = (opt.dataset.timeType || '').trim().toLowerCase();
        var name      = opt.dataset.name || opt.text;
        var firstOwed = opt.dataset.firstOwed;

        feeDisplay.value    = '$' + fee.toFixed(2);
        sumName.textContent = name;
        sumFee.textContent  = '$' + fee.toFixed(2);

        /* Auto-fill time type */
        for (var i = 0; i < ttSelect.options.length; i++) {
            if (ttSelect.options[i].value.toLowerCase() === tt) {
                ttSelect.selectedIndex = i;
                break;
            }
        }

        buildChips(firstOwed, opt.dataset.paymentDay);
    }

    sel.addEventListener('change', update);
    if (sel.value) update();

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
@endsection
