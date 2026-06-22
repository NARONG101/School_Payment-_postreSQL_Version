@extends('layouts.app')
@section('title', $date->format('F Y').' — Payment History')
@section('page-title', $date->format('F Y').' Payments')

@section('topbar-back')
    <button type="button" id="month-students-back-btn" class="btn btn-outline btn-sm"
            data-back-url="{{ route('history.monthly') }}">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection

@section('topbar-actions')
    <a href="{{ route('history.month.export.csv', $yearMonth) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-download" aria-hidden="true"></i> <span>CSV</span>
    </a>
@endsection

@section('styles')
<style>
/* ── Grade cards (same as students/index) ───────── */
.grade-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius); padding:18px 14px; text-align:center;
    cursor:pointer; transition:box-shadow .15s,transform .15s,border-color .15s;
    display:flex; flex-direction:column; align-items:center; gap:6px;
}
.grade-card:hover, .grade-card.active {
    box-shadow:var(--shadow-md); transform:translateY(-2px); border-color:var(--primary);
}
.grade-card.active { background:var(--primary-50); }
.grade-card-icon  { font-size:26px; color:var(--primary); }
.grade-card-name  { font-size:14px; font-weight:700; color:var(--text-primary); }
.grade-card-count { font-size:11px; color:var(--text-muted); }

/* ── Sort bar ────────────────────────────────────── */
.sort-bar {
    display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    padding:12px 16px; border-bottom:1px solid var(--border);
    background:var(--bg-muted);
}
.sort-menu-item {
    transition:background 0.12s;
    width:100%;
    text-align:left;
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 14px;
    border:none;
    color:var(--text-primary);
    font-size:13px;
    font-weight:500;
    cursor:pointer;
    border-bottom:1px solid var(--border);
}
.sort-menu-item:last-child { border-bottom:none !important; }
.sort-menu-item:hover { background:var(--bg-hover) !important; color:var(--text-primary) !important; }
.sort-menu-active { color:var(--primary) !important; background:var(--primary-50) !important; }
.sort-check {
    margin-left:auto;
    color:var(--primary);
    font-size:11px;
}
.sort-check-hidden {
    visibility:hidden;
}

/* ── Avatar ──────────────────────────────────────── */
.stu-avatar {
    width:34px; height:34px; background:var(--primary-light);
    border-radius:50%; display:flex; align-items:center; justify-content:center;
    font-weight:700; color:var(--primary); font-size:12px; flex-shrink:0;
}
</style>
@endsection

@section('content')

{{-- ── Grade Cards ──────────────────────────────────────── --}}
@if($gradeLevels->count() > 0)
<div class="card" style="margin-bottom:18px">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
            {{ $isCurrentMonth ? 'All Grades' : 'Grades with Payments' }}
        </div>
        <div style="font-size:13px;color:var(--text-muted)">
            {{ $students->count() }} {{ $isCurrentMonth ? 'students' : 'students paid' }} in {{ $date->format('F Y') }}
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px">
            {{-- All Grades Card --}}
            <div class="grade-card" id="hist-grade-card-all"
                 data-grade="all"
                 role="button" tabindex="0"
                 aria-label="Show All Grades">
                <i class="fas fa-layer-group grade-card-icon" aria-hidden="true"></i>
                <div class="grade-card-name">All Grades</div>
                <div class="grade-card-count">{{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}</div>
            </div>
            @foreach($gradeLevels as $grade)
            @php $gradeCount = $students->where('year_level', $grade)->count(); @endphp
            <div class="grade-card" id="hist-grade-card-{{ $grade }}"
                 data-grade="{{ $grade }}"
                 role="button" tabindex="0"
                 aria-label="Filter by Grade {{ $grade }}">
                <i class="fas fa-users grade-card-icon" aria-hidden="true"></i>
                <div class="grade-card-name">Grade {{ $grade }}</div>
                <div class="grade-card-count">{{ $gradeCount }} student{{ $gradeCount !== 1 ? 's' : '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Students Table ───────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title" id="histTableTitle">
            All Students ({{ $students->count() }})
        </div>
    </div>

    {{-- Sort bar --}}
    <div class="sort-bar">
        {{-- Search --}}
        <input type="text" id="histSearch" class="form-control"
               style="max-width:260px"
               placeholder="Search name, ID, subject…"
               autocomplete="off" aria-label="Search students">

        <div style="flex:1"></div>

        {{-- Sort dropdown --}}
        <div style="position:relative" id="histSortDropdown">
            <button type="button" id="histSortBtn"
                    class="btn btn-outline btn-sm"
                    style="gap:8px;min-width:150px;justify-content:space-between"
                    aria-haspopup="true" aria-expanded="false">
                <span>
                    <i class="fas fa-sort" aria-hidden="true"></i>
                    Sort: <strong id="histSortLabel">Newest First</strong>
                </span>
                <i class="fas fa-chevron-down" id="histSortChevron"
                   style="font-size:10px;transition:transform .2s" aria-hidden="true"></i>
            </button>
            <div id="histSortMenu"
                 style="display:none;position:absolute;right:0;top:calc(100% + 6px);
                        background:var(--bg-card);border:1px solid var(--border);
                        border-radius:var(--radius);box-shadow:var(--shadow-lg);
                        min-width:170px;z-index:500;overflow:hidden">
                @foreach([
                    'newest'  => ['label'=>'Newest First',  'icon'=>'fa-clock-rotate-left'],
                    'az'      => ['label'=>'A → Z',         'icon'=>'fa-arrow-down-a-z'],
                    'za'      => ['label'=>'Z → A',         'icon'=>'fa-arrow-up-z-a'],
                    'grade'   => ['label'=>'By Grade',      'icon'=>'fa-layer-group'],
                    'amount'  => ['label'=>'By Amount',     'icon'=>'fa-money-bill-wave'],
                ] as $k => $o)
                <button type="button"
                        class="sort-menu-item {{ $k === 'newest' ? 'sort-menu-active' : '' }}"
                        data-sort="{{ $k }}">
                    <i class="fas {{ $o['icon'] }}" style="width:14px;color:var(--text-muted)" aria-hidden="true"></i>
                    {{ $o['label'] }}
                    @if($k === 'newest')
                    <i class="fas fa-check sort-check" aria-hidden="true"></i>
                    @else
                    <i class="fas fa-check sort-check sort-check-hidden" aria-hidden="true"></i>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrap">
        <table id="histTable">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Grade</th>
                    <th>Subject</th>
                    <th>Time Slot</th>
                    <th>Paid On</th>
                    <th>For Month</th>
                    <th>Amount</th>
                    <th>Next Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="histBody">
                @forelse($students as $student)
                    @if($student->payments->isNotEmpty())
                        @foreach($student->payments as $payment)
                        <tr data-name="{{ strtolower($student->full_name) }}"
                            data-grade="{{ $student->year_level }}"
                            data-amount="{{ $payment->amount_paid }}"
                            data-paid-id="{{ $payment->id }}"
                            data-search="{{ strtolower(implode(' ', array_filter([
                                $student->full_name,
                                $student->student_id,
                                $student->subject ?? '',
                                'grade '.$student->year_level,
                            ]))) }}">
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="stu-avatar" aria-hidden="true">
                                        {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary)">
                                            {{ $student->full_name }}
                                            @if($student->gender)
                                            ({{ ucfirst($student->gender) }})
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono" style="font-size:12px;color:var(--text-secondary)">
                                    {{ $student->student_id }}
                                </span>
                            </td>
                            <td><span class="badge badge-primary">Grade {{ $student->year_level }}</span></td>
                            <td style="color:var(--text-secondary)">{{ $student->subject ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--text-secondary)">{{ $payment->time_type ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--text-muted)">
                                {{ $payment->payment_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td style="font-size:12px;color:var(--text-muted)">
                                {{ $payment->due_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td style="font-weight:700;color:var(--success)">
                                ${{ number_format($payment->amount_paid, 2) }}
                            </td>
                            <td style="font-size:12px;color:var(--text-muted)">
                                {{ $payment->next_payment_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="{{ route('payments.show', $payment) }}"
                                       class="btn btn-icon btn-outline" title="View payment">
                                        <i class="fas fa-eye" style="font-size:12px" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('payments.receipt', $payment) }}"
                                       class="btn btn-icon btn-outline" title="Receipt"
                                       target="_blank" rel="noopener">
                                        <i class="fas fa-file-pdf" style="font-size:12px" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr data-name="{{ strtolower($student->full_name) }}"
                            data-grade="{{ $student->year_level }}"
                            data-amount="0"
                            data-paid-id="0"
                            data-search="{{ strtolower(implode(' ', array_filter([
                                $student->full_name,
                                $student->student_id,
                                $student->subject ?? '',
                                'grade '.$student->year_level,
                            ]))) }}">
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="stu-avatar" aria-hidden="true">
                                        {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary)">
                                            {{ $student->full_name }}
                                            @if($student->gender)
                                            ({{ ucfirst($student->gender) }})
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono" style="font-size:12px;color:var(--text-secondary)">
                                    {{ $student->student_id }}
                                </span>
                            </td>
                            <td><span class="badge badge-primary">Grade {{ $student->year_level }}</span></td>
                            <td style="color:var(--text-secondary)">{{ $student->subject ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--text-secondary)">{{ $student->time_type ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--danger)"><strong>Not Paid Yet</strong></td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $date->format('M Y') }}</td>
                            <td style="font-weight:700;color:var(--text-muted)">$0.00</td>
                            <td style="font-size:12px;color:var(--text-muted)">{{ $student->payments->first()?->next_payment_date?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="{{ route('payments.create') }}?student_id={{ $student->id }}"
                                       class="btn btn-icon btn-primary" title="Record Payment">
                                        <i class="fas fa-plus" style="font-size:12px" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('students.show', $student) }}"
                                       class="btn btn-icon btn-outline" title="View Student">
                                        <i class="fas fa-user" style="font-size:12px" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                <tr><td colspan="10">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times" aria-hidden="true"></i>
                        <p>No students for {{ $date->format('F Y') }}</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInp   = document.getElementById('histSearch');
    var tbody       = document.getElementById('histBody');
    var titleEl     = document.getElementById('histTableTitle');
    var sortBtn     = document.getElementById('histSortBtn');
    var sortMenu    = document.getElementById('histSortMenu');
    var sortChevron = document.getElementById('histSortChevron');
    var sortLabel   = document.getElementById('histSortLabel');
    var menuItems   = Array.prototype.slice.call(document.querySelectorAll('#histSortMenu [data-sort]'));
    var allRows     = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-name]'));
    var totalCount  = allRows.length;
    var activeGrade = null;
    var currentSort = 'newest';

    // Set "All Grades" as active by default
    var allGradesCard = document.getElementById('hist-grade-card-all');
    if (allGradesCard) {
        allGradesCard.classList.add('active');
    }

    /* ── Back button handler ────────────────────────────── */
    var backBtn = document.getElementById('month-students-back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            var backUrl = this.dataset.backUrl;
            if (history.length > 1) {
                history.back();
            } else if (backUrl) {
                window.location = backUrl;
            }
        });
    }

    /* ── Grade card click handlers ─────────────────────────── */
    var gradeCards = document.querySelectorAll('[id^="hist-grade-card-"]');
    gradeCards.forEach(function(card) {
        card.addEventListener('click', function() {
            var grade = this.dataset.grade;
            histFilterGrade(grade);
        });
        // Optional: handle keyboard events for accessibility
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                var grade = this.dataset.grade;
                histFilterGrade(grade);
            }
        });
    });

    /* ── Sort dropdown toggle ────────────────────────────── */
    if (sortBtn) {
        sortBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = sortMenu.style.display !== 'none';
            sortMenu.style.display = open ? 'none' : 'block';
            sortChevron.style.transform = open ? '' : 'rotate(180deg)';
            sortBtn.setAttribute('aria-expanded', String(!open));
        });
        document.addEventListener('click', function () {
            sortMenu.style.display = 'none';
            sortChevron.style.transform = '';
            sortBtn.setAttribute('aria-expanded', 'false');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { sortMenu.style.display = 'none'; sortBtn.focus(); }
        });
    }

    /* ── Sort items ──────────────────────────────────────── */
    menuItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.stopPropagation();
            var key = this.dataset.sort;
            currentSort = key;

            /* Update UI */
            menuItems.forEach(function (mi) {
                mi.classList.remove('sort-menu-active');
                var chk = mi.querySelector('.sort-check');
                if (chk) chk.classList.add('sort-check-hidden');
            });
            this.classList.add('sort-menu-active');
            var myChk = this.querySelector('.sort-check');
            if (myChk) myChk.classList.remove('sort-check-hidden');

            sortLabel.textContent = this.textContent.trim().replace(/\s+/g, ' ');
            sortMenu.style.display = 'none';
            sortChevron.style.transform = '';

            applySort();
        });
    });

    function applySort() {
        var visible = allRows.filter(function (r) { return r.style.display !== 'none'; });
        var sorted  = visible.sort(function (a, b) {
            switch (currentSort) {
                case 'newest':  return parseInt(b.dataset.paidId || 0) - parseInt(a.dataset.paidId || 0);
                case 'az':      return a.dataset.name.localeCompare(b.dataset.name);
                case 'za':      return b.dataset.name.localeCompare(a.dataset.name);
                case 'grade':   return parseInt(b.dataset.grade) - parseInt(a.dataset.grade);
                case 'amount':  return parseFloat(b.dataset.amount) - parseFloat(a.dataset.amount);
                default:        return 0;
            }
        });
        sorted.forEach(function (row) { tbody.appendChild(row); });
    }

    /* ── Grade card filter ───────────────────────────────── */
    function histFilterGrade(grade) {
        var cards = document.querySelectorAll('[id^="hist-grade-card-"]');
        if (grade === 'all') {
            activeGrade = null;
            cards.forEach(function (c) {
                c.classList.remove('active');
                if (c.dataset.grade === 'all') {
                    c.classList.add('active');
                }
            });
        } else if (activeGrade === grade) {
            activeGrade = null;
            cards.forEach(function (c) { c.classList.remove('active'); });
        } else {
            activeGrade = grade;
            cards.forEach(function (c) {
                c.classList.toggle('active', c.dataset.grade === grade);
            });
        }
        applyFilter();
        document.querySelector('.card:last-of-type').scrollIntoView({ behavior:'smooth', block:'start' });
    };

    // Remove the old redundant keydown listener since we already added them above!

    /* ── Apply search + grade filter ─────────────────────── */
    function applyFilter() {
        var term  = searchInp ? searchInp.value.trim().toLowerCase() : '';
        var shown = 0;
        allRows.forEach(function (row) {
            var matchSearch = term === '' || row.dataset.search.indexOf(term) !== -1;
            var matchGrade  = activeGrade === null || parseInt(row.dataset.grade) === parseInt(activeGrade);
            var show = matchSearch && matchGrade;
            row.style.display = show ? '' : 'none';
            if (show) shown++;
        });
        var gLabel = activeGrade !== null ? ' — Grade ' + activeGrade : '';
        titleEl.textContent = 'All Students' + gLabel +
            ' (' + (shown === totalCount ? totalCount : shown + ' of ' + totalCount) + ')';
        applySort();
    }

    /* ── Live search ─────────────────────────────────────── */
    if (searchInp) {
        searchInp.addEventListener('input', applyFilter);
        searchInp.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { searchInp.value = ''; applyFilter(); }
        });
    }

    /* ── Default: newest first ───────────────────────────── */
    applySort();
});
</script>
@endsection
