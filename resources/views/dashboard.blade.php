@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('topbar-actions')
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New Payment</span>
    </a>
@endsection
@section('styles')
<style>
.chart-bar { width:100%; background:var(--primary); border-radius:6px 6px 0 0; transition:height 0.4s ease, opacity 0.3s; }
.chart-bar:hover { opacity:1 !important; filter:brightness(1.1); }
.chart-label { font-size:10px; color:var(--text-muted); white-space:nowrap; }
.chart-val   { font-size:11px; font-weight:700; color:var(--text-secondary); }
.alert-item  { padding:12px; border-radius:8px; border:1px solid var(--border); background:var(--bg-card); transition:box-shadow 0.15s; text-decoration:none; display:block; color:inherit; }
.alert-item:hover { box-shadow:var(--shadow-md); }
.stat-icon-blue   { background:var(--primary-50); }
.stat-icon-green  { background:var(--success-light); }
.stat-icon-red    { background:var(--danger-light); }
.stat-icon-yellow { background:var(--warning-light); }
.stat-icon-purple { background:rgba(124,58,237,0.1); }

/* ── Quick Actions ── */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    margin-top: 18px;
}
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: var(--text-primary);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.15s, box-shadow 0.15s, transform 0.1s, border-color 0.15s;
    cursor: pointer;
    text-align: center;
}
.quick-action-btn:hover {
    background: var(--primary-light);
    border-color: var(--primary);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
    color: var(--primary);
}
.quick-action-btn .qa-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.qa-blue   { background: var(--primary-50);          color: var(--primary); }
.qa-green  { background: var(--success-light);        color: var(--success); }
.qa-yellow { background: var(--warning-light);        color: var(--warning); }
.qa-red    { background: var(--danger-light);         color: var(--danger); }
.qa-purple { background: rgba(124,58,237,0.12);       color: #7c3aed; }
.qa-teal   { background: rgba(6,182,212,0.12);        color: #0891b2; }
</style>
@endsection
@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-blue"><i class="fas fa-user-graduate" style="color:var(--primary)"></i></div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_students']) }}</div>
            <div class="stat-label">Active Students</div>
        </div>
    </div>
    <a href="{{ route('revenue.index') }}" class="stat-card" style="text-decoration:none;color:inherit">
        <div class="stat-icon stat-icon-green"><i class="fas fa-money-bill-wave" style="color:var(--success)"></i></div>
        <div>
            <div class="stat-value">${{ number_format($stats['total_collected'], 2) }}</div>
            <div class="stat-label">Collected This Month</div>
        </div>
    </a>
    <a href="{{ route('payments.alerts.overdue') }}" class="stat-card" style="text-decoration:none;color:inherit">
        <div class="stat-icon stat-icon-red"><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i></div>
        <div>
            <div class="stat-value">{{ $stats['overdue_count'] }}</div>
            <div class="stat-label">Overdue Payments</div>
        </div>
    </a>
    <a href="{{ route('payments.alerts.closely') }}" class="stat-card" style="text-decoration:none;color:inherit">
        <div class="stat-icon stat-icon-yellow"><i class="fas fa-bell" style="color:var(--warning)"></i></div>
        <div>
            <div class="stat-value">{{ $stats['due_this_week'] }}</div>
            <div class="stat-label">Closely Date</div>
        </div>
    </a>
    <a href="{{ route('payments.alerts.upcoming') }}" class="stat-card" style="text-decoration:none;color:inherit">
        <div class="stat-icon stat-icon-purple"><i class="fas fa-calendar-check" style="color:#7c3aed"></i></div>
        <div>
            <div class="stat-value">{{ $stats['upcoming_count'] }}</div>
            <div class="stat-label">Upcoming</div>
        </div>
    </a>
</div>

{{-- ── Quick Actions ─────────────────────────────────────── --}}
<div class="card" style="margin-top:18px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-bolt" style="color:var(--warning)" aria-hidden="true"></i> Quick Actions</div>
    </div>
    <div class="card-body">
        <div class="quick-actions-grid">
            <a href="{{ route('students.create') }}" class="quick-action-btn">
                <div class="qa-icon qa-blue"><i class="fas fa-user-plus"></i></div>
                Enroll Student
            </a>
            <a href="{{ route('payments.create') }}" class="quick-action-btn">
                <div class="qa-icon qa-green"><i class="fas fa-plus-circle"></i></div>
                New Payment
            </a>
            <a href="{{ route('students.index') }}" class="quick-action-btn">
                <div class="qa-icon qa-teal"><i class="fas fa-user-graduate"></i></div>
                All Students
            </a>
            <a href="{{ route('payments.index') }}" class="quick-action-btn">
                <div class="qa-icon qa-purple"><i class="fas fa-receipt"></i></div>
                All Payments
            </a>
            <a href="{{ route('revenue.index') }}" class="quick-action-btn">
                <div class="qa-icon qa-green"><i class="fas fa-chart-line"></i></div>
                Revenue Report
            </a>
            <a href="{{ route('history.monthly') }}" class="quick-action-btn">
                <div class="qa-icon qa-blue"><i class="fas fa-history"></i></div>
                Monthly History
            </a>
            <a href="{{ route('payments.alerts') }}" class="quick-action-btn">
                <div class="qa-icon qa-yellow"><i class="fas fa-bell"></i></div>
                Deadline Alerts
            </a>
            <a href="{{ route('payments.alerts.overdue') }}" class="quick-action-btn">
                <div class="qa-icon qa-red"><i class="fas fa-exclamation-triangle"></i></div>
                Overdue
            </a>
            <a href="{{ route('payment-types.index') }}" class="quick-action-btn">
                <div class="qa-icon qa-purple"><i class="fas fa-tags"></i></div>
                Payment Types
            </a>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    {{-- Recent Payments --}}
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
                        <th>Amount</th>
                        <th>For Month</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr>
                        <td><span class="mono" style="font-size:12px;color:var(--primary)">{{ $payment->receipt_number }}</span></td>
                        <td>
                            <div style="font-weight:600;color:var(--text-primary)">{{ $payment->student?->full_name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $payment->student?->student_id ?? '—' }}</div>
                        </td>
                        <td style="font-weight:700;color:var(--text-primary)">${{ number_format($payment->amount_paid, 2) }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">
                            {{ $payment->due_date?->format('M d, Y') ?? $payment->payment_date?->format('M d, Y') }}
                            @if($payment->payment_date && $payment->due_date && $payment->payment_date->format('Y-m-d') !== $payment->due_date->format('Y-m-d'))
                                <div style="font-size:10px;color:var(--text-muted)">paid {{ $payment->payment_date->format('M d, Y') }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-success">Paid</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state"><i class="fas fa-receipt"></i><p>No recent payments</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Deadline Alerts --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-bell" style="color:var(--warning)" aria-hidden="true"></i> Alerts</div>
            <a href="{{ route('payments.alerts') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div style="padding:12px;display:flex;flex-direction:column;gap:8px;max-height:400px;overflow-y:auto">
            @forelse($alertData as $data)
            <a href="{{ route('students.show', $data['student']) }}" class="alert-item">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px">
                    <div style="font-weight:600;font-size:13px;color:var(--text-primary)">{{ $data['student']->full_name ?? '—' }}</div>
                    @if($data['alertLevel'] === 'overdue')
                        <span class="deadline-pill deadline-overdue">Overdue</span>
                    @elseif($data['alertLevel'] === 'critical')
                        <span class="deadline-pill deadline-critical">{{ $data['daysUntilNextPayment'] }}d left</span>
                    @else
                        <span class="deadline-pill deadline-warning">{{ $data['daysUntilNextPayment'] }}d left</span>
                    @endif
                </div>
                <div style="font-size:11px;color:var(--text-muted)">{{ $data['student']->student_id ?? '—' }}</div>
                <div style="font-size:13px;font-weight:700;color:var(--primary);margin-top:4px">
                    Next: {{ $data['nextPaymentDate']->format('M d, Y') }}
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

{{-- Monthly Chart --}}
<div class="card" style="margin-top:18px">
    <div class="card-header">
        <div class="card-title">Monthly Revenue (Last 6 Months)</div>
    </div>
    <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:10px;height:150px">
            @php $maxVal = max(array_column($monthlyData, 'amount')) ?: 1; @endphp
            @foreach($monthlyData as $data)
            @php
                $h = max(4, ($data['amount'] / $maxVal) * 100);
                $op = $loop->last ? 1 : 0.55;
            @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;height:100%">
                <div style="flex:1;display:flex;align-items:flex-end;width:100%">
                    <div class="chart-bar"
                         data-height="{{ $h }}"
                         data-opacity="{{ $op }}"
                         title="{{ $data['month'] }}: ${{ number_format($data['amount'], 2) }}">
                    </div>
                </div>
                <div class="chart-label">{{ $data['month'] }}</div>
                <div class="chart-val">${{ number_format($data['amount']/1000, 1) }}k</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
