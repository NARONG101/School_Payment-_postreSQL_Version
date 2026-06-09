@php $s = $payment->student; @endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
/* ── Reset ── */
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: kantumruypro, sans-serif;
    font-size: 11pt;
    color: #1e293b;
    background: #ffffff;
}

/* ── Page wrapper ── */
.page {
    width: 100%;
    padding: 20px 22px;
}

/* ════════════════════════════════════
   HEADER
════════════════════════════════════ */
.header {
    text-align: center;
    padding-bottom: 12px;
    border-bottom: 2.5px solid #1a56db;
    margin-bottom: 14px;
}
.school-name {
    font-size: 20pt;
    font-weight: 700;
    color: #1a56db;
    letter-spacing: 1px;
}
.school-sub {
    font-size: 9pt;
    color: #64748b;
    margin-top: 2px;
}
.receipt-badge {
    display: inline-block;
    margin-top: 8px;
    background: #1a56db;
    color: #ffffff;
    font-size: 9pt;
    font-weight: 700;
    letter-spacing: 2px;
    padding: 3px 18px;
    border-radius: 20px;
    text-transform: uppercase;
}
.receipt-num {
    font-size: 10pt;
    color: #1a56db;
    font-weight: 700;
    margin-top: 4px;
}

/* ════════════════════════════════════
   SECTION HEADING
════════════════════════════════════ */
.section-head {
    background: #eff6ff;
    border-left: 3px solid #1a56db;
    padding: 4px 10px;
    font-size: 8pt;
    font-weight: 700;
    color: #1a56db;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin: 12px 0 6px;
}

/* ════════════════════════════════════
   INFO TABLE
════════════════════════════════════ */
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    padding: 3px 6px;
    vertical-align: top;
    font-size: 10pt;
}
.info-table .lbl {
    width: 28%;
    color: #94a3b8;
    font-size: 8pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding-top: 4px;
}
.info-table .val {
    color: #0f172a;
    font-weight: 600;
    border-bottom: 1px dotted #e2e8f0;
}
.info-table .val-wide {
    color: #0f172a;
    font-weight: 600;
    border-bottom: 1px dotted #e2e8f0;
}

/* ════════════════════════════════════
   DIVIDER
════════════════════════════════════ */
.divider {
    border: none;
    border-top: 1px dashed #cbd5e1;
    margin: 10px 0;
}

