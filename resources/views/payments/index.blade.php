@extends('layouts.app')
@section('title','All Payments')
@section('page-title','All Payments')
@section('topbar-actions')
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Payment</a>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">All Payments ({{ $payments->count() }})</div>
    </div>
    <div style="padding:16px;border-bottom:1px solid var(--gray-200)">
        <form method="GET" class="filter-bar" id="filterForm">
            <input type="text" name="search" class="form-control" placeholder="Search student, receipt..." value="{{ request('search') }}">
            <select name="grade" class="form-control" style="width:150px;flex:none">
                <option value="">All Grades</option>
                @for($i=1;$i<=12;$i++)
                <option value="{{ $i }}" {{ request('grade')==$i?'selected':'' }}>Grade {{ $i }}</option>
                @endfor
            </select>
            <select name="sort_by" class="form-control" style="width:150px;flex:none">
                <option value="id" {{ request('sort_by')=='id'?'selected':'' }}>Sort by ID</option>
                <option value="date" {{ request('sort_by')=='date'?'selected':'' }}>Sort by Date</option>
                <option value="grade" {{ request('sort_by')=='grade'?'selected':'' }}>Sort by Grade</option>
            </select>
            <a href="{{ route('payments.index') }}" class="btn btn-outline btn-sm">Reset</a>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Come From</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th>Time Type</th>
                    <th>Amount Paid</th>
                    <th>Next Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                @php
                    $statusClasses = [
                        'paid' => 'text-success',
                        'partial' => 'text-warning',
                        'pending' => 'text-info',
                        'overdue' => 'text-danger'
                    ];
                    $statusClass = $statusClasses[$payment->status] ?? 'text-gray-600';
                @endphp
                <tr>
                    <td><span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--primary)">{{ $payment->receipt_number }}</span></td>
                    <td>
                        @if($payment->student)
                            <a href="{{ route('students.show', $payment->student) }}" style="text-decoration:none">
                                <div style="font-weight:600;color:var(--gray-800)">{{ $payment->student->full_name }} ({{ ucfirst($payment->student->gender ?? 'N/A') }})</div>
                                <div style="font-size:11px;color:var(--gray-400)">{{ $payment->student->student_id }}</div>
                            </a>
                        @else
                            <div style="font-weight:600;color:var(--gray-400)">—</div>
                        @endif
                    </td>
                    <td>Grade {{ $payment->student?->year_level ?? '—' }}</td>
                    <td>{{ $payment->student?->come_from ?? '-' }}</td>
                    <td>{{ $payment->student?->subject ?? '-' }}</td>
                    <td><span class="{{ $statusClass }}" style="font-weight:600">{{ ucfirst($payment->status) }}</span></td>
                    <td style="font-size:12px">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ ucfirst($payment->time_type ?? 'weekday') }}</td>
                    <td class="{{ $statusClass }}" style="font-weight:600">${{ number_format($payment->amount_paid,2) }}</td>
                    <td style="font-size:12px">{{ $payment->next_payment_date?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-icon btn-outline" title="View"><i class="fas fa-eye" style="font-size:11px"></i></a>
                            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-icon btn-outline" title="Receipt" style="color:var(--danger)" target="_blank"><i class="fas fa-file-pdf" style="font-size:11px"></i></a>
                            <button type="button" class="btn btn-icon btn-outline delete-btn" title="Delete" style="color:var(--danger)"
                                    data-action="{{ route('payments.destroy', $payment) }}"
                                    data-title="Delete Payment"
                                    data-body="Are you sure you want to delete payment {{ $payment->receipt_number }}? This action cannot be undone.">
                                <i class="fas fa-trash" style="font-size:11px"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11"><div class="empty-state"><i class="fas fa-receipt"></i><p>No payments found</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const inputs = filterForm.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                filterForm.submit();
            }
        });
    });
});
</script>
@endsection
