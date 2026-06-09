@php
    // Embed KantumruyPro as base64 — the only reliable way to render Khmer in DomPDF
    $fontB64Css = '';
    $fontFile   = storage_path('fonts/KantumruyPro.ttf');
    if (file_exists($fontFile)) {
        $b64        = base64_encode(file_get_contents($fontFile));
        $fontB64Css = "@font-face { font-family: 'KantumruyPro'; src: url('data:font/truetype;base64,{$b64}') format('truetype'); }";
    }
    $s = $payment->student; // shorthand
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    {!! $fontB64Css !!}
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'KantumruyPro', 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; background: white; }
    .receipt { width: 100%; padding: 18px 20px; }

    /* ── Header ── */
    .header { text-align: center; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 2px solid #1a56db; }
    .school-name  { font-size: 17px; font-weight: 700; color: #1a56db; margin-bottom: 2px; }
    .school-sub   { font-size: 9px; color: #6b7280; }
    .receipt-title{ font-size: 13px; font-weight: 700; margin: 8px 0 3px; text-transform: uppercase; letter-spacing: 1px; }
    .receipt-num  { font-size: 11px; color: #1a56db; font-weight: 600; }

    /* ── Section heading ── */
    .section-title {
        font-size: 8px; font-weight: 700; color: #1a56db;
        text-transform: uppercase; letter-spacing: 1px;
        border-bottom: 1px solid #dbeafe; padding-bottom: 3px;
        margin: 10px 0 6px;
    }

    /* ── Info rows ── */
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .info-table td { padding: 3px 4px; vertical-align: top; }
    .lbl { width: 38%; font-size: 8px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.4px; }
    .val { font-size: 11px; font-weight: 600; color: #111827; }

    /* ── Amount box ── */
    .amount-box { background: #f3f4f6; border-radius: 5px; padding: 10px 12px; margin: 10px 0; }
    .amount-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 11px; }
    .amount-row.total   { border-top: 1px solid #d1d5db; margin-top: 5px; padding-top: 7px; font-size: 12px; font-weight: 700; }
    .amount-row.balance { color: #e02424; font-weight: 700; }
    .amount-row.paid-amt{ color: #0e9f6e; }

    /* ── Badge ── */
    .badge      { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
    .badge-paid { background: #def7ec; color: #0e9f6e; }

    /* ── Watermark ── */
    .watermark { font-size: 38px; font-weight: 900; color: rgba(14,159,110,0.07); text-transform: uppercase; text-align: center; position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(-30deg); letter-spacing: 6px; pointer-events: none; }

    /* ── Divider ── */
    .divider { border: none; border-top: 1px dashed #e5e7eb; margin: 8px 0; }

    /* ── Signatures ── */
    .signature-area { display: flex; justify-content: space-between; margin-top: 22px; }
    .sig-line { border-top: 1px solid #d1d5db; padding-top: 4px; font-size: 8px; color: #6b7280; width: 110px; text-align: center; }

    /* ── Footer ── */
    .footer { margin-top: 14px; text-align: center; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
</style>
</head>
<body>
<div class="receipt">

    @if($payment->status === 'paid')
    <div class="watermark">PAID</div>
    @endif

    {{-- ── Header ── --}}
    <div class="header">
        <div class="school-name">EduPay Manager</div>
        <div class="school-sub">Student Payment Management System</div>
        <div class="receipt-title">Receipt</div>
        <div class="receipt-num">{{ $payment->receipt_number }}</div>
    </div>

    {{-- ── Student Information ── --}}
    <div class="section-title">Student Information</div>
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
        <tr>
            <td class="lbl">Address</td>
            <td class="val" colspan="3">{{ $s?->address ?? '—' }}</td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ── Class Information ── --}}
    <div class="section-title">Class Information</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Grade</td>
            <td class="val">Grade {{ $s?->year_level ?? '—' }}</td>
            <td class="lbl">Subject</td>
            <td class="val">{{ $s?->subject ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Time Slot</td>
            <td class="val">{{ $payment->time_type ?? ($s?->time_type ?? '—') }}</td>
            <td class="lbl">Class Type</td>
            <td class="val">
                @if(str_starts_with($payment->time_type ?? $s?->time_type ?? '', 'sat-sun'))
                    Weekend
                @else
                    Weekday
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Enrollment Date</td>
            <td class="val">{{ $s?->enrollment_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="lbl">Study Status</td>
            <td class="val">{{ $s?->study_status ? ucfirst($s->study_status) : 'Studying' }}</td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ── Payment Information ── --}}
    <div class="section-title">Payment Information</div>
    <table class="info-table">
        <tr>
            <td class="lbl">Payment Date</td>
            <td class="val">{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="lbl">For Month</td>
            <td class="val">{{ $payment->due_date?->format('F Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Payment Method</td>
            <td class="val">{{ $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : '—' }}</td>
            <td class="lbl">Next Payment</td>
            <td class="val">{{ $payment->next_payment_date?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @if($payment->notes)
        <tr>
            <td class="lbl">Notes</td>
            <td class="val" colspan="3">{{ $payment->notes }}</td>
        </tr>
        @endif
    </table>

    {{-- ── Amount Summary ── --}}
    <div class="amount-box">
        <div class="amount-row">
            <span>Monthly Fee</span>
            <span>${{ number_format($payment->amount_due, 2) }}</span>
        </div>
        @if(($payment->admin_fee ?? 0) > 0)
        <div class="amount-row">
            <span>Admin Fee (enrollment)</span>
            <span>${{ number_format($payment->admin_fee, 2) }}</span>
        </div>
        @endif
        <div class="amount-row paid-amt">
            <span>Amount Paid</span>
            <span>${{ number_format($payment->amount_paid, 2) }}</span>
        </div>
        @if(($payment->balance ?? 0) > 0)
        <div class="amount-row balance">
            <span>Balance Due</span>
            <span>${{ number_format($payment->balance, 2) }}</span>
        </div>
        @endif
        <div class="amount-row total">
            <span>Status</span>
            <span>
                @if($payment->status === 'paid')
                    <span class="badge badge-paid">PAID</span>
                @else
                    {{ strtoupper($payment->status) }}
                @endif
            </span>
        </div>
    </div>

    {{-- ── Signatures ── --}}
    <div class="signature-area">
        <div class="sig-line">Student Signature</div>
        <div class="sig-line">Cashier / Processed By<br>{{ $payment->creator?->name ?? 'Admin' }}</div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <div>{{ now()->format('d/m/Y \a\t h:i A') }}</div>
        <div style="margin-top:2px">EduPay Manager — Student Payment Management System</div>
        <div style="margin-top:2px;font-size:7px">Official receipt. Keep for records.</div>
    </div>

</div>
</body>
</html>
