<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: kantumruypro, DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: white; }
    .receipt { width: 100%; padding: 20px; }
    .header { text-align: center; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 2px solid #1a56db; }
    .school-name { font-size: 18px; font-weight: 700; color: #1a56db; margin-bottom: 3px; }
    .school-sub { font-size: 10px; color: #6b7280; }
    .receipt-title { font-size: 14px; font-weight: 700; margin: 10px 0 4px; text-transform: uppercase; letter-spacing: 1px; }
    .receipt-num { font-size: 12px; color: #1a56db; font-weight: 600; }
    .info-grid { display: flex; gap: 0; margin-bottom: 14px; }
    .info-col { flex: 1; }
    .info-label { font-size: 8px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .info-val { font-size: 11px; font-weight: 600; color: #111827; }
    .amount-box { background: #f3f4f6; border-radius: 6px; padding: 12px; margin: 14px 0; }
    .amount-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 11px; }
    .amount-row.total { border-top: 1px solid #d1d5db; margin-top: 6px; padding-top: 8px; font-size: 13px; font-weight: 700; }
    .amount-row.balance { color: #e02424; font-weight: 700; }
    .amount-row.paid-amt { color: #0e9f6e; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
    .badge-paid { background: #def7ec; color: #0e9f6e; }
    .footer { margin-top: 18px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    .signature-area { display: flex; justify-content: space-between; margin-top: 28px; }
    .sig-line { border-top: 1px solid #d1d5db; padding-top: 5px; font-size: 9px; color: #6b7280; width: 120px; text-align: center; }
    .watermark { font-size: 40px; font-weight: 900; color: rgba(14,159,110,0.08); text-transform: uppercase; text-align: center; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); letter-spacing: 6px; pointer-events: none; }
    .divider { border: none; border-top: 1px dashed #d1d5db; margin: 12px 0; }
</style>
</head>
<body>
<div class="receipt">
    @if($payment->status === 'paid')
    <div class="watermark">PAID</div>
    @endif

    <div class="header">
        <div class="school-name">EduPay Manager</div>
        <div class="school-sub">Student Payment Management System</div>
        <div class="receipt-title">{{ __('app.receipt') }}</div>
        <div class="receipt-num">{{ $payment->receipt_number }}</div>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <div class="info-label">{{ __('app.first_name') }} {{ __('app.last_name') }}</div>
            <div class="info-val">{{ $payment->student?->full_name ?? '—' }} ({{ $payment->student?->gender ? __('app.'.$payment->student->gender) : 'N/A' }})</div>
        </div>
        <div class="info-col">
            <div class="info-label">{{ __('app.student_id') }}</div>
            <div class="info-val">{{ $payment->student?->student_id ?? '—' }}</div>
        </div>
    </div>
    <div class="info-grid">
        <div class="info-col">
            <div class="info-label">{{ __('app.come_from') }}</div>
            <div class="info-val">{{ $payment->student?->come_from ?? '-' }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">{{ __('app.grade') }}</div>
            <div class="info-val">{{ __('app.grade') }} {{ $payment->student?->year_level ?? '—' }}</div>
        </div>
    </div>

    <hr class="divider">

    <div class="info-grid">
        <div class="info-col">
            <div class="info-label">{{ __('app.subject') }}</div>
            <div class="info-val">{{ $payment->student?->subject ?? '-' }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">{{ __('app.time_type') }}</div>
            <div class="info-val">{{ ucfirst($payment->time_type ?? 'weekday') }}</div>
        </div>
    </div>

    <div class="info-grid" style="margin-top:8px">
        <div class="info-col">
            <div class="info-label">{{ __('app.for_month') }}</div>
            <div class="info-val">{{ $payment->due_date?->format('F Y') ?? '-' }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">{{ __('app.next_payment') }}</div>
            <div class="info-val">{{ $payment->next_payment_date?->format('d/m/Y') ?? '-' }}</div>
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-row">
            <span>{{ __('app.monthly_fee') }}</span>
            <span>${{ number_format($payment->amount_due, 2) }}</span>
        </div>
        @if($payment->admin_fee > 0)
        <div class="amount-row">
            <span>Admin Fee</span>
            <span>${{ number_format($payment->admin_fee ?? 0, 2) }}</span>
        </div>
        @endif
        <div class="amount-row paid-amt">
            <span>{{ __('app.paid') }}</span>
            <span>${{ number_format($payment->amount_paid, 2) }}</span>
        </div>
        @if($payment->balance > 0)
        <div class="amount-row balance">
            <span>Balance</span>
            <span>${{ number_format($payment->balance, 2) }}</span>
        </div>
        @endif
        <div class="amount-row total">
            <span>{{ __('app.status') }}</span>
            <span>
                @if($payment->status === 'paid')
                    <span class="badge badge-paid">{{ strtoupper(__('app.paid')) }}</span>
                @else
                    <span>{{ strtoupper($payment->status) }}</span>
                @endif
            </span>
        </div>
    </div>

    <div class="signature-area">
        <div class="sig-line">Student Signature</div>
        <div class="sig-line">Cashier / Processed By<br>{{ $payment->creator?->name ?? 'System' }}</div>
    </div>

    <div class="footer">
        <div>{{ now()->format('d/m/Y \a\t h:i A') }}</div>
        <div style="margin-top:3px">EduPay Manager — Student Payment Management System</div>
        <div style="margin-top:3px;font-size:8px">Official receipt. Keep for records.</div>
    </div>
</div>
</body>
</html>
