@extends('layouts.app')
@section('title', $title)
@section('page-title', $title)
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm" onclick="history.length>1?history.back():window.location='{{ route('payments.alerts') }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('styles')
<style>
.grade-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius); padding:24px; text-align:center;
    text-decoration:none; color:inherit; display:block;
    transition:box-shadow 0.15s, transform 0.15s, border-color 0.15s;
}
.grade-card:hover {
    box-shadow:var(--shadow-md); transform:translateY(-2px);
    border-color:var(--primary); color:var(--primary);
}
.grade-num { font-size:44px; font-weight:900; color:var(--primary); line-height:1; }
.grade-lbl { font-size:13px; color:var(--text-muted); font-weight:600; margin-top:6px; }
</style>
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
    <div class="card-body">
        @if(isset($studentData) && count($studentData) > 0)
        {{-- Student list --}}
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
                                <div style="font-weight:600;color:var(--text-primary)">{{ $data['student']->full_name }} ({{ ucfirst($data['student']->gender ?? 'N/A') }})</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $data['student']->student_id }}</div>
                            </a>
                        </td>
                        <td style="color:var(--text-secondary)">Grade {{ $data['student']->year_level ?? '—' }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">
                            @if($data['lastPayment']?->due_date)
                                {{ $data['lastPayment']->due_date->format('M d, Y') }}
                                @if($data['lastPayment']->payment_date && $data['lastPayment']->payment_date->format('Y-m-d') !== $data['lastPayment']->due_date->format('Y-m-d'))
                                    <div style="font-size:10px;color:var(--text-muted)">paid {{ $data['lastPayment']->payment_date->format('M d, Y') }}</div>
                                @endif
                            @else
                                {{ $data['lastPayment']?->payment_date?->format('M d, Y') ?? '—' }}
                            @endif
                        </td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $data['nextPaymentDate']?->format('M d, Y') ?? '—' }}</td>
                        <td style="font-weight:600">
                            @if($data['daysUntilNextPayment'] !== null)
                                @if($data['daysUntilNextPayment'] < 0)
                                    <span class="text-danger">{{ abs($data['daysUntilNextPayment']) }}d late</span>
                                @elseif($data['daysUntilNextPayment'] <= 7)
                                    <span class="text-warning">{{ $data['daysUntilNextPayment'] }}d</span>
                                @else
                                    <span class="text-success">{{ $data['daysUntilNextPayment'] }}d</span>
                                @endif
                            @else <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="font-weight:600;color:var(--text-primary)">${{ number_format($data['student']->monthly_fee ?? 0, 2) }}</td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <a href="{{ route('students.show', $data['student']) }}" class="btn btn-icon btn-outline" title="View student"><i class="fas fa-user" style="font-size:11px" aria-hidden="true"></i></a>
                                <a href="{{ route('payments.create') }}?student_id={{ $data['student']->id }}" class="btn btn-icon btn-outline" title="Add payment" style="color:var(--primary)"><i class="fas fa-plus" style="font-size:11px" aria-hidden="true"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        {{-- Grade grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px">
            @forelse($grades as $grade)
            <a href="{{ $type === 'overdue' ? route('payments.alerts.overdue.grade', $grade) : ($type === 'closely' ? route('payments.alerts.closely.grade', $grade) : route('payments.alerts.upcoming.grade', $grade)) }}"
               class="grade-card" aria-label="Grade {{ $grade }}">
                <div class="grade-num">G{{ $grade }}</div>
                <div class="grade-lbl">Grade {{ $grade }}</div>
            </a>
            @empty
            <div class="empty-state" style="grid-column:1/-1">
                <i class="fas fa-check-circle" style="color:var(--success);font-size:36px" aria-hidden="true"></i>
                <p style="margin-top:8px;font-weight:600">No grades found!</p>
            </div>
            @endforelse
        </div>
        @endif
    </div>
</div>
@endsection
