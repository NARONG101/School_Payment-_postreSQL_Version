@extends('layouts.app')
@section('title','Students - Grade ' . $grade . ' - ' . $date->format('F Y'))
@section('page-title','Students - Grade ' . $grade . ' - ' . $date->format('F Y'))
@section('topbar-actions')
    <a href="{{ route('history.month', $yearMonth) }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Grades</a>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-user-graduate"></i> Students with Payments ({{ $students->count() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Come From</th>
                    <th>Subject</th>
                    <th>Monthly Payment Day</th>
                    <th>Paid Date</th>
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
                            <div style="width:40px;height:40px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);font-size:14px;flex-shrink:0">
                                {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600">{{ $student->full_name }} ({{ ucfirst($student->gender ?? 'n/a') }})</div>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-family:'JetBrains Mono',monospace;font-size:12px">{{ $student->student_id }}</span></td>
                    <td>{{ $student->come_from ?? '-' }}</td>
                    <td>{{ $student->subject ?? '-' }}</td>
                    <td>{{ $student->monthly_payment_day ?? '-' }}</td>
                    <td>{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</td>
                    <td style="font-weight:700;color:var(--success)">${{ number_format($payment->amount_paid,2) }}</td>
                    <td>{{ $payment->next_payment_date?->format('M d, Y') ?? '-' }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-icon btn-outline" title="View Payment">
                                <i class="fas fa-eye" style="font-size:12px"></i>
                            </a>
                            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-icon btn-outline" title="Print Receipt">
                                <i class="fas fa-file-pdf" style="font-size:12px"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr><td colspan="9">
                    <div class="empty-state">
                        <i class="fas fa-user-times"></i>
                        <p>No students with payments in this month and grade</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
