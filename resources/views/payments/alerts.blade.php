@extends('layouts.app')
@section('title', 'Deadline Alerts')
@section('page-title', 'Deadline Alerts')

@section('content')
<div style="display:grid;grid-template-columns:1fr;gap:24px;margin-bottom:32px">
    <style>
        @media (min-width: 768px) {
            .alerts-grid { grid-template-columns:repeat(2,1fr) !important; }
        }
        @media (min-width: 1200px) {
            .alerts-grid { grid-template-columns:repeat(3,1fr) !important; }
        }
    </style>
</div>
<div class="alerts-grid" style="display:grid;grid-template-columns:1fr;gap:24px;margin-bottom:32px">
    <!-- Overdue Section -->
    <div class="card" style="border-left:5px solid var(--danger);border-radius:16px">
        <div class="card-header" style="padding:20px;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:16px;font-weight:800;color:var(--danger);display:flex;align-items:center;gap:8px">
                    <i class="fas fa-circle-exclamation" style="font-size:20px"></i> Overdue
                </div>
            </div>
            <span style="font-size:28px;font-weight:900;color:var(--danger);background:var(--danger);background:rgb(from var(--danger) r g b / 0.1);padding:8px 16px;border-radius:12px">{{ count($overdue) }}</span>
        </div>
        <div class="card-body" style="padding:20px;padding-top:0">
            @forelse($overdue->take(2) as $data)
            <div style="padding:16px;border-radius:12px;border:1px solid var(--gray-200);background:white;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.03)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                    <div style="font-weight:700;font-size:14px;color:var(--gray-900)">{{ $data['student']->full_name ?? '—' }}</div>
                </div>
                <div style="font-size:12px;color:var(--gray-500);margin-bottom:4px">{{ $data['student']->student_id ?? '—' }}</div>
                @if($data['nextPaymentDate'])
                <div style="font-size:12px;color:var(--gray-600)">Next: {{ $data['nextPaymentDate']->format('M d, Y') }}</div>
                @endif
                <div style="font-size:16px;font-weight:900;color:var(--danger);margin-top:8px">{{ abs($data['daysUntilNextPayment']) }} days overdue</div>
            </div>
            @empty
            <div class="empty-state" style="padding:32px;text-align:center">
                <i class="fas fa-check-circle" style="color:var(--success);font-size:48px;margin-bottom:12px"></i>
                <p style="margin:0;font-size:15px;color:var(--gray-600);font-weight:600">No overdue payments!</p>
            </div>
            @endforelse
            @if(count($overdue) > 2)
            <div style="margin-top:12px;text-align:center">
                <a href="{{ route('payments.alerts.overdue') }}" class="btn btn-primary" style="width:100%">
                    <i class="fas fa-list"></i> View All {{ count($overdue) }} Overdue Students
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Closely Date Section -->
    <div class="card" style="border-left:5px solid var(--warning);border-radius:16px">
        <div class="card-header" style="padding:20px;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:16px;font-weight:800;color:var(--warning);display:flex;align-items:center;gap:8px">
                    <i class="fas fa-bell" style="font-size:20px"></i> Closely Date
                </div>
            </div>
            <span style="font-size:28px;font-weight:900;color:var(--warning);background:rgb(from var(--warning) r g b / 0.15);padding:8px 16px;border-radius:12px">{{ count($closely) }}</span>
        </div>
        <div class="card-body" style="padding:20px;padding-top:0">
            @forelse($closely->take(2) as $data)
            <div style="padding:16px;border-radius:12px;border:1px solid var(--gray-200);background:white;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.03)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                    <div style="font-weight:700;font-size:14px;color:var(--gray-900)">{{ $data['student']->full_name ?? '—' }}</div>
                </div>
                <div style="font-size:12px;color:var(--gray-500);margin-bottom:4px">{{ $data['student']->student_id ?? '—' }}</div>
                @if($data['nextPaymentDate'])
                <div style="font-size:12px;color:var(--gray-600)">Next: {{ $data['nextPaymentDate']->format('M d, Y') }}</div>
                @endif
                <div style="font-size:16px;font-weight:900;color:var(--warning);margin-top:8px">{{ $data['daysUntilNextPayment'] }} days left</div>
            </div>
            @empty
            <div class="empty-state" style="padding:32px;text-align:center">
                <i class="fas fa-check-circle" style="color:var(--success);font-size:48px;margin-bottom:12px"></i>
                <p style="margin:0;font-size:15px;color:var(--gray-600);font-weight:600">No closely date payments!</p>
            </div>
            @endforelse
            @if(count($closely) > 2)
            <div style="margin-top:12px;text-align:center">
                <a href="{{ route('payments.alerts.closely') }}" class="btn btn-primary" style="width:100%;background:var(--warning);border-color:var(--warning)">
                    <i class="fas fa-list"></i> View All {{ count($closely) }} Closely Date Students
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Section -->
    <div class="card" style="border-left:5px solid var(--primary);border-radius:16px">
        <div class="card-header" style="padding:20px;display:flex;justify-content:space-between;align-items:center">
            <div>
                <div style="font-size:16px;font-weight:800;color:var(--primary);display:flex;align-items:center;gap:8px">
                    <i class="fas fa-clock" style="font-size:20px"></i> Upcoming
                </div>
            </div>
            <span style="font-size:28px;font-weight:900;color:var(--primary);background:rgb(from var(--primary) r g b / 0.1);padding:8px 16px;border-radius:12px">{{ count($upcoming) }}</span>
        </div>
        <div class="card-body" style="padding:20px;padding-top:0">
            @forelse($upcoming->take(2) as $data)
            <div style="padding:16px;border-radius:12px;border:1px solid var(--gray-200);background:white;margin-bottom:12px;box-shadow:0 2px 8px rgba(0,0,0,0.03)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
                    <div style="font-weight:700;font-size:14px;color:var(--gray-900)">{{ $data['student']->full_name ?? '—' }}</div>
                </div>
                <div style="font-size:12px;color:var(--gray-500);margin-bottom:4px">{{ $data['student']->student_id ?? '—' }}</div>
                @if($data['nextPaymentDate'])
                <div style="font-size:12px;color:var(--gray-600)">Next: {{ $data['nextPaymentDate']->format('M d, Y') }}</div>
                @endif
                <div style="font-size:16px;font-weight:900;color:var(--primary);margin-top:8px">{{ $data['daysUntilNextPayment'] }} days left</div>
            </div>
            @empty
            <div class="empty-state" style="padding:32px;text-align:center">
                <i class="fas fa-check-circle" style="color:var(--success);font-size:48px;margin-bottom:12px"></i>
                <p style="margin:0;font-size:15px;color:var(--gray-600);font-weight:600">No upcoming payments!</p>
            </div>
            @endforelse
            @if(count($upcoming) > 2)
            <div style="margin-top:12px;text-align:center">
                <a href="{{ route('payments.alerts.upcoming') }}" class="btn btn-primary" style="width:100%">
                    <i class="fas fa-list"></i> View All {{ count($upcoming) }} Upcoming Students
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- All Students Section -->
<div class="card">
    <div class="card-header">
        <div class="card-title">All Students ({{ count($allStudentData) }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Alert</th>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Come From</th>
                    <th>Subject</th>
                    <th>Last Payment</th>
                    <th>Next Payment</th>
                    <th>Days Left</th>
                    <th>Monthly Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allStudentData as $data)
                @php
                    $alertLevel = $data['alertLevel'];
                    $rowClass = match($alertLevel) {
                        'overdue' => 'alert-level-overdue',
                        'closely' => 'alert-level-critical',
                        default => ''
                    };
                    $deadlinePillClass = match($alertLevel) {
                        'overdue' => 'deadline-overdue',
                        'closely' => 'deadline-critical',
                        'upcoming' => 'deadline-normal',
                        default => ''
                    };
                    $alertLabel = match($alertLevel) {
                        'overdue' => 'Overdue',
                        'closely' => 'Critical',
                        'upcoming' => 'Upcoming',
                        default => 'Normal'
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>
                        <span class="deadline-pill {{ $deadlinePillClass }}">
                            {{ $alertLabel }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('students.show', $data['student']) }}" style="text-decoration:none">
                            <div style="font-weight:600;color:var(--gray-800)">{{ $data['student']->full_name }} ({{ ucfirst($data['student']->gender ?? 'N/A') }})</div>
                            <div style="font-size:11px;color:var(--gray-400)">{{ $data['student']->student_id }}</div>
                        </a>
                    </td>
                    <td>Grade {{ $data['student']->year_level ?? '—' }}</td>
                    <td>{{ $data['student']->come_from ?? '-' }}</td>
                    <td>{{ $data['student']->subject ?? '-' }}</td>
                    <td style="font-size:12px">{{ $data['lastPayment']?->payment_date?->format('M d, Y') ?? '—' }}</td>
                    <td style="font-size:12px">{{ $data['nextPaymentDate']?->format('M d, Y') ?? '—' }}</td>
                    <td style="font-weight:600">
                        @if($data['daysUntilNextPayment'] !== null)
                            @if($data['daysUntilNextPayment'] < 0)
                                <span class="text-danger">{{ abs($data['daysUntilNextPayment']) }} days late</span>
                            @elseif($data['daysUntilNextPayment'] <= 7)
                                <span class="text-warning">{{ $data['daysUntilNextPayment'] }} days</span>
                            @else
                                <span class="text-success">{{ $data['daysUntilNextPayment'] }} days</span>
                            @endif
                        @else
                            <span class="text-gray-600">—</span>
                        @endif
                    </td>
                    <td style="font-weight:600">${{ number_format($data['student']->monthly_fee ?? 0, 2) }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('students.show', $data['student']) }}" class="btn btn-icon btn-outline" title="View Student"><i class="fas fa-user" style="font-size:11px"></i></a>
                            <a href="{{ route('payments.create') }}?student_id={{ $data['student']->id }}" class="btn btn-icon btn-outline" title="Add Payment" style="color:var(--primary)"><i class="fas fa-plus" style="font-size:11px"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="empty-state"><i class="fas fa-users"></i><p>No students found</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
