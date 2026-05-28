@extends('layouts.app')
@section('title', 'Payment ' . $payment->receipt_number)
@section('page-title', 'Payment Details')
@section('topbar-actions')
    <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-primary btn-sm" target="_blank">
        <i class="fas fa-eye"></i> View Receipt
    </a>
    <a href="{{ route('payments.receipt.download', $payment) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-download"></i> Download PDF
    </a>
    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-edit"></i> Edit
    </a>
@endsection
@section('content')
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;max-width:1000px">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Receipt: <span style="font-family:'JetBrains Mono',monospace;color:var(--primary)">{{ $payment->receipt_number }}</span></div>
                <div style="font-size:12px;color:var(--gray-400);margin-top:3px">Created: {{ $payment->created_at->format('M d, Y h:i A') }}</div>
            </div>
            @if($payment->status==='paid') <span class="badge badge-success" style="font-size:13px;padding:6px 16px">PAID</span>
            @elseif($payment->status==='partial') <span class="badge badge-warning" style="font-size:13px;padding:6px 16px">PARTIAL</span>
            @elseif($payment->status==='overdue') <span class="badge badge-danger" style="font-size:13px;padding:6px 16px">OVERDUE</span>
            @else <span class="badge badge-gray" style="font-size:13px;padding:6px 16px">PENDING</span>
            @endif
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
                <div>
                    <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Student</div>
                    <div style="font-size:16px;font-weight:700">{{ $payment->student?->full_name ?? '—' }}</div>
                    <div style="font-size:12px;color:var(--primary);font-family:'JetBrains Mono',monospace">{{ $payment->student?->student_id ?? '—' }}</div>
                    <div style="font-size:12px;color:var(--gray-500)">{{ $payment->student?->subject ?? '—' }} — Grade {{ $payment->student?->year_level ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Time Type</div>
                    <div style="font-size:15px;font-weight:700">{{ $payment->time_type ?? '—' }}</div>
                    <div style="font-size:12px;color:var(--gray-500)">{{ $payment->paymentType?->name ?? '—' }}</div>
                </div>
            </div>

            <div style="background:var(--gray-50);border-radius:10px;padding:20px;margin-bottom:20px">
                @if($payment->admin_fee > 0)
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:20px">
                    <div style="text-align:center">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">MONTHLY FEE</div>
                        <div style="font-size:20px;font-weight:800;color:var(--gray-900)">${{ number_format($payment->amount_due,2) }}</div>
                    </div>
                    <div style="text-align:center;border-left:1px solid var(--gray-200)">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">ADMIN FEE</div>
                        <div style="font-size:20px;font-weight:800;color:var(--warning)">${{ number_format($payment->admin_fee ?? 0,2) }}</div>
                    </div>
                    <div style="text-align:center;border-left:1px solid var(--gray-200)">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">TOTAL PAID</div>
                        <div style="font-size:20px;font-weight:800;color:var(--success)">${{ number_format($payment->amount_paid,2) }}</div>
                    </div>
                    <div style="text-align:center;border-left:1px solid var(--gray-200)">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">BALANCE</div>
                        <div data-balance-color="{{ $payment->balance > 0 ? 'var(--danger)' : 'var(--success)' }}" style="font-size:20px;font-weight:800">${{ number_format($payment->balance, 2) }}</div>
                    </div>
                </div>
                @else
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">
                    <div style="text-align:center">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">MONTHLY FEE</div>
                        <div style="font-size:20px;font-weight:800;color:var(--gray-900)">${{ number_format($payment->amount_due,2) }}</div>
                    </div>
                    <div style="text-align:center;border-left:1px solid var(--gray-200)">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">TOTAL PAID</div>
                        <div style="font-size:20px;font-weight:800;color:var(--success)">${{ number_format($payment->amount_paid,2) }}</div>
                    </div>
                    <div style="text-align:center;border-left:1px solid var(--gray-200)">
                        <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">BALANCE</div>
                        <div data-balance-color="{{ $payment->balance > 0 ? 'var(--danger)' : 'var(--success)' }}" style="font-size:20px;font-weight:800">${{ number_format($payment->balance, 2) }}</div>
                    </div>
                </div>
                @endif
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                @foreach([
                    ['label'=>'Due Date','val'=>$payment->due_date->format('M d, Y')],
                    ['label'=>'Deadline Date','val'=>$payment->deadline_date->format('M d, Y')],
                    ['label'=>'Payment Date','val'=>$payment->payment_date?->format('M d, Y') ?? '—'],
                    ['label'=>'Payment Method','val'=>$payment->payment_method ? ucfirst(str_replace('_',' ',$payment->payment_method)) : '—'],
                    ['label'=>'Next Payment Date','val'=>$payment->next_payment_date?->format('M d, Y') ?? '—'],
                    ['label'=>'Processed By','val'=>$payment->creator?->name ?? 'System'],
                ] as $item)
                <div style="padding:12px;background:var(--gray-50);border-radius:8px">
                    <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;margin-bottom:3px">{{ $item['label'] }}</div>
                    <div style="font-size:14px;font-weight:600;color:var(--gray-800)">{{ $item['val'] }}</div>
                </div>
                @endforeach
            </div>

            @if($payment->photo)
            <div style="margin-top:16px;padding:12px;background:var(--gray-50);border-radius:8px">
                <div style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;margin-bottom:8px">Payment Photo</div>
                <img src="{{ asset('storage/' . $payment->photo) }}" alt="Payment Photo" style="max-width:100%;border-radius:8px">
            </div>
            @endif

            @if($payment->notes)
            <div style="margin-top:16px;padding:12px;background:var(--gray-50);border-radius:8px;border-left:3px solid var(--primary)">
                <div style="font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px">NOTES</div>
                <div style="font-size:14px;color:var(--gray-700)">{{ $payment->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Alert Panel -->
    <div>
        @if($payment->status !== 'paid')
        @php
            if ($payment->status === 'overdue') {
                $borderColor = 'var(--danger)';
            } elseif ($payment->deadline_alert_level === 'critical') {
                $borderColor = '#fbbf24';
            } else {
                $borderColor = 'var(--gray-200)';
            }
        @endphp
        <div class="card" data-border-color="{{ $borderColor }}" style="margin-bottom:16px">
            <div class="card-body" style="text-align:center;padding:20px">
                @if($payment->status === 'overdue')
                    <i class="fas fa-exclamation-circle" style="font-size:36px;color:var(--danger)"></i>
                    <div style="font-size:16px;font-weight:800;color:var(--danger);margin:10px 0 4px">Payment Overdue!</div>
                    <div style="font-size:13px;color:var(--gray-500)">{{ abs($payment->days_until_deadline) }} days past deadline</div>
                @elseif($payment->deadline_alert_level === 'critical')
                    <i class="fas fa-bell" style="font-size:36px;color:var(--warning)"></i>
                    <div style="font-size:16px;font-weight:800;color:var(--warning);margin:10px 0 4px">Deadline Soon!</div>
                    <div style="font-size:13px;color:var(--gray-500)">{{ $payment->days_until_deadline }} days remaining</div>
                @else
                    <i class="fas fa-clock" style="font-size:36px;color:var(--primary)"></i>
                    <div style="font-size:16px;font-weight:800;color:var(--primary);margin:10px 0 4px">Payment Pending</div>
                    <div style="font-size:13px;color:var(--gray-500)">{{ $payment->days_until_deadline }} days until deadline</div>
                @endif
                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-primary" style="margin-top:14px;width:100%">
                    <i class="fas fa-money-bill-wave"></i> Record Payment
                </a>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body" style="padding:16px">
                <div style="font-size:12px;font-weight:700;color:var(--gray-600);margin-bottom:12px">QUICK ACTIONS</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-eye" style="color:var(--primary)"></i> View Receipt
                    </a>
                    <a href="{{ route('payments.receipt.download', $payment) }}" class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-download" style="color:var(--success)"></i> Download PDF
                    </a>
                    <a href="{{ route('students.show', $payment->student) }}" class="btn btn-outline" style="justify-content:flex-start">
                        <i class="fas fa-user-graduate" style="color:var(--gray-500)"></i> View Student
                    </a>
                    <button type="button" class="btn btn-outline delete-btn" style="width:100%;justify-content:flex-start;color:var(--danger)"
                            data-action="{{ route('payments.destroy', $payment) }}"
                            data-title="Delete Payment"
                            data-body="Are you sure you want to delete payment {{ $payment->receipt_number }}? This action cannot be undone.">
                        <i class="fas fa-trash"></i> Delete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-balance-color]').forEach(function(el) {
        el.style.color = el.dataset.balanceColor;
    });
    document.querySelectorAll('[data-border-color]').forEach(function(el) {
        el.style.borderColor = el.dataset.borderColor;
    });
});
</script>
@endsection