@extends('layouts.app')
@section('title', 'Payment '.$payment->receipt_number)
@section('page-title', 'Payment Details')

@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            onclick="history.length>1?history.back():window.location='{{ route('payments.index') }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('topbar-actions')
    <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener">
        <i class="fas fa-print" aria-hidden="true"></i> <span>Receipt</span>
    </a>
    <a href="{{ route('payments.receipt.download', $payment) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-download" aria-hidden="true"></i> <span>PDF</span>
    </a>
    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-edit" aria-hidden="true"></i> <span>Edit</span>
    </a>
@endsection

@section('styles')
<style>
/* ── Amount boxes ─────────────────────────────────── */
.amount-box {
    background:var(--bg-muted); border-radius:var(--radius);
    padding:20px; margin-bottom:18px;
}
.amount-cols { display:grid; gap:0; }
.amount-col  { text-align:center; padding:0 8px; }
.amount-col + .amount-col { border-left:1px solid var(--border); }
.amount-col-label { font-size:10px; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.amount-col-val   { font-size:22px; font-weight:900; line-height:1; }

/* ── Info boxes ───────────────────────────────────── */
.info-box { padding:12px; background:var(--bg-muted); border-radius:var(--radius-sm); }
.info-box-label { font-size:10px; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
.info-box-val   { font-size:14px; font-weight:600; color:var(--text-primary); }

/* ── Next payment countdown ───────────────────────── */
.next-payment-card {
    border-radius:var(--radius);
    padding:20px; text-align:center;
    margin-bottom:14px;
}
.next-payment-card.status-paid     { background:var(--success-light); border:1px solid var(--success); }
.next-payment-card.status-overdue  { background:var(--danger-light);  border:1px solid var(--danger); }
.next-payment-card.status-soon     { background:var(--warning-light); border:1px solid var(--warning); }
.next-payment-card.status-upcoming { background:var(--primary-50);    border:1px solid var(--primary-200); }

.countdown-number { font-size:48px; font-weight:900; line-height:1; }
.countdown-label  { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-top:4px; }
.countdown-date   { font-size:13px; margin-top:8px; opacity:0.8; }

/* ── Student mini card ────────────────────────────── */
.student-mini {
    display:flex; align-items:center; gap:12px;
    padding:14px; background:var(--bg-muted);
    border-radius:var(--radius-sm); margin-bottom:14px;
    text-decoration:none; color:inherit;
    border:1px solid var(--border);
    transition:box-shadow 0.15s, border-color 0.15s;
}
.student-mini:hover { box-shadow:var(--shadow-md); border-color:var(--primary); }
.student-mini-avatar {
    width:44px; height:44px; background:var(--primary-light);
    border-radius:50%; display:flex; align-items:center;
    justify-content:center; font-weight:800; color:var(--primary);
    font-size:16px; flex-shrink:0;
}
</style>
@endsection

@section('content')
@php
    /* ── Next payment calculation ─────────────────── */
    $nextDate  = $payment->next_payment_date;
    $daysLeft  = $nextDate ? (int) now()->diffInDays($nextDate, false) : null;

    /* ── Check if MORE months still overdue for this student ── */
    $stillOverdue   = false;
    $overdueCount   = 0;
    $nextMonthLabel = null;

    if ($payment->student && $nextDate) {
        $nextMonthStart = \Carbon\Carbon::parse($nextDate)->startOfMonth();
        $stillOverdue   = $nextMonthStart->lte(now()->startOfMonth());

        if ($stillOverdue) {
            // Count how many months are still owed
            $cursor  = $nextMonthStart->copy();
            $today   = now()->startOfMonth();
            while ($cursor->lte($today) && $overdueCount < 60) {
                $overdueCount++;
                $cursor->addMonth();
            }
            $nextMonthLabel = $nextMonthStart->format('F Y');
        }
    }

    if ($payment->status !== 'paid') {
        $countdownClass = 'status-overdue';
        $countdownColor = 'var(--danger)';
        $countdownIcon  = 'fas fa-exclamation-circle';
        $countdownTitle = 'Payment Overdue';
    } elseif ($daysLeft === null) {
        $countdownClass = 'status-upcoming';
        $countdownColor = 'var(--primary)';
        $countdownIcon  = 'fas fa-calendar';
        $countdownTitle = 'Next Payment';
    } elseif ($daysLeft < 0) {
        $countdownClass = 'status-overdue';
        $countdownColor = 'var(--danger)';
        $countdownIcon  = 'fas fa-exclamation-circle';
        $countdownTitle = 'Next Payment Overdue';
    } elseif ($daysLeft <= 7) {
        $countdownClass = 'status-soon';
        $countdownColor = 'var(--warning)';
        $countdownIcon  = 'fas fa-bell';
        $countdownTitle = 'Due Soon';
    } else {
        $countdownClass = 'status-upcoming';
        $countdownColor = 'var(--primary)';
        $countdownIcon  = 'fas fa-calendar-check';
        $countdownTitle = 'Next Payment';
    }
@endphp

<div class="payment-detail-grid" style="max-width:1040px">

    {{-- ── LEFT: Main payment detail ─────────────── --}}
    <div>
        <div class="card">
            {{-- Header --}}
            <div class="card-header">
                <div>
                    <div class="card-title">
                        Receipt:
                        <span class="mono" style="color:var(--primary)">{{ $payment->receipt_number }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                        Recorded {{ $payment->created_at->format('M d, Y') }} at {{ $payment->created_at->format('h:i A') }}
                        @if($payment->creator) · by {{ $payment->creator->name }} @endif
                    </div>
                </div>
                @if($payment->status==='paid')
                    <span class="badge badge-success" style="font-size:12px;padding:5px 14px">✓ PAID</span>
                @elseif($payment->status==='partial')
                    <span class="badge badge-warning" style="font-size:12px;padding:5px 14px">PARTIAL</span>
                @elseif($payment->status==='overdue')
                    <span class="badge badge-danger" style="font-size:12px;padding:5px 14px">OVERDUE</span>
                @else
                    <span class="badge badge-gray" style="font-size:12px;padding:5px 14px">PENDING</span>
                @endif
            </div>

            <div class="card-body">

                {{-- Student + Time info --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Student</div>
                        <div style="font-size:17px;font-weight:800;color:var(--text-heading)">{{ $payment->student?->full_name ?? '—' }}</div>
                        <div class="mono" style="font-size:12px;color:var(--primary);margin:2px 0">{{ $payment->student?->student_id ?? '—' }}</div>
                        <div style="font-size:12px;color:var(--text-muted)">
                            {{ $payment->student?->subject ?? '—' }} · Grade {{ $payment->student?->year_level ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Schedule</div>
                        <div style="font-size:15px;font-weight:700;color:var(--text-primary)">{{ $payment->time_type ?? '—' }}</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                            Payment day: {{ $payment->student?->monthly_payment_day ?? '—' }} of each month
                        </div>
                    </div>
                </div>

                {{-- Amount breakdown --}}
                <div class="amount-box">
                    @if($payment->admin_fee > 0)
                    <div class="amount-cols" style="grid-template-columns:repeat(4,1fr)">
                        <div class="amount-col">
                            <div class="amount-col-label">Monthly Fee</div>
                            <div class="amount-col-val" style="color:var(--text-heading)">${{ number_format($payment->amount_due,2) }}</div>
                        </div>
                        <div class="amount-col">
                            <div class="amount-col-label">Admin Fee</div>
                            <div class="amount-col-val" style="color:var(--warning)">${{ number_format($payment->admin_fee,2) }}</div>
                        </div>
                        <div class="amount-col">
                            <div class="amount-col-label">Total Paid</div>
                            <div class="amount-col-val" style="color:var(--success)">${{ number_format($payment->amount_paid,2) }}</div>
                        </div>
                        <div class="amount-col">
                            <div class="amount-col-label">Balance</div>
                            <div class="amount-col-val" style="color:{{ $payment->balance > 0 ? 'var(--danger)' : 'var(--success)' }}">${{ number_format($payment->balance,2) }}</div>
                        </div>
                    </div>
                    @else
                    <div class="amount-cols" style="grid-template-columns:repeat(3,1fr)">
                        <div class="amount-col">
                            <div class="amount-col-label">Monthly Fee</div>
                            <div class="amount-col-val" style="color:var(--text-heading)">${{ number_format($payment->amount_due,2) }}</div>
                        </div>
                        <div class="amount-col">
                            <div class="amount-col-label">Total Paid</div>
                            <div class="amount-col-val" style="color:var(--success)">${{ number_format($payment->amount_paid,2) }}</div>
                        </div>
                        <div class="amount-col">
                            <div class="amount-col-label">Balance</div>
                            <div class="amount-col-val" style="color:{{ $payment->balance > 0 ? 'var(--danger)' : 'var(--success)' }}">${{ number_format($payment->balance,2) }}</div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Date details grid --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
                    <div class="info-box" style="border-left:3px solid var(--primary)">
                        <div class="info-box-label">Covers Month</div>
                        <div class="info-box-val" style="color:var(--primary);font-size:16px">
                            {{ $payment->due_date?->format('F Y') ?? $payment->payment_date?->format('F Y') ?? '—' }}
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                            Due: {{ $payment->due_date?->format('d M Y') ?? '—' }}
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Actual Paid Date</div>
                        <div class="info-box-val">{{ $payment->payment_date?->format('d M Y') ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Date student physically paid</div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Next Payment Date</div>
                        <div class="info-box-val" style="color:{{ $daysLeft !== null && $daysLeft < 0 ? 'var(--danger)' : 'var(--primary)' }}">
                            {{ $nextDate?->format('d M Y') ?? '—' }}
                            @if($daysLeft !== null)
                                <span style="font-size:11px;font-weight:600;margin-left:6px;
                                    color:{{ $daysLeft < 0 ? 'var(--danger)' : ($daysLeft <= 7 ? 'var(--warning)' : 'var(--success)') }}">
                                    ({{ $daysLeft < 0 ? abs($daysLeft).'d overdue' : $daysLeft.'d left' }})
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Payment Method</div>
                        <div class="info-box-val">
                            @if($payment->payment_method === 'cash')
                                💵 Cash
                            @elseif($payment->payment_method === 'bank_transfer')
                                🏦 Bank Transfer
                            @else
                                {{ $payment->payment_method ? ucfirst(str_replace('_',' ',$payment->payment_method)) : '—' }}
                            @endif
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Processed By</div>
                        <div class="info-box-val">{{ $payment->creator?->name ?? 'System' }}</div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Recorded On</div>
                        <div class="info-box-val" style="font-size:13px">{{ $payment->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                </div>

                {{-- Payment photo --}}
                @if($payment->photo)
                <div class="info-box" style="margin-bottom:14px">
                    <div class="info-box-label" style="margin-bottom:8px">Payment Photo</div>
                    <img src="{{ asset('storage/'.$payment->photo) }}" alt="Payment receipt photo"
                         style="max-width:100%;max-height:300px;object-fit:contain;border-radius:8px;border:1px solid var(--border)">
                </div>
                @endif

                {{-- Notes --}}
                @if($payment->notes)
                <div style="padding:12px;background:var(--bg-muted);border-radius:var(--radius-sm);border-left:3px solid var(--primary)">
                    <div class="info-box-label">Notes</div>
                    <div style="font-size:14px;color:var(--text-secondary);margin-top:4px">{{ $payment->notes }}</div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ── RIGHT: Sidebar ──────────────────────────── --}}
    <div>

        {{-- Next payment countdown --}}
        <div class="next-payment-card {{ $countdownClass }}">
            <i class="{{ $countdownIcon }}" style="font-size:28px;color:{{ $countdownColor }}" aria-hidden="true"></i>
            <div class="countdown-label" style="color:{{ $countdownColor }};margin-top:8px">{{ $countdownTitle }}</div>
            @if($daysLeft !== null)
                <div class="countdown-number" style="color:{{ $countdownColor }}">
                    {{ abs($daysLeft) }}<span style="font-size:20px">d</span>
                </div>
                <div class="countdown-date" style="color:{{ $countdownColor }}">
                    {{ $daysLeft < 0 ? 'was due' : 'due' }} {{ $nextDate?->format('d M Y') }}
                </div>
            @elseif($nextDate)
                <div style="font-size:16px;font-weight:700;color:{{ $countdownColor }};margin-top:6px">
                    {{ $nextDate->format('d M Y') }}
                </div>
            @else
                <div style="font-size:14px;color:{{ $countdownColor }};margin-top:6px">No date set</div>
            @endif

            @if($payment->status === 'paid')
                <div style="font-size:12px;margin-top:10px;color:{{ $countdownColor }};opacity:0.85">
                    Next amount: <strong>${{ number_format($payment->student?->monthly_fee ?? 0, 2) }}</strong>
                </div>
                @if($stillOverdue && $nextMonthLabel)
                    {{-- Still overdue months — show prominent CTA --}}
                    <div style="margin-top:6px;font-size:11px;font-weight:700;color:var(--danger);background:rgba(239,68,68,0.15);padding:4px 10px;border-radius:20px;display:inline-block">
                        {{ $overdueCount }} month{{ $overdueCount > 1 ? 's' : '' }} still overdue
                    </div>
                    <a href="{{ route('payments.create', ['student_id' => $payment->student_id]) }}"
                       class="btn btn-danger" style="margin-top:12px;width:100%;justify-content:center;font-weight:700">
                        <i class="fas fa-plus" aria-hidden="true"></i> Pay {{ $nextMonthLabel }}
                    </a>
                @else
                    <a href="{{ route('payments.create', ['student_id' => $payment->student_id]) }}"
                       class="btn btn-primary" style="margin-top:12px;width:100%;justify-content:center">
                        <i class="fas fa-plus" aria-hidden="true"></i> Record Next Payment
                    </a>
                @endif
            @endif
        </div>

        {{-- Student mini card --}}
        @if($payment->student)
        <a href="{{ route('students.show', $payment->student) }}" class="student-mini">
            <div class="student-mini-avatar" aria-hidden="true">
                {{ strtoupper(substr($payment->student->first_name,0,1).substr($payment->student->last_name,0,1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;color:var(--text-heading);font-size:14px">{{ $payment->student->full_name }}</div>
                <div class="mono" style="font-size:11px;color:var(--primary)">{{ $payment->student->student_id }}</div>
                <div style="font-size:11px;color:var(--text-muted)">Grade {{ $payment->student->year_level }} · {{ $payment->student->subject ?? '—' }}</div>
            </div>
            <i class="fas fa-chevron-right" style="color:var(--text-muted);font-size:12px" aria-hidden="true"></i>
        </a>
        @endif

        {{-- Quick actions --}}
        <div class="card">
            <div class="card-body" style="padding:14px">
                <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px">
                    Actions
                </div>
                <div style="display:flex;flex-direction:column;gap:7px">
                    <a href="{{ route('payments.receipt', $payment) }}" target="_blank" rel="noopener"
                       class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-print" style="color:var(--primary)" aria-hidden="true"></i> Print Receipt
                    </a>
                    <a href="{{ route('payments.receipt.download', $payment) }}"
                       class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-download" style="color:var(--success)" aria-hidden="true"></i> Download PDF
                    </a>
                    <a href="{{ route('payments.edit', $payment) }}"
                       class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-edit" style="color:var(--warning)" aria-hidden="true"></i> Edit Payment
                    </a>
                    @if($payment->student)
                    <a href="{{ route('payments.create', ['student_id' => $payment->student_id]) }}"
                       class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-plus-circle" style="color:var(--primary)" aria-hidden="true"></i> New Payment
                    </a>
                    @endif
                    <hr style="border:none;border-top:1px solid var(--border);margin:2px 0">
                    <button type="button" class="btn btn-outline delete-btn"
                            style="justify-content:flex-start;color:var(--danger)"
                            data-action="{{ route('payments.destroy', $payment) }}"
                            data-title="Delete Payment"
                            data-body="Delete receipt {{ e($payment->receipt_number) }}? This cannot be undone.">
                        <i class="fas fa-trash" aria-hidden="true"></i> Delete
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
