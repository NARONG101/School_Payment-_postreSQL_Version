{{-- This view is kept for compatibility but alerts-grades.blade.php is the primary view --}}
@extends('layouts.app')
@section('title', $title)
@section('page-title', $title)
@section('topbar-actions')
    <a href="{{ route('payments.alerts') }}" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> <span>Back to Alerts</span>
    </a>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            @if($type === 'overdue')
                <i class="fas fa-exclamation-circle" style="color:var(--danger)" aria-hidden="true"></i>
            @elseif($type === 'closely')
                <i class="fas fa-bell" style="color:var(--warning)" aria-hidden="true"></i>
            @else
                <i class="fas fa-clock" style="color:var(--primary)" aria-hidden="true"></i>
            @endif
            {{ $title }}
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Time Slot</th>
                    <th>Balance Due</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments ?? [] as $payment)
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $payment->student?->full_name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $payment->student?->student_id ?? '—' }}</div>
                    </td>
                    <td style="color:var(--text-secondary)">Grade {{ $payment->student?->year_level ?? '—' }}</td>
                    <td style="color:var(--text-secondary)">{{ $payment->time_type ?? '—' }}</td>
                    <td style="font-weight:700;color:var(--text-primary)">${{ number_format($payment->balance, 2) }}</td>
                    <td style="font-size:12px;color:var(--text-muted)">{{ $payment->deadline_date?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        @if($payment->status === 'overdue')
                            <span class="badge badge-danger">Overdue</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('payments.show', $payment) }}" class="btn btn-primary btn-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-check-circle" style="color:var(--success)" aria-hidden="true"></i><p>No payments found!</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
