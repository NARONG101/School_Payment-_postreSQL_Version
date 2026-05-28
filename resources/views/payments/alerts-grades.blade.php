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
        <a href="{{ route('payments.alerts') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Alerts
        </a>
    </div>
    <div class="card-body">
        @if(isset($studentData) && count($studentData) > 0)
            <!-- Student List View -->
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Last Payment</th>
                            <th>Next Payment</th>
                            <th>Days Left</th>
                            <th>Monthly Fee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentData as $data)
                        <tr>
                            <td>
                                <a href="{{ route('students.show', $data['student']) }}" style="text-decoration:none">
                                    <div style="font-weight:600;color:var(--gray-800)">{{ $data['student']->full_name }} ({{ ucfirst($data['student']->gender ?? 'N/A') }})</div>
                                    <div style="font-size:11px;color:var(--gray-400)">{{ $data['student']->student_id }}</div>
                                </a>
                            </td>
                            <td>Grade {{ $data['student']->year_level ?? '—' }}</td>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- Grade List View -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
                @forelse($grades as $grade)
                    <a href="{{ 
                        $type === 'overdue' ? route('payments.alerts.overdue.grade', $grade) : 
                        ($type === 'closely' ? route('payments.alerts.closely.grade', $grade) : 
                        route('payments.alerts.upcoming.grade', $grade)) 
                    }}" style="text-decoration:none">
                    <div class="card" style="border:1px solid var(--gray-200);cursor:pointer;transition:box-shadow 0.15s"
                         onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'"
                         onmouseout="this.style.boxShadow='none'">
                        <div class="card-body" style="text-align:center">
                            <div style="font-size:48px;font-weight:900;color:var(--primary)">G{{ $grade }}</div>
                            <div style="font-size:14px;color:var(--gray-500);font-weight:600;margin-top:8px">Grade {{ $grade }}</div>
                        </div>
                    </div>
                    </a>
                @empty
                    <div class="empty-state" style="grid-column:1/-1">
                        <i class="fas fa-check-circle" style="color:var(--success);font-size:28px"></i>
                        <p style="margin-top:8px">No grades found!</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection
