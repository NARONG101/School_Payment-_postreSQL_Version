@extends('layouts.app')
@section('title', $student->full_name)
@section('page-title', $student->full_name)
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm" id="back-btn" data-url="{{ route('students.index') }}">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const backBtn = document.getElementById('back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            if (history.length > 1) {
                history.back();
            } else {
                window.location = backBtn.dataset.url;
            }
        });
    }
});
</script>
@endsection
@section('topbar-actions')
    <a href="{{ route('payments.create', ['student_id'=>$student->id]) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>Add Payment</span>
    </a>
    <a href="{{ route('students.edit', $student) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-edit" aria-hidden="true"></i> <span>Edit</span>
    </a>
    <button type="button" class="btn btn-outline btn-sm delete-btn" style="color:var(--danger);border-color:var(--danger)"
            data-action="{{ route('students.destroy', $student) }}"
            data-title="Delete Student"
            data-body="Delete {{ e($student->full_name) }}? All associated payments will also be deleted.">
        <i class="fas fa-trash" aria-hidden="true"></i> <span>Delete</span>
    </button>
@endsection
@section('styles')
@php
    $nextPay = $student->next_payment_date;
    $daysLeft = $student->days_until_next_payment;
    $statusColor = $daysLeft < 0 ? 'var(--danger)' : ($daysLeft <= 7 ? 'var(--warning)' : 'var(--success)');