/* ════════════════════════════════════
   AMOUNT BOX
════════════════════════════════════ */
.amount-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 14px;
    margin-top: 12px;
}
.amount-row {
    width: 100%;
    display: table;
    padding: 3px 0;
    font-size: 10.5pt;
    border-bottom: 1px solid #f1f5f9;
}
.amount-row:last-child { border-bottom: none; }
.ar-label { display: table-cell; color: #475569; }
.ar-value { display: table-cell; text-align: right; font-weight: 600; color: #0f172a; }
.amount-row.paid .ar-value  { color: #0e9f6e; }
.amount-row.bal  .ar-value  { color: #e02424; font-weight: 700; }
.amount-row.total-row {
    margin-top: 4px;
    padding-top: 6px;
    border-top: 2px solid #e2e8f0;
    font-size: 12pt;
    font-weight: 700;
}
.amount-row.total-row .ar-label { color: #0f172a; font-weight: 700; }

/* ════════════════════════════════════
   STATUS BADGE
════════════════════════════════════ */
.status-paid {
    display: inline-block;
    background: #def7ec;
    color: #0e9f6e;
    font-size: 9pt;
    font-weight: 700;
    padding: 2px 14px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.status-other {
    display: inline-block;
    background: #fef3c7;
    color: #d97706;
    font-size: 9pt;
    font-weight: 700;
    padding: 2px 14px;
    border-radius: 20px;
    text-transform: uppercase;
}

/* ════════════════════════════════════
   WATERMARK
════════════════════════════════════ */
.watermark {
    position: fixed;
    top: 42%;
    left: 20%;
    font-size: 52pt;
    font-weight: 900;
    color: rgba(14,159,110,0.06);
    text-transform: uppercase;
    letter-spacing: 10px;
    transform: rotate(-30deg);
    z-index: -1;
}

/* ════════════════════════════════════
   SIGNATURES
════════════════════════════════════ */
.sig-row {
    width: 100%;
    display: table;
    margin-top: 24px;
}
.sig-cell {
    display: table-cell;
    width: 50%;
    text-align: center;
    font-size: 8.5pt;
    color: #64748b;
}
.sig-line {
    border-top: 1px solid #cbd5e1;
    padding-top: 5px;
    margin: 0 20px;
}

/* ════════════════════════════════════
   FOOTER
════════════════════════════════════ */
.footer {
    margin-top: 16px;
    text-align: center;
    font-size: 8pt;
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    padding-top: 10px;
}
</style>
</head>
<body>
<div class="page">

    @if($payment->status === 'paid')
    <div class="watermark">PAID</div>
    @endif

    {{-- ══ HEADER ══ --}}
    <div class="header">
        <div class="school-name">EduPay Manager</div>
        <div class="school-sub">Student Payment Management System</div>
        <div><span class="receipt-badge">Receipt</span></div>
        <div class="receipt-num">{{ $payment->receipt_number }}</div>
    </div>

    {{-- ══ STUDENT INFORMATION ══ --}}
    <div class="section-head">Student Information</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Student ID</td>
            <td class="val">{{ $s?->student_id ?? '—' }}</td>
            <td class="lbl">Full Name</td>
            <td class="val">{{ $s?->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Gender</td>
            <td class="val">{{ $s?->gender ? ucfirst($s->gender) : '—' }}</td>
            <td class="lbl">Date of Birth</td>
            <td class="val">{{ $s?->date_of_birth?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Phone</td>
            <td class="val">{{ $s?->phone ?? '—' }}</td>
            <td class="lbl">Come From</td>
            <td class="val">{{ $s?->come_from ?? '—' }}</td>
        </tr>
        @if($s?->address)
        <tr>
            <td class="lbl">Address</td>
            <td class="val-wide" colspan="3">{{ $s->address }}</td>
        </tr>
        @endif
    </table>

    {{-- ══ CLASS INFORMATION ══ --}}
    <div class="section-head">Class Information</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Grade</td>
            <td class="val">Grade {{ $s?->year_level ?? '—' }}</td>
            <td class="lbl">Subject</td>
            <td class="val">{{ $s?->subject ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Time Slot</td>
            <td class="val">{{ $payment->time_type ?? $s?->time_type ?? '—' }}</td>
            <td class="lbl">Class Type</td>
            <td class="val">
                @php $tt = $payment->time_type ?? $s?->time_type ?? ''; @endphp
                {{ str_starts_with($tt, 'sat-sun') ? 'Weekend' : 'Weekday' }}
            </td>
        </tr>
        <tr>
            <td class="lbl">Enrolled</td>
            <td class="val">{{ $s?->enrollment_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="lbl">Study Status</td>
            <td class="val">{{ $s?->study_status ? ucfirst($s->study_status) : 'Studying' }}</td>
        </tr>
    </table>

    {{-- ══ PAYMENT INFORMATION ══ --}}
    <div class="section-head">Payment Information</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Payment Date</td>
            <td class="val">{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="lbl">For Month</td>
            <td class="val">{{ $payment->due_date?->format('F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Method</td>
            <td class="val">{{ $payment->payment_method ? ucfirst(str_replace('_',' ',$payment->payment_method)) : '—' }}</td>
            <td class="lbl">Next Payment</td>
            <td class="val">{{ $payment->next_payment_date?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @if($payment->notes)
        <tr>
            <td class="lbl">Notes</td>
            <td class="val-wide" colspan="3">{{ $payment->notes }}</td>
        </tr>
        @endif
    </table>

    {{-- ══ AMOUNT SUMMARY ══ --}}
    <div class="amount-box">
        <div class="amount-row">
            <span class="ar-label">Monthly Fee</span>
            <span class="ar-value">${{ number_format($payment->amount_due, 2) }}</span>
        </div>
        @if(($payment->admin_fee ?? 0) > 0)
        <div class="amount-row">
            <span class="ar-label">Admin Fee <small style="color:#94a3b8">(enrollment)</small></span>
            <span class="ar-value">${{ number_format($payment->admin_fee, 2) }}</span>
        </div>
        @endif
        <div class="amount-row paid">
            <span class="ar-label">Amount Paid</span>
            <span class="ar-value">${{ number_format($payment->amount_paid, 2) }}</span>
        </div>
        @if(($payment->balance ?? 0) > 0)
        <div class="amount-row bal">
            <span class="ar-label">Balance Due</span>
            <span class="ar-value">${{ number_format($payment->balance, 2) }}</span>
        </div>
        @endif
        <div class="amount-row total-row">
            <span class="ar-label">Status</span>
            <span class="ar-value">
                @if($payment->status === 'paid')
                    <span class="status-paid">Paid</span>
                @else
                    <span class="status-other">{{ ucfirst($payment->status) }}</span>
                @endif
            </span>
        </div>
    </div>

    {{-- ══ SIGNATURES ══ --}}
    <div class="sig-row">
        <div class="sig-cell">
            <div class="sig-line">Student Signature</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">
                Cashier / Processed By<br>
                <strong>{{ $payment->creator?->name ?? 'Admin' }}</strong>
            </div>
        </div>
    </div>

    {{-- ══ FOOTER ══ --}}
    <div class="footer">
        <div>Printed: {{ now()->format('d/m/Y \a\t h:i A') }}</div>
        <div style="margin-top:2px">EduPay Manager &mdash; Student Payment Management System</div>
        <div style="margin-top:2px; font-size:7.5pt; color:#cbd5e1">Official receipt. Keep for records.</div>
    </div>

</div>
</body>
</html>
