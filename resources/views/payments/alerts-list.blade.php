@extends('layouts.app')
@section('title', $title)
@section('page-title', $title)

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            @if($type === 'overdue')
                <i class="fas fa-exclamation-circle" style="color:var(--danger)"></i>
            @elseif($type === 'closely')
                <i class="fas fa-bell" style="color:var(--warning)"></i>
            @else
                <i class="fas fa-clock" style="color:var(--primary)"></i>
            @endif
            {{ $title }}
        </div>
        @if(str_contains($title, 'Grade'))
            @php
                $backRoute = $type === 'overdue' ? route('payments.alerts.overdue') : 
                            ($type === 'closely' ? route('payments.alerts.closely') : 
                            route('payments.alerts.upcoming'));
            @endphp
            <a href="{{ $backRoute }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Grades
            </a>
        @else
            <a href="{{ route('payments.alerts') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Alerts
            </a>
        @endif
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
                @forelse($payments as $payment)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $payment->student?->full_name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--gray-400)">{{ $payment->student?->student_id ?? '—' }}</div>
                    </td>
                    <td>Grade {{ $payment->student?->year_level ?? '—' }}</td>
                    <td>{{ $payment->time_type ?? '—' }}</td>
                    <td style="font-weight:700">${{ number_format($payment->balance, 2) }}</td>
                    <td>{{ $payment->deadline_date?->format('M d, Y') ?? '—' }}</td>
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
                <tr><td colspan="7" class="empty-state">No payments found!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection