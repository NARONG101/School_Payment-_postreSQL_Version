@extends('layouts.app')
@section('title', 'Deadline Alerts')
@section('page-title', 'Deadline Alerts')

@section('topbar-actions')
    <a href="{{ route('history.monthly') }}" class="btn btn-outline btn-sm">
        <i class="fas fa-folder-open" aria-hidden="true"></i> <span>Monthly History</span>
    </a>
    <a href="{{ route('payments.alerts.export.csv', request()->query()) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-download" aria-hidden="true"></i> <span>CSV</span>
    </a>
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New Payment</span>
    </a>
@endsection

@section('styles')
<style>
/* ── Summary cards ─────────────────────────────── */
.alert-summary-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px; margin-bottom:20px;
}
@media(max-width:768px){ .alert-summary-grid{grid-template-columns:1fr;} }

.alert-summary-card {
    border-radius:var(--radius);
    padding:18px 20px;
    display:flex; align-items:center;
    justify-content:space-between; gap:12px;
    border:1px solid var(--border);
    background:var(--bg-card);
    cursor:pointer; text-decoration:none; color:inherit;
    transition:transform 0.15s, box-shadow 0.15s;
}
.alert-summary-card:hover { transform:translateY(-2px); box-shadow:var(--shadow-md); }
.alert-summary-card.active-filter { outline:2px solid currentColor; outline-offset:2px; }

.asc-icon { font-size:28px; width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.asc-count { font-size:36px; font-weight:900; line-height:1; }
.asc-label { font-size:12px; font-weight:600; margin-top:3px; opacity:0.8; }

/* ── Filter bar ─────────────────────────────────── */
.alert-filter-bar {
    display:flex; gap:10px; align-items:center;
    flex-wrap:wrap; padding:14px 16px;
    border-bottom:1px solid var(--border);
    background:var(--bg-card);
}
.filter-pill {
    padding:5px 14px; border-radius:20px;
    font-size:12px; font-weight:600; cursor:pointer;
    border:2px solid transparent; transition:all 0.15s;
    text-decoration:none; display:inline-flex; align-items:center; gap:6px;
}
.filter-pill.all     { background:var(--bg-muted);       color:var(--text-secondary);  border-color:var(--border); }
.filter-pill.overdue { background:var(--danger-light);   color:var(--danger);           border-color:var(--danger); }
.filter-pill.closely { background:var(--warning-light);  color:var(--warning);          border-color:var(--warning); }
.filter-pill.upcoming{ background:var(--primary-light);  color:var(--primary);          border-color:var(--primary); }
.filter-pill.active  { color:#fff !important; }
.filter-pill.all.active     { background:var(--text-secondary); border-color:var(--text-secondary); }
.filter-pill.overdue.active { background:var(--danger); }
.filter-pill.closely.active { background:var(--warning); }
.filter-pill.upcoming.active{ background:var(--primary); }

/* ── Table rows by alert level ──────────────────── */
.row-overdue td { background:rgba(239,68,68,0.06) !important; }
.row-closely td { background:rgba(245,158,11,0.06) !important; }

/* ── Class type tabs (shared style) ─────────────── */
.class-type-tab {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 12px; border-radius:8px; border:1px solid var(--border);
    font-size:12px; font-weight:600; text-decoration:none;
    color:var(--text-secondary); background:var(--bg-card);
    transition:all 0.15s;
}
.class-type-tab:hover { background:var(--bg-hover); color:var(--text-primary); }
.class-type-tab.active-all     { background:var(--primary); color:#fff; border-color:var(--primary); }
.class-type-tab.active-weekday { background:var(--success); color:#fff; border-color:var(--success); }
.class-type-tab.active-weekend { background:#7c3aed; color:#fff; border-color:#7c3aed; }
</style>
@endsection

@section('content')

{{-- ── 3 Summary Cards ─────────────────────────────────── --}}
<div class="alert-summary-grid">

    {{-- Overdue --}}
    <a href="?filter=overdue{{ request('grade') ? '&grade='.request('grade') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}"
       class="alert-summary-card {{ $filterLevel === 'overdue' ? 'active-filter' : '' }}"
       style="border-left:4px solid var(--danger);color:var(--danger)">
        <div>
            <div class="asc-count">{{ count($overdue) }}</div>
            <div class="asc-label">Overdue Students</div>
            @if(count($overdue) > 0)
            <div style="font-size:11px;margin-top:4px;opacity:0.7">
                Most late: {{ abs($overdue->first()['daysUntilNextPayment'] ?? 0) }}d
            </div>
            @endif
        </div>
        <div class="asc-icon" style="background:var(--danger-light)">
            <i class="fas fa-exclamation-circle" style="color:var(--danger);font-size:22px" aria-hidden="true"></i>
        </div>
    </a>

    {{-- Closely --}}
    <a href="?filter=closely{{ request('grade') ? '&grade='.request('grade') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}"
       class="alert-summary-card {{ $filterLevel === 'closely' ? 'active-filter' : '' }}"
       style="border-left:4px solid var(--warning);color:var(--warning)">
        <div>
            <div class="asc-count">{{ count($closely) }}</div>
            <div class="asc-label">Due Within 7 Days</div>
            @if(count($closely) > 0)
            <div style="font-size:11px;margin-top:4px;opacity:0.7">
                Soonest: {{ $closely->first()['daysUntilNextPayment'] ?? 0 }}d left
            </div>
            @endif
        </div>
        <div class="asc-icon" style="background:var(--warning-light)">
            <i class="fas fa-bell" style="color:var(--warning);font-size:22px" aria-hidden="true"></i>
        </div>
    </a>

    {{-- Upcoming --}}
    <a href="?filter=upcoming{{ request('grade') ? '&grade='.request('grade') : '' }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}"
       class="alert-summary-card {{ $filterLevel === 'upcoming' ? 'active-filter' : '' }}"
       style="border-left:4px solid var(--primary);color:var(--primary)">
        <div>
            <div class="asc-count">{{ count($upcoming) }}</div>
            <div class="asc-label">Upcoming Payments</div>
            @if(count($upcoming) > 0)
            <div style="font-size:11px;margin-top:4px;opacity:0.7">
                Soonest: {{ $upcoming->first()['daysUntilNextPayment'] ?? 0 }}d left
            </div>
            @endif
        </div>
        <div class="asc-icon" style="background:var(--primary-50)">
            <i class="fas fa-calendar-check" style="color:var(--primary);font-size:22px" aria-hidden="true"></i>
        </div>
    </a>

</div>

{{-- ── All Students Table ───────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">
            All Students
            <span style="font-size:13px;font-weight:400;color:var(--text-muted);margin-left:6px">
                ({{ count($allStudentData) }}
                @if(count($allStudentData) !== $totalCount)
                    of {{ $totalCount }}
                @endif
                )
            </span>
        </div>
    </div>

    {{-- Filter + Search bar --}}
    <form method="GET" action="{{ route('payments.alerts') }}" id="alertFilterForm">

        {{-- Class type filter row --}}
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 16px;border-bottom:1px solid var(--border);background:var(--bg-muted)">
            <span style="font-size:12px;font-weight:700;color:var(--text-muted)">
                <i class="fas fa-filter" aria-hidden="true"></i> Class:
            </span>
            <a href="?{{ http_build_query(array_merge(request()->except('class_type'), [])) }}"
               class="class-type-tab {{ ($classType ?? '') === '' ? 'active-all' : '' }}">
                <i class="fas fa-users"></i> {{ __('app.all_classes') }}
            </a>
            <a href="?{{ http_build_query(array_merge(request()->all(), ['class_type' => 'weekday'])) }}"
               class="class-type-tab {{ ($classType ?? '') === 'weekday' ? 'active-weekday' : '' }}">
                <i class="fas fa-calendar-week"></i> {{ __('app.weekday') }}
            </a>
            <a href="?{{ http_build_query(array_merge(request()->all(), ['class_type' => 'weekend'])) }}"
               class="class-type-tab {{ ($classType ?? '') === 'weekend' ? 'active-weekend' : '' }}">
                <i class="fas fa-calendar-day"></i> {{ __('app.weekend') }}
            </a>
        </div>

        <div class="alert-filter-bar">

            {{-- Status filter pills --}}
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                <a href="?{{ http_build_query(array_merge(request()->except('filter'), ['filter'=>'all'])) }}"
                   class="filter-pill all {{ $filterLevel === 'all' ? 'active' : '' }}">
                    All <span style="font-weight:900">{{ $totalCount }}</span>
                </a>
                <a href="?{{ http_build_query(array_merge(request()->except('filter'), ['filter'=>'overdue'])) }}"
                   class="filter-pill overdue {{ $filterLevel === 'overdue' ? 'active' : '' }}">
                    🔴 Overdue <span style="font-weight:900">{{ count($overdue) }}</span>
                </a>
                <a href="?{{ http_build_query(array_merge(request()->except('filter'), ['filter'=>'closely'])) }}"
                   class="filter-pill closely {{ $filterLevel === 'closely' ? 'active' : '' }}">
                    🟡 Closely <span style="font-weight:900">{{ count($closely) }}</span>
                </a>
                <a href="?{{ http_build_query(array_merge(request()->except('filter'), ['filter'=>'upcoming'])) }}"
                   class="filter-pill upcoming {{ $filterLevel === 'upcoming' ? 'active' : '' }}">
                    🔵 Upcoming <span style="font-weight:900">{{ count($upcoming) }}</span>
                </a>
            </div>

            {{-- Spacer --}}
            <div style="flex:1;min-width:0"></div>

            {{-- Search box with submit button --}}
            <input type="hidden" name="filter" value="{{ $filterLevel }}">
            @if(($classType ?? '') !== '')
            <input type="hidden" name="class_type" value="{{ $classType }}">
            @endif
            <div style="display:flex;gap:0">
                <input type="text" name="search" class="form-control" style="width:200px;flex:none;border-radius:var(--radius-sm) 0 0 var(--radius-sm);border-right:none"
                       placeholder="Search student…" value="{{ $search }}"
                       aria-label="Search students" id="alertSearch">
                <button type="submit" class="btn btn-primary"
                        style="border-radius:0 var(--radius-sm) var(--radius-sm) 0;padding:0 14px;flex-shrink:0"
                        aria-label="Search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Grade filter --}}
            <select name="grade" class="form-control" style="width:120px;flex:none"
                    aria-label="Filter by grade" id="alertGrade">
                <option value="">All Grades</option>
                @foreach($availableGrades as $g)
                <option value="{{ $g }}" {{ $filterGrade == $g ? 'selected' : '' }}>Grade {{ $g }}</option>
                @endforeach
            </select>

            @if($filterLevel !== 'all' || $search !== '' || $filterGrade !== '')
            <a href="{{ route('payments.alerts', ($classType ?? '') ? ['class_type' => $classType] : []) }}" class="btn btn-outline btn-sm">
                <i class="fas fa-times" aria-hidden="true"></i> Clear
            </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Alert</th>
                    <th>Student</th>
                    <th>Grade</th>
                    <th>Class</th>
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
                    $al       = $data['alertLevel'];
                    $rowCls   = match($al) { 'overdue'=>'row-overdue','closely'=>'row-closely', default=>'' };
                    $pillCls  = match($al) { 'overdue'=>'deadline-overdue','closely'=>'deadline-critical','upcoming'=>'deadline-normal', default=>'' };
                    $pillLbl  = match($al) { 'overdue'=>'Overdue','closely'=>'Closely','upcoming'=>'Upcoming', default=>'Normal' };
                @endphp
                <tr class="{{ $rowCls }}">
                    <td>
                        <span class="deadline-pill {{ $pillCls }}">{{ $pillLbl }}</span>
                    </td>
                    <td>
                        <a href="{{ route('students.show', $data['student']) }}" style="text-decoration:none">
                            <div style="font-weight:600;color:var(--text-primary)">
                                {{ $data['student']->full_name }}
                                @if($data['student']->gender)
                                    ({{ ucfirst($data['student']->gender) }})
                                @endif
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $data['student']->student_id }}</div>
                        </a>
                    </td>
                    <td style="color:var(--text-secondary)">Grade {{ $data['student']->year_level ?? '—' }}</td>
                    <td>
                        @if(str_starts_with($data['student']->time_type ?? '', 'sat-sun'))
                            <span class="badge" style="background:rgba(124,58,237,0.12);color:#7c3aed">{{ __('app.weekend') }}</span>
                        @else
                            <span class="badge badge-success">{{ __('app.weekday') }}</span>
                        @endif
                    </td>
                    <td style="color:var(--text-secondary)">{{ $data['student']->subject ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-muted)">
                        @if($data['lastPayment']?->due_date)
                            {{ $data['lastPayment']->due_date->format('M d, Y') }}
                            @if($data['lastPayment']->payment_date &&
                                $data['lastPayment']->payment_date->format('Y-m-d') !== $data['lastPayment']->due_date->format('Y-m-d'))
                                <div style="font-size:10px;color:var(--text-muted)">
                                    paid {{ $data['lastPayment']->payment_date->format('M d, Y') }}
                                </div>
                            @endif
                        @else
                            {{ $data['lastPayment']?->payment_date?->format('M d, Y') ?? '—' }}
                        @endif
                    </td>
                    <td style="font-size:12px;font-weight:600;color:{{ ($data['daysUntilNextPayment'] ?? 1) < 0 ? 'var(--danger)' : (($data['daysUntilNextPayment'] ?? 99) <= 7 ? 'var(--warning)' : 'var(--primary)') }}">
                        {{ $data['nextPaymentDate']?->format('M d, Y') ?? '—' }}
                    </td>
                    <td style="font-weight:700;min-width:80px">
                        @if($data['daysUntilNextPayment'] !== null)
                            @if($data['daysUntilNextPayment'] < 0)
                                <span class="text-danger">{{ abs($data['daysUntilNextPayment']) }}d late</span>
                            @elseif($data['daysUntilNextPayment'] <= 7)
                                <span class="text-warning">{{ $data['daysUntilNextPayment'] }}d</span>
                            @else
                                <span class="text-success">{{ $data['daysUntilNextPayment'] }}d</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="font-weight:600;color:var(--text-primary)">${{ number_format($data['student']->monthly_fee ?? 0, 2) }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('students.show', $data['student']) }}"
                               class="btn btn-icon btn-outline" title="View student">
                                <i class="fas fa-user" style="font-size:11px" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('payments.create') }}?student_id={{ $data['student']->id }}"
                               class="btn btn-icon btn-outline" title="Add payment" style="color:var(--primary)">
                                <i class="fas fa-plus" style="font-size:11px" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <i class="fas fa-check-circle" style="color:var(--success);font-size:40px" aria-hidden="true"></i>
                            <p style="margin-top:8px;font-weight:600">
                                @if($search || $filterGrade || $filterLevel !== 'all')
                                    No students match your filter.
                                    <a href="{{ route('payments.alerts') }}" style="color:var(--primary)">Clear filters</a>
                                @else
                                    No students found!
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form      = document.getElementById('alertFilterForm');
    var searchInp = document.getElementById('alertSearch');
    var gradeEl   = document.getElementById('alertGrade');
    var tableRows = document.querySelectorAll('tbody tr');
    var countEl   = document.querySelector('.card-title');
    var totalCount = {{ $totalCount }};

    /* ── Client-side live search + grade filter ─────────────── */
    function filterRows() {
        var term  = searchInp ? searchInp.value.trim().toLowerCase() : '';
        var grade = gradeEl   ? gradeEl.value : '';
        var shown = 0;

        tableRows.forEach(function (row) {
            var text      = row.textContent.toLowerCase();
            var gradeCell = row.querySelector('td:nth-child(3)');
            var gradeText = gradeCell ? gradeCell.textContent.trim() : '';

            var matchTerm  = term  === '' || text.indexOf(term) !== -1;
            var matchGrade = grade === '' || gradeText === 'Grade ' + grade;

            var match = matchTerm && matchGrade;
            row.style.display = match ? '' : 'none';
            if (match) shown++;
        });

        /* Update count */
        if (countEl) {
            countEl.innerHTML = 'All Students <span style="font-size:13px;font-weight:400;color:var(--text-muted);margin-left:6px">(' +
                shown + (shown !== totalCount ? ' of ' + totalCount : '') + ')</span>';
        }
    }

    /* Live search on input */
    if (searchInp) {
        searchInp.addEventListener('input', filterRows);
        searchInp.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { searchInp.value = ''; filterRows(); }
            if (e.key === 'Enter')  { e.preventDefault(); filterRows(); }
        });
        if (searchInp.value) filterRows();
    }

    /* Grade dropdown — client-side filter (no reload needed) */
    if (gradeEl) {
        /* Remove the onchange=submit from server-rendered HTML */
        gradeEl.removeAttribute('onchange');
        gradeEl.addEventListener('change', filterRows);
    }

    /* Status pills (All/Overdue/Closely/Upcoming) still do server-side filter
       since they need to rebuild the sorted list */
});
</script>
@endsection