@endphp
<style>
.student-avatar {
    width:72px; height:72px; background:var(--primary-light);
    border-radius:50%; display:flex; align-items:center;
    justify-content:center; font-weight:800; color:var(--primary);
    font-size:26px; margin:0 auto 14px;
}
.mini-stat {
    padding:14px; text-align:center;
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius);
}
.next-payment-icon {
    width:16px;
    margin-top:2px;
    font-size:13px;
    flex-shrink:0;
    color: {!! $statusColor !!};
}
.next-payment-text {
    color: {!! $statusColor !!};
}
</style>
@endsection
@section('content')
<div class="detail-grid">
    {{-- Student Info --}}
    <div>
        <div class="card" style="margin-bottom:14px">
            <div class="card-body" style="text-align:center;padding:24px 20px">
                <div style="font-size:18px;font-weight:800;color:var(--text-heading)">{{ $student->full_name }}</div>
                <div class="mono" style="font-size:13px;color:var(--primary);margin:4px 0">{{ $student->student_id }}</div>
                @if($student->status === 'active')
                    <span class="badge badge-success" style="margin-top:6px">Active</span>
                @elseif($student->status === 'graduated')
                    <span class="badge badge-primary" style="margin-top:6px">Graduated</span>
                @else
                    <span class="badge badge-gray" style="margin-top:6px">{{ ucfirst($student->status ?? 'unknown') }}</span>
                @endif
            </div>
            <div style="padding:0 20px 20px">
                @foreach([
                    ['icon'=>'fas fa-school',      'label'=>'Come From', 'val'=>$student->come_from ?: 'N/A'],
                    ['icon'=>'fas fa-book',         'label'=>'Subject',   'val'=>$student->subject ?: 'N/A'],
                    ['icon'=>'fas fa-layer-group',  'label'=>'Grade',     'val'=>'Grade '.$student->year_level],
                    ['icon'=>'fas fa-phone',        'label'=>'Phone',     'val'=>$student->phone ?: 'N/A'],
                    ['icon'=>'fas fa-calendar',     'label'=>'Enrolled',  'val'=>$student->enrollment_date->format('M d, Y')],
                    ['icon'=>'fas fa-clock',        'label'=>'Time Slot', 'val'=>$student->time_type ?: 'N/A'],
                    ['icon'=>'fas fa-dollar-sign',  'label'=>'Monthly Fee', 'val'=>'$'.number_format($student->monthly_fee, 2)],
                    ['icon'=>'fas fa-percent',      'label'=>'Discount',  'val'=>($student->discount ?? 0).'%'],
                ] as $info)
                <div class="info-row">
                    <i class="{{ $info['icon'] }}" style="color:var(--text-muted);width:16px;margin-top:2px;font-size:13px;flex-shrink:0" aria-hidden="true"></i>
                    <div>
                        <div class="info-label-sm">{{ $info['label'] }}</div>
                        <div class="info-val-sm">{{ $info['val'] }}</div>
                    </div>
                </div>
                @endforeach

                {{-- Next payment date with countdown --}}
                @php
                    $nextPay = $student->next_payment_date;
                    $daysLeft = $student->days_until_next_payment;
                @endphp
                @if($nextPay)
                <div class="info-row" style="border-bottom:none">
                    <i class="fas fa-calendar-check next-payment-icon" aria-hidden="true"></i>
                    <div>
                        <div class="info-label-sm">Next Payment</div>
                        <div class="info-val-sm next-payment-text">
                            {{ $nextPay->format('d M Y') }}
                            <span style="font-size:11px;font-weight:600;margin-left:4px">
                                ({{ $daysLeft < 0 ? abs($daysLeft).'d overdue' : $daysLeft.'d left' }})
                            </span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="mini-stat">
                <div style="font-size:18px;font-weight:800;color:var(--success)">${{ number_format($stats['total_paid'],2) }}</div>
                <div style="font-size:11px;color:var(--text-muted)">Total Paid</div>
            </div>
            <div class="mini-stat">
                <div style="font-size:18px;font-weight:800;color:var(--danger)">${{ number_format($stats['total_due'],2) }}</div>
                <div style="font-size:11px;color:var(--text-muted)">Balance Due</div>
            </div>
            <div class="mini-stat">
                <div style="font-size:18px;font-weight:800;color:var(--primary)">{{ $stats['paid_count'] }}</div>
                <div style="font-size:11px;color:var(--text-muted)">Paid</div>
            </div>
            <div class="mini-stat">
                <div style="font-size:18px;font-weight:800;color:var(--warning)">{{ $stats['overdue_count'] }}</div>
                <div style="font-size:11px;color:var(--text-muted)">Overdue</div>
            </div>
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Payment History</div>
            <a href="{{ route('payments.create', ['student_id'=>$student->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus" aria-hidden="true"></i> Add
            </a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Amount Due</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>For Month</th>
                        <th>Next Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr class="{{ $payment->status==='overdue'?'alert-level-overdue':($payment->deadline_alert_level==='critical'?'alert-level-critical':'') }}">
                        <td><span class="mono" style="font-size:12px;color:var(--primary)">{{ $payment->receipt_number }}</span></td>
                        <td style="color:var(--text-primary)">${{ number_format($payment->amount_due,2) }}</td>
                        <td style="color:var(--success);font-weight:600">${{ number_format($payment->amount_paid,2) }}</td>
                        <td class="{{ $payment->balance > 0 ? 'payment-balance-positive' : 'payment-balance-zero' }}" style="font-weight:600">${{ number_format($payment->balance,2) }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">
                            {{ $payment->due_date?->format('M d, Y') ?? $payment->payment_date?->format('M d, Y') ?? '—' }}
                            @if($payment->payment_date && $payment->due_date && $payment->payment_date->format('Y-m-d') !== $payment->due_date->format('Y-m-d'))
                                <div style="font-size:10px;color:var(--text-muted)">paid {{ $payment->payment_date->format('M d, Y') }}</div>
                            @endif
                        </td>
                        <td style="font-size:12px;color:var(--text-muted)">
                            @if($payment->next_payment_date)
                                {{ $payment->next_payment_date->format('M d, Y') }}
                            @else
                                <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->status==='paid') <span class="badge badge-success">Paid</span>
                            @elseif($payment->status==='partial') <span class="badge badge-warning">Partial</span>
                            @elseif($payment->status==='overdue') <span class="badge badge-danger">Overdue</span>
                            @else <span class="badge badge-gray">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-icon btn-outline" title="View"><i class="fas fa-eye" style="font-size:11px" aria-hidden="true"></i></a>
                                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-icon btn-outline" title="Receipt" style="color:var(--primary)" target="_blank" rel="noopener"><i class="fas fa-file-pdf" style="font-size:11px" aria-hidden="true"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12"><div class="empty-state"><i class="fas fa-receipt" aria-hidden="true"></i><p>No payments yet</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:14px;border-top:1px solid var(--border)">
            {{ $payments->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
