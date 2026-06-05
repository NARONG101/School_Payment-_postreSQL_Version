@extends('layouts.app')
@section('title','Students — Grade '.$grade.' — '.$date->format('F Y'))
@section('page-title','Students — Grade '.$grade.' — '.$date->format('F Y'))
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm" onclick="history.length>1?history.back():window.location='{{ route('history.month', $yearMonth) }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-user-graduate" aria-hidden="true"></i>
            Students with Payments ({{ $students->count() }})
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Come From</th>
                    <th>Subject</th>
                    <th>Payment Day</th>
                    <th>For Month</th>
                    <th>Amount Paid</th>
                    <th>Next Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                @foreach($student->payments as $payment)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:38px;height:38px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);font-size:13px;flex-shrink:0" aria-hidden="true">
                                {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;color:var(--text-primary)">{{ $student->full_name }} ({{ ucfirst($student->gender ?? 'n/a') }})</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="mono" style="font-size:12px;color:var(--text-secondary)">{{ $student->student_id }}</span></td>
                    <td style="color:var(--text-secondary)">{{ $student->come_from ?? '—' }}</td>
                    <td style="color:var(--text-secondary)">{{ $student->subject ?? '—' }}</td>
                    <td style="color:var(--text-secondary)">{{ $student->monthly_payment_day ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-muted)">
                        {{ $payment->due_date?->format('M d, Y') ?? $payment->payment_date?->format('M d, Y') ?? '—' }}
                        @if($payment->payment_date && $payment->due_date && $payment->payment_date->format('Y-m-d') !== $payment->due_date->format('Y-m-d'))
                            <div style="font-size:10px;color:var(--text-muted)">paid {{ $payment->payment_date->format('M d, Y') }}</div>
                        @endif
                    </td>
                    <td style="font-weight:700;color:var(--success)">${{ number_format($payment->amount_paid,2) }}</td>
                    <td style="font-size:12px;color:var(--text-muted)">{{ $payment->next_payment_date?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-icon btn-outline" title="View payment">
                                <i class="fas fa-eye" style="font-size:12px" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-icon btn-outline" title="Print receipt" target="_blank" rel="noopener">
                                <i class="fas fa-file-pdf" style="font-size:12px" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr><td colspan="9">
                    <div class="empty-state">
                        <i class="fas fa-user-times" aria-hidden="true"></i>
                        <p>No students with payments in this month and grade</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
