@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> New Payment
    </a>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff">
            <i class="fas fa-user-graduate" style="color:#1a56db"></i>
        </div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_students']) }}</div>
            <div class="stat-label">Active Students</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4">
            <i class="fas fa-money-bill-wave" style="color:#0e9f6e"></i>
        </div>
        <div>
            <div class="stat-value">${{ number_format($stats['total_collected'], 2) }}</div>
            <div class="stat-label">Collected This Month</div>
        </div>
    </div>
    <a href="{{ route('payments.alerts.overdue') }}" style="text-decoration:none">
    <div class="stat-card" style="cursor:pointer">
        <div class="stat-icon" style="background:#fff1f2">
            <i class="fas fa-exclamation-triangle" style="color:#e02424"></i>
        </div>
        <div>
            <div class="stat-value">{{ $stats['overdue_count'] }}</div>
            <div class="stat-label">Overdue Payments</div>
        </div>
    </div>
    </a>
    <a href="{{ route('payments.alerts.closely') }}" style="text-decoration:none">
    <div class="stat-card" style="cursor:pointer">
        <div class="stat-icon" style="background:#fefce8">
            <i class="fas fa-bell" style="color:#d97706"></i>
        </div>
        <div>
            <div class="stat-value">{{ $stats['due_this_week'] }}</div>
            <div class="stat-label">Closely Date</div>
        </div>
    </div>
    </a>
    <a href="{{ route('payments.alerts.upcoming') }}" style="text-decoration:none">
    <div class="stat-card" style="cursor:pointer">
        <div class="stat-icon" style="background:#f5f3ff">
            <i class="fas fa-calendar-check" style="color:#7c3aed"></i>
        </div>
        <div>
            <div class="stat-value">{{ $stats['upcoming_count'] }}</div>
            <div class="stat-label">Upcoming</div>
        </div>
    </div>
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:20px">
    <style>
        @media (min-width: 1024px) {
            .dashboard-grid { grid-template-columns:1fr 380px !important; }
        }
    </style>
</div>
<div class="dashboard-grid" style="display:grid;grid-template-columns:1fr;gap:20px">
    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Payments</div>
            <a href="{{ route('payments.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr>
                        <td>
                            <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--primary)">
                                {{ $payment->receipt_number }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600">{{ $payment->student?->full_name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--gray-400)">{{ $payment->student?->student_id ?? '—' }}</div>
                        </td>
                        <td>{{ $payment->paymentType?->name ?? '—' }}</td>
                        <td style="font-weight:700">${{ number_format($payment->amount_paid, 2) }}</td>
                        <td style="font-size:12px">{{ $payment->payment_date?->format('M d, Y') }}</td>
                        <td><span class="badge badge-success">Paid</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="empty-state">No recent payments</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Deadline Alerts -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-bell" style="color:var(--warning)"></i> Alerts</div>
            <a href="{{ route('payments.alerts') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div style="padding:12px;display:flex;flex-direction:column;gap:8px">
            @forelse($alertData as $data)
            <a href="{{ route('students.show', $data['student']) }}" style="text-decoration:none">
            <div style="padding:12px;border-radius:8px;border:1px solid var(--gray-200);background:white;transition:box-shadow 0.15s"
                 onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'"
                 onmouseout="this.style.boxShadow='none'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px">
                    <div style="font-weight:600;font-size:13px;color:var(--gray-800)">{{ $data['student']->full_name ?? '—' }}</div>
                    @if($data['alertLevel'] === 'overdue')
                        <span class="deadline-pill deadline-overdue">Overdue</span>
                    @elseif($data['alertLevel'] === 'critical')
                        <span class="deadline-pill deadline-critical">{{ $data['daysUntilNextPayment'] }}d left</span>
                    @else
                        <span class="deadline-pill deadline-warning">{{ $data['daysUntilNextPayment'] }}d left</span>
                    @endif
                </div>
                <div style="font-size:11px;color:var(--gray-400)">{{ $data['student']->student_id ?? '—' }}</div>
                <div style="font-size:13px;font-weight:700;color:var(--primary);margin-top:4px">Next: {{ $data['nextPaymentDate']->format('M d, Y') }}</div>
            </div>
            </a>
            @empty
            <div class="empty-state" style="padding:24px">
                <i class="fas fa-check-circle" style="color:var(--success);font-size:28px"></i>
                <p style="margin-top:8px">No urgent alerts!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Monthly Chart -->
<div class="card" style="margin-top:20px">
    <div class="card-header">
        <div class="card-title">Monthly Revenue (Last 6 Months)</div>
    </div>
    <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:12px;height:160px">
            @php $maxVal = max(array_column($monthlyData, 'amount')) ?: 1; @endphp
            @foreach($monthlyData as $data)
            @php
                $height = max(4, ($data['amount'] / $maxVal) * 100);
                $opacity = $loop->last ? 1 : 0.5;
            @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%">
                <div style="flex:1;display:flex;align-items:flex-end;width:100%">
                    <div data-height="{{ $height }}" data-opacity="{{ $opacity }}" style="width:100%;background:var(--primary);border-radius:6px 6px 0 0;transition:opacity 0.2s" title="{{ $data['month'] }}: ${{ number_format($data['amount'], 2) }}">
                    </div>
                </div>
                <div style="font-size:10px;color:var(--gray-400);white-space:nowrap">{{ $data['month'] }}</div>
                <div style="font-size:11px;font-weight:700;color:var(--gray-700)">${{ number_format($data['amount']/1000, 1) }}k</div>
            </div>
            @endforeach
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[data-height]').forEach(function(el) {
                    el.style.height = el.dataset.height + '%';
                    el.style.opacity = el.dataset.opacity;
                });
            });
        </script>
    </div>
</div>
@endsection