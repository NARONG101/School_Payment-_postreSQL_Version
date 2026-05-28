@extends('layouts.app')
@section('title', $student->full_name)
@section('page-title', $student->full_name)
@section('topbar-actions')
    <a href="{{ route('payments.create', ['student_id'=>$student->id]) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Payment
    </a>
    <a href="{{ route('students.edit', $student) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-edit"></i> Edit
    </a>
    <button type="button" class="btn btn-outline btn-sm delete-btn" style="color:var(--danger);border-color:var(--danger)"
            data-action="{{ route('students.destroy', $student) }}"
            data-title="Delete Student"
            data-body="Are you sure you want to delete {{ $student->full_name }}? This action cannot be undone and all associated payments will also be deleted.">
        <i class="fas fa-trash"></i> Delete
    </button>
@endsection
@section('content')
<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start">
    <!-- Student Info Card -->
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-body" style="text-align:center;padding:28px 20px">
                <div style="width:72px;height:72px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--primary);font-size:26px;margin:0 auto 14px">
                    {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                </div>
                <div style="font-size:18px;font-weight:800;color:var(--gray-900)">{{ $student->full_name }}</div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--primary);margin:4px 0">{{ $student->student_id }}</div>
                @if($student->status === 'active')
                    <span class="badge badge-success" style="margin-top:6px">Active</span>
                @elseif($student->status === 'graduated')
                    <span class="badge badge-primary" style="margin-top:6px">Graduated</span>
                @else
                    <span class="badge badge-gray" style="margin-top:6px">{{ ucfirst($student->status) }}</span>
                @endif
            </div>
            <div style="padding:0 20px 20px">
                @foreach([
                    ['icon'=>'fas fa-school','label'=>'Come From','val'=>$student->come_from ?: 'N/A'],
                    ['icon'=>'fas fa-book','label'=>'Subject','val'=>$student->subject ?: 'N/A'],
                    ['icon'=>'fas fa-layer-group','label'=>'Grade','val'=>'Grade '.$student->year_level],
                    ['icon'=>'fas fa-phone','label'=>'Phone','val'=>$student->phone ?: 'N/A'],
                    ['icon'=>'fas fa-calendar','label'=>'Enrolled','val'=>$student->enrollment_date->format('M d, Y')],
                ] as $info)
                <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--gray-100)">
                    <i class="{{ $info['icon'] }}" style="color:var(--gray-400);width:16px;margin-top:2px;font-size:13px"></i>
                    <div>
                        <div style="font-size:10px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:0.5px">{{ $info['label'] }}</div>
                        <div style="font-size:13px;font-weight:600;color:var(--gray-800)">{{ $info['val'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="card" style="padding:16px;text-align:center">
                <div style="font-size:20px;font-weight:800;color:var(--success)">${{ number_format($stats['total_paid'],2) }}</div>
                <div style="font-size:11px;color:var(--gray-400)">Total Paid</div>
            </div>
            <div class="card" style="padding:16px;text-align:center">
                <div style="font-size:20px;font-weight:800;color:var(--danger)">${{ number_format($stats['total_due'],2) }}</div>
                <div style="font-size:11px;color:var(--gray-400)">Balance Due</div>
            </div>
            <div class="card" style="padding:16px;text-align:center">
                <div style="font-size:20px;font-weight:800;color:var(--primary)">{{ $stats['paid_count'] }}</div>
                <div style="font-size:11px;color:var(--gray-400)">Paid</div>
            </div>
            <div class="card" style="padding:16px;text-align:center">
                <div style="font-size:20px;font-weight:800;color:var(--warning)">{{ $stats['overdue_count'] }}</div>
                <div style="font-size:11px;color:var(--gray-400)">Overdue</div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Payment History</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Type</th>
                        <th>Amount Due</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Deadline</th>
                        <th>Paid Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr class="{{ $payment->status==='overdue'?'alert-level-overdue':($payment->deadline_alert_level==='critical'?'alert-level-critical':'') }}">
                        <td><span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--primary)">{{ $payment->receipt_number }}</span></td>
                        <td>{{ $payment->paymentType?->name ?? '—' }}</td>
                        <td>${{ number_format($payment->amount_due,2) }}</td>
                        <td style="color:var(--success);font-weight:600">${{ number_format($payment->amount_paid,2) }}</td>
                        <td @if($payment->balance > 0) style="color:var(--danger);font-weight:600" @endif>${{ number_format($payment->balance, 2) }}</td>
                        <td style="font-size:12px">
                            {{ $payment->deadline_date->format('M d, Y') }}
                            @if($payment->status !== 'paid')
                                <div class="deadline-pill deadline-{{ $payment->deadline_alert_level }}" style="margin-top:2px;display:inline-block">
                                    @if($payment->days_until_deadline < 0)
                                        {{ abs($payment->days_until_deadline) }}d ago
                                    @else
                                        {{ $payment->days_until_deadline }}d left
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td style="font-size:12px">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge badge-success">Paid</span>
                            @elseif($payment->status === 'partial')
                                <span class="badge badge-warning">Partial</span>
                            @elseif($payment->status === 'overdue')
                                <span class="badge badge-danger">Overdue</span>
                            @else
                                <span class="badge badge-gray">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-icon btn-outline" title="View"><i class="fas fa-eye" style="font-size:11px"></i></a>
                                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-icon btn-outline" title="Receipt" style="color:var(--primary)" target="_blank"><i class="fas fa-file-pdf" style="font-size:11px"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9"><div class="empty-state"><i class="fas fa-receipt"></i><p>No payments yet</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px;border-top:1px solid var(--gray-200)">
            {{ $payments->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection 