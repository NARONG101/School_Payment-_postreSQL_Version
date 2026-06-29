
@extends('layouts.app')
@section('title','All Payments')
@section('page-title','All Payments')
@section('topbar-actions')
    <a href="{{ route('payments.export.csv', request()->query()) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-download" aria-hidden="true"></i> <span>CSV</span>
    </a>
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New Payment</span>
    </a>
@endsection
@section('styles')
<style>
.pay-sort-item { transition:background 0.12s; }
.pay-sort-item:hover { background:var(--bg-hover) !important; color:var(--text-primary) !important; }
.pay-sort-item:last-child { border-bottom:none !important; }
.pay-sort-active { color:var(--primary) !important; background:var(--primary-50) !important; }

/* Class type tabs */
.class-type-tab {
    display:inline-flex; align-items:center; gap:7px;
    padding:7px 14px; border-radius:8px; border:1px solid var(--border);
    font-size:13px; font-weight:600; text-decoration:none;
    color:var(--text-secondary); background:var(--bg-card);
    transition:all 0.15s;
}
.class-type-tab:hover { background:var(--bg-hover); color:var(--text-primary); }
.class-type-tab.active-all     { background:var(--primary); color:#fff; border-color:var(--primary); }
.class-type-tab.active-weekday { background:var(--success); color:#fff; border-color:var(--success); }
.class-type-tab.active-weekend { background:#7c3aed; color:#fff; border-color:#7c3aed; }

/* Payment method stats */
.payment-method-stat {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    font-size: 12px;
    font-weight: 600;
}
.payment-method-stat.badge-success { background: var(--success-light); border-color: var(--success); color: var(--success); }
.payment-method-stat.badge-primary { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
.payment-method-stat.badge-purple { background: #f3e8ff; border-color: #7c3aed; color: #7c3aed; }
</style>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">All Payments ({{ $payments->count() }})</div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @php
                $methodLabels = [
                    'cash' => ['label' => 'Cash', 'class' => 'badge-success', 'icon' => '💵'],
                    'aba' => ['label' => 'ABA', 'class' => 'badge-primary', 'icon' => '🏦'],
                    'ac' => ['label' => 'ACLEDA', 'class' => 'badge-purple', 'icon' => '🏦'],
                    'auto' => ['label' => 'Auto', 'class' => 'badge-secondary', 'icon' => '⚡'],
                ];
            @endphp
            @foreach($methodLabels as $method => $info)
                @if(isset($paymentMethodCounts[$method]))
                    <div class="payment-method-stat {{ $info['class'] }}">
                        <span>{{ $info['icon'] }} {{ $info['label'] }}:</span>
                        <span style="font-weight: bold">{{ $paymentMethodCounts[$method] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    {{-- Class Type Filter --}}
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);background:var(--bg-muted);display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span style="font-size:12px;font-weight:700;color:var(--text-muted)">
            <i class="fas fa-filter" aria-hidden="true"></i> Class:
        </span>
        <a href="{{ route('payments.index', array_merge(request()->except('class_type'), [])) }}"
           class="class-type-tab {{ $classType === '' ? 'active-all' : '' }}">
            <i class="fas fa-users"></i> {{ __('app.all_classes') }}
        </a>
        <a href="{{ route('payments.index', array_merge(request()->query(), ['class_type' => 'weekday'])) }}"
           class="class-type-tab {{ $classType === 'weekday' ? 'active-weekday' : '' }}">
            <i class="fas fa-calendar-week"></i> {{ __('app.weekday') }}
        </a>
        <a href="{{ route('payments.index', array_merge(request()->query(), ['class_type' => 'weekend'])) }}"
           class="class-type-tab {{ $classType === 'weekend' ? 'active-weekend' : '' }}">
            <i class="fas fa-calendar-day"></i> {{ __('app.weekend') }}
        </a>
    </div>
    <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
        <form method="GET" action="{{ route('payments.index') }}" class="filter-bar" id="filterForm" role="search">
            {{-- Live search — client-side only --}}
            <input type="text" class="form-control"
                   placeholder="Search student, receipt…"
                   value="" aria-label="Search payments"
                   id="paySearchInput" autocomplete="off">

            <div style="flex:1"></div>

            {{-- Sort dropdown button --}}
            <div style="position:relative" id="paymentSortDropdown">
                <button type="button" id="paymentSortBtn"
                        class="btn btn-outline btn-sm"
                        style="gap:8px;min-width:130px;justify-content:space-between"
                        aria-haspopup="true" aria-expanded="false">
                    <span>
                        <i class="fas fa-sort" aria-hidden="true"></i>
                        Sort:
                        <strong>{{ ['id'=>'Newest','date'=>'By Month','grade'=>'By Grade'][request('sort_by','id')] ?? 'Newest' }}</strong>
                    </span>
                    <i class="fas fa-chevron-down pay-sort-chevron" style="font-size:10px;transition:transform 0.2s" aria-hidden="true"></i>
                </button>
                <div id="paymentSortMenu"
                     style="display:none;position:absolute;right:0;top:calc(100% + 6px);
                            background:var(--bg-card);border:1px solid var(--border);
                            border-radius:var(--radius);box-shadow:var(--shadow-md);
                            min-width:150px;z-index:500;overflow:hidden">
                    @foreach(['id'=>['l'=>'Newest First','i'=>'fa-clock-rotate-left'],
                             'date'=>['l'=>'By Month','i'=>'fa-calendar'],
                             'grade'=>['l'=>'By Grade (High→Low)','i'=>'fa-layer-group']] as $k=>$o)
                    @php $isActive = request('sort_by', 'id') === $k; @endphp
                    <a href="{{ route('payments.index', array_merge(request()->query(), ['sort_by'=>$k])) }}"
                       class="pay-sort-item{{ $isActive ? ' pay-sort-active' : '' }}"
                       style="display:flex;align-items:center;gap:10px;padding:10px 14px;
                              text-decoration:none;font-size:13px;font-weight:500;
                              color:{{ $isActive?'var(--primary)':'var(--text-primary)' }};
                              background:{{ $isActive?'var(--primary-50)':'transparent' }};
                              border-bottom:1px solid var(--border);">
                        <i class="fas {{ $o['i'] }}" style="width:14px;color:var(--text-muted)" aria-hidden="true"></i>
                        {{ $o['l'] }}
                        @if($isActive)
                            <i class="fas fa-check" style="margin-left:auto;color:var(--primary);font-size:11px" aria-hidden="true"></i>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            @if(request('sort_by'))
            <a href="{{ route('payments.index', request()->only('class_type')) }}" class="btn btn-outline btn-sm" title="Reset sort">
                <i class="fas fa-times" aria-hidden="true"></i>
            </a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>For Month</th>
                    <th>Time Type</th>
                    <th>Payment Method</th>
                    @if(Auth::user()->isAdmin())
                    <th>Amount Paid</th>
                    @endif
                    <th>Next Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                @php
                    $sc = ['paid'=>'text-success','partial'=>'text-warning','pending'=>'text-info','overdue'=>'text-danger'];
                    $cls = $sc[$payment->status] ?? 'text-muted';
                    // Build a searchable string for client-side search
                    $searchStr = strtolower(implode(' ', array_filter([
                        $payment->receipt_number,
                        $payment->student?->full_name,
                        $payment->student?->student_id,
                        $payment->student?->subject,
                        $payment->status,
                        $payment->time_type,
                        $payment->payment_method,
                        'grade '.($payment->student?->year_level ?? ''),
                    ])));
                    
                    $methodDisplay = [
                        'cash' => '💵 Cash',
                        'aba' => '🏦 ABA',
                        'ac' => '🏦 ACLEDA',
                        'auto' => '⚡ Auto',
                    ][$payment->payment_method] ?? $payment->payment_method;
                @endphp
                <tr data-searchable data-search="{{ $searchStr }}">
                    <td><span class="mono" style="font-size:12px;color:var(--primary)">{{ $payment->receipt_number }}</span></td>
                    <td>
                        @if($payment->student)
                        <a href="{{ route('students.show', $payment->student) }}" style="text-decoration:none">
                            <div style="font-weight:600;color:var(--text-primary)">{{ $payment->student->full_name }} ({{ ucfirst($payment->student->gender ?? 'N/A') }})</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $payment->student->student_id }}</div>
                        </a>
                        @else <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td style="color:var(--text-secondary)">Grade {{ $payment->student?->year_level ?? '—' }}</td>
                    <td style="color:var(--text-secondary)">{{ $payment->student?->subject ?? '—' }}</td>
                    <td><span class="{{ $cls }}" style="font-weight:600">{{ ucfirst($payment->status) }}</span></td>
                    <td style="font-size:12px;color:var(--text-muted)">
                        {{ $payment->due_date?->format('M d, Y') ?? $payment->payment_date?->format('M d, Y') ?? '—' }}
                        @if($payment->payment_date && $payment->due_date && $payment->payment_date->format('Y-m-d') !== $payment->due_date->format('Y-m-d'))
                            <div style="font-size:10px;color:var(--text-muted)">paid {{ $payment->payment_date->format('M d, Y') }}</div>
                        @endif
                    </td>
                    <td style="color:var(--text-secondary)">
                        @if($payment->time_types && count($payment->time_types) > 0)
                            @foreach($payment->time_types as $type)
                                <span style="display:inline-block;margin-right:4px;font-size:11px;">{{ $type }}</span>
                            @endforeach
                        @else
                            {{ $payment->time_type ?? '—' }}
                        @endif
                    </td>
                    <td style="color:var(--text-secondary)">{{ $methodDisplay }}</td>
                    @if(Auth::user()->isAdmin())
                    <td class="{{ $cls }}" style="font-weight:600">${{ number_format($payment->amount_paid,2) }}</td>
                    @endif
                    <td style="font-size:12px;color:var(--text-muted)">{{ $payment->next_payment_date?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-icon btn-outline" title="View"><i class="fas fa-eye" style="font-size:11px" aria-hidden="true"></i></a>
                            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-icon btn-outline" title="Receipt" style="color:var(--danger)" target="_blank" rel="noopener"><i class="fas fa-file-pdf" style="font-size:11px" aria-hidden="true"></i></a>
                            <button type="button" class="btn btn-icon btn-outline delete-btn" title="Delete" style="color:var(--danger)"
                                    data-action="{{ route('payments.destroy', $payment) }}"
                                    data-title="Delete Payment"
                                    data-body="Delete receipt {{ e($payment->receipt_number) }}? This cannot be undone.">
                                <i class="fas fa-trash" style="font-size:11px" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ Auth::user()->isAdmin() ? 11 : 10 }}"><div class="empty-state"><i class="fas fa-receipt" aria-hidden="true"></i><p>No payments found</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInp = document.getElementById('paySearchInput');
    var allRows   = Array.prototype.slice.call(document.querySelectorAll('tbody tr[data-searchable]'));
    var countEl   = document.querySelector('.card-title');
    var total     = allRows.length;

    /* ── Sort dropdown toggle ────────────────────────────── */
    var sortBtn  = document.getElementById('paymentSortBtn');
    var sortMenu = document.getElementById('paymentSortMenu');
    var chevron  = document.querySelector('.pay-sort-chevron');

    if (sortBtn && sortMenu) {
        sortBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = sortMenu.style.display !== 'none';
            sortMenu.style.display = open ? 'none' : 'block';
            if (chevron) chevron.style.transform = open ? '' : 'rotate(180deg)';
            sortBtn.setAttribute('aria-expanded', String(!open));
        });
        document.addEventListener('click', function () {
            sortMenu.style.display = 'none';
            if (chevron) chevron.style.transform = '';
            sortBtn.setAttribute('aria-expanded', 'false');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { sortMenu.style.display = 'none'; sortBtn.focus(); }
        });
    }

    /* ── Client-side live search ─────────────────────────── */
    function doSearch() {
        var term  = (searchInp ? searchInp.value : '').trim().toLowerCase();
        var shown = 0;
        allRows.forEach(function (row) {
            var match = term === '' || (row.getAttribute('data-search') || '').indexOf(term) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        if (countEl) {
            countEl.textContent = 'All Payments (' + (shown === total ? total : shown + ' of ' + total) + ')';
        }
    }

    if (searchInp) {
        searchInp.addEventListener('input', doSearch);
        searchInp.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { searchInp.value = ''; doSearch(); }
        });
    }
});
</script>
@endsection

