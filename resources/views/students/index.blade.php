@extends('layouts.app')
@section('title', 'Students')
@section('page-title', 'Students')
@section('topbar-actions')
    <a href="{{ route('students.export.csv', request()->query()) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-download" aria-hidden="true"></i> <span>CSV</span>
    </a>
    <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus" aria-hidden="true"></i> <span>Enroll Student</span>
    </a>
@endsection

@section('styles')
<style>
/* ── Grade cards ─────────────────────────────────── */
.grade-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius); padding:18px 16px; text-align:center;
    cursor:pointer; transition:box-shadow 0.15s, transform 0.15s, border-color 0.15s;
    display:flex; flex-direction:column; align-items:center; gap:6px;
}
.grade-card:hover, .grade-card.active {
    box-shadow:var(--shadow-md); transform:translateY(-2px);
    border-color:var(--primary);
}
.grade-card.active { background:var(--primary-50); }
.grade-card-icon  { font-size:28px; color:var(--primary); }
.grade-card-name  { font-size:14px; font-weight:700; color:var(--text-primary); }
.grade-card-count { font-size:11px; color:var(--text-muted); }

/* ── Class type tabs ─────────────────────────────── */
.class-type-tabs {
    display:flex; gap:8px; flex-wrap:wrap;
}
.class-type-tab {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; border-radius:8px; border:1px solid var(--border);
    font-size:13px; font-weight:600; text-decoration:none;
    color:var(--text-secondary); background:var(--bg-card);
    transition:all 0.15s;
}
.class-type-tab:hover { background:var(--bg-hover); color:var(--text-primary); }
.class-type-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
.class-type-tab.active-weekday { background:var(--success); color:#fff; border-color:var(--success); }
.class-type-tab.active-weekend { background:#7c3aed; color:#fff; border-color:#7c3aed; }

/* ── Sort bar ────────────────────────────────────── */
.sort-bar {
    display:flex; align-items:center; gap:12px;
    flex-wrap:wrap; padding:12px 16px;
    border-bottom:1px solid var(--border);
    background:var(--bg-muted);
}
.sort-menu-item { transition:background 0.12s; }
.sort-menu-item:last-child { border-bottom:none !important; }
.sort-menu-item:hover { background:var(--bg-hover) !important; color:var(--text-primary) !important; }
.sort-menu-active { color:var(--primary) !important; background:var(--primary-50) !important; }

/* ── Avatar ──────────────────────────────────────── */
.stu-avatar {
    width:34px; height:34px; background:var(--primary-light);
    border-radius:50%; display:flex; align-items:center; justify-content:center;
    font-weight:700; color:var(--primary); font-size:12px; flex-shrink:0;
}
</style>
@endsection

@section('content')

{{-- ── Class Type Filter ────────────────────────────────────── --}}
<div class="card" style="margin-bottom:14px">
    <div class="card-body" style="padding:14px 16px">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <span style="font-size:13px;font-weight:700;color:var(--text-muted)">
                <i class="fas fa-filter" aria-hidden="true"></i> {{ __('app.all_classes') }}:
            </span>
            <div class="class-type-tabs">
                <a href="{{ route('students.index', array_merge(request()->query(), ['class_type' => '', 'sort' => $sortBy])) }}"
                   class="class-type-tab {{ $classType === '' ? 'active' : '' }}">
                    <i class="fas fa-users"></i> {{ __('app.all_classes') }}
                    <span style="font-size:11px;opacity:0.8">({{ $allStudents->count() }})</span>
                </a>
                @php
                    $weekdayCount = \App\Models\Student::where(function ($q) { $q->where('status','active')->orWhereNull('status'); })->where('time_type','like','mon-fri%')->count();
                    $weekendCount = \App\Models\Student::where(function ($q) { $q->where('status','active')->orWhereNull('status'); })->where('time_type','like','sat-sun%')->count();
                @endphp
                <a href="{{ route('students.index', array_merge(request()->query(), ['class_type' => 'weekday', 'sort' => $sortBy])) }}"
                   class="class-type-tab {{ $classType === 'weekday' ? 'active-weekday' : '' }}">
                    <i class="fas fa-calendar-week"></i> {{ __('app.weekday') }}
                    <span style="font-size:11px;opacity:0.8">({{ $weekdayCount }})</span>
                </a>
                <a href="{{ route('students.index', array_merge(request()->query(), ['class_type' => 'weekend', 'sort' => $sortBy])) }}"
                   class="class-type-tab {{ $classType === 'weekend' ? 'active-weekend' : '' }}">
                    <i class="fas fa-calendar-day"></i> {{ __('app.weekend') }}
                    <span style="font-size:11px;opacity:0.8">({{ $weekendCount }})</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Grade Cards ──────────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-graduation-cap" aria-hidden="true"></i>
            Select a Grade
        </div>
        <div style="font-size:13px;color:var(--text-muted)">
            {{ $allStudents->count() }} students total
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px">
            @forelse($grades as $grade)
            <div class="grade-card" id="grade-card-{{ $grade }}"
                 data-grade="{{ $grade }}"
                 role="button" tabindex="0"
                 aria-label="Filter by Grade {{ $grade }}">
                <i class="fas fa-users grade-card-icon" aria-hidden="true"></i>
                <div class="grade-card-name">Grade {{ $grade }}</div>
                <div class="grade-card-count">
                    {{ $byGrade->get($grade, collect())->count() }} students
                </div>
            </div>
            @empty
            <div class="empty-state" style="grid-column:1/-1">
                <i class="fas fa-user-graduate" aria-hidden="true"></i>
                <p>No students yet. <a href="{{ route('students.create') }}" style="color:var(--primary)">Enroll one</a></p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── All Students Table ───────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-title" id="tableTitle">
            All Students ({{ $allStudents->count() }})
        </div>
    </div>

    {{-- Sort + Search bar --}}
    <div class="sort-bar">
        {{-- Search on the left --}}
        <input type="text" id="studentSearch" class="form-control"
               style="max-width:260px"
               placeholder="Search name, ID, subject…"
               autocomplete="off" aria-label="Search students">

        {{-- Spacer --}}
        <div style="flex:1"></div>

        {{-- Sort button + dropdown --}}
        <div style="position:relative" id="sortDropdown">
            <button type="button" id="sortBtn"
                    class="btn btn-outline btn-sm"
                    style="gap:8px;min-width:120px;justify-content:space-between"
                    aria-haspopup="true" aria-expanded="false">
                <span>
                    <i class="fas fa-sort" aria-hidden="true"></i>
                    Sort:
                    <strong id="sortLabel">{{ [
                        'newest'=>'Newest','oldest'=>'Oldest',
                        'az'=>'A → Z','za'=>'Z → A',
                        'enroll'=>'Enrollment','grade'=>'Grade'
                    ][$sortBy] ?? 'Oldest' }}</strong>
                </span>
                <i class="fas fa-chevron-down" id="sortChevron" style="font-size:10px;transition:transform 0.2s" aria-hidden="true"></i>
            </button>

            {{-- Dropdown menu --}}
            <div id="sortMenu"
                 style="display:none;position:absolute;right:0;top:calc(100% + 6px);
                        background:var(--bg-card);border:1px solid var(--border);
                        border-radius:var(--radius);box-shadow:var(--shadow-lg);
                        min-width:160px;z-index:500;overflow:hidden">
                @foreach([
                    'newest' => ['label'=>'Newest',      'icon'=>'fa-clock-rotate-left'],
                    'oldest' => ['label'=>'Oldest',      'icon'=>'fa-clock'],
                    'az'     => ['label'=>'A → Z',       'icon'=>'fa-arrow-down-a-z'],
                    'za'     => ['label'=>'Z → A',       'icon'=>'fa-arrow-up-z-a'],
                    'enroll' => ['label'=>'Enrollment',  'icon'=>'fa-calendar'],
                    'grade'  => ['label'=>'Grade',       'icon'=>'fa-layer-group'],
                ] as $key => $opt)
                <a href="{{ route('students.index', ['sort' => $key, 'class_type' => $classType]) }}"
                   class="sort-menu-item {{ $sortBy === $key ? 'sort-menu-active' : '' }}"
                   style="display:flex;align-items:center;gap:10px;padding:10px 14px;
                          text-decoration:none;color:var(--text-primary);font-size:13px;font-weight:500;
                          border-bottom:1px solid var(--border);transition:background 0.1s">
                    <i class="fas {{ $opt['icon'] }}" style="width:14px;color:var(--text-muted)" aria-hidden="true"></i>
                    {{ $opt['label'] }}
                    @if($sortBy === $key)
                        <i class="fas fa-check" style="margin-left:auto;color:var(--primary);font-size:11px" aria-hidden="true"></i>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrap">
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Grade</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Come From</th>
                    <th>Time Slot</th>
                    <th>Monthly Fee</th>
                    <th>Discount</th>
                    <th>Enrolled</th>
                    <th>Payments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allStudents as $student)
                @php
                    $search = strtolower(implode(' ', array_filter([
                        $student->full_name,
                        $student->student_id,
                        $student->subject,
                        $student->come_from,
                        'grade '.$student->year_level,
                        $student->time_type,
                    ])));
                @endphp
                <tr data-search="{{ $search }}" data-grade="{{ $student->year_level }}">
                    <td>
                        <div>
                            <div style="font-weight:600;color:var(--text-heading)">{{ $student->full_name }}</div>
                            @if($student->status !== 'active')
                                <span class="badge badge-gray" style="font-size:10px">{{ ucfirst($student->status ?? 'inactive') }}</span>
                            @endif
                        </div>
                    </td>
                    <td><span class="mono" style="font-size:12px;color:var(--text-secondary)">{{ $student->student_id }}</span></td>
                    <td>
                        <span class="badge badge-primary">Grade {{ $student->year_level }}</span>
                    </td>
                    <td>
                        @if(str_starts_with($student->time_type ?? '', 'sat-sun'))
                            <span class="badge" style="background:rgba(124,58,237,0.12);color:#7c3aed">{{ __('app.weekend') }}</span>
                        @else
                            <span class="badge badge-success">{{ __('app.weekday') }}</span>
                        @endif
                    </td>
                    <td style="color:var(--text-secondary)">{{ $student->subject ?? '—' }}</td>
                    <td style="color:var(--text-secondary)">{{ $student->come_from ?? '—' }}</td>
                    <td style="color:var(--text-secondary);font-size:12px">{{ $student->time_type ?? '—' }}</td>
                    <td style="color:var(--text-secondary);font-size:12px">${{ number_format($student->monthly_fee, 2) }}</td>
                    <td style="color:var(--text-secondary);font-size:12px">
                        @if($student->discount > 0)
                            <span class="badge" style="background:rgba(34,197,94,0.12);color:var(--success)">{{ $student->discount }}%</span>
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--text-muted)">{{ $student->enrollment_date->format('M d, Y') }}</td>
                    <td>
                        <span class="badge {{ $student->payments_count > 0 ? 'badge-success' : 'badge-gray' }}">
                            {{ $student->payments_count }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('students.show', $student) }}"
                               class="btn btn-icon btn-outline" title="View">
                                <i class="fas fa-eye" style="font-size:12px" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('students.edit', $student) }}"
                               class="btn btn-icon btn-outline" title="Edit">
                                <i class="fas fa-edit" style="font-size:12px" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('payments.create', ['student_id'=>$student->id]) }}"
                               class="btn btn-icon btn-outline" title="Add payment" style="color:var(--success)">
                                <i class="fas fa-plus" style="font-size:12px" aria-hidden="true"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-outline delete-btn"
                                    title="Delete" style="color:var(--danger)"
                                    data-action="{{ route('students.destroy', $student) }}"
                                    data-title="Delete Student"
                                    data-body="Delete {{ e($student->full_name) }}? All payments will also be deleted.">
                                <i class="fas fa-trash" style="font-size:12px" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12">
                    <div class="empty-state">
                        <i class="fas fa-user-graduate" aria-hidden="true"></i>
                        <p>No students found. <a href="{{ route('students.create') }}" style="color:var(--primary)">Enroll one</a></p>
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
    var searchInp  = document.getElementById('studentSearch');
    var allRows    = Array.prototype.slice.call(document.querySelectorAll('#studentsTable tbody tr[data-search]'));
    var titleEl    = document.getElementById('tableTitle');
    var totalCount = allRows.length;
    var activeGrade = null;

    /* ── Sort dropdown toggle ──────────────────────────── */
    var sortBtn  = document.getElementById('sortBtn');
    var sortMenu = document.getElementById('sortMenu');
    var chevron  = document.getElementById('sortChevron');

    if (sortBtn && sortMenu) {
        sortBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = sortMenu.style.display !== 'none';
            sortMenu.style.display = open ? 'none' : 'block';
            chevron.style.transform = open ? '' : 'rotate(180deg)';
            sortBtn.setAttribute('aria-expanded', String(!open));
        });
        /* Close when clicking outside */
        document.addEventListener('click', function () {
            sortMenu.style.display = 'none';
            chevron.style.transform = '';
            sortBtn.setAttribute('aria-expanded', 'false');
        });
        /* Keyboard close */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                sortMenu.style.display = 'none';
                chevron.style.transform = '';
                sortBtn.focus();
            }
        });
    }

    /* ── Grade card filter ─────────────────────────────── */
    window.filterGrade = function (grade) {
        var cards = document.querySelectorAll('.grade-card');
        if (activeGrade === grade) {
            activeGrade = null;
            cards.forEach(function (c) { c.classList.remove('active'); });
        } else {
            activeGrade = grade;
            cards.forEach(function (c) {
                c.classList.toggle('active', parseInt(c.dataset.grade) === grade);
            });
        }
        applyFilter();
        document.querySelector('.card:last-of-type').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // Event delegation for grade cards
    document.querySelector('.card-body').addEventListener('click', function (e) {
        var card = e.target.closest('.grade-card');
        if (card) {
            filterGrade(parseInt(card.dataset.grade));
        }
    });

    document.querySelectorAll('.grade-card').forEach(function (card) {
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                filterGrade(parseInt(card.dataset.grade));
            }
        });
    });

    /* ── Apply search + grade filter ─────────────────────── */
    function applyFilter() {
        var term  = searchInp ? searchInp.value.trim().toLowerCase() : '';
        var shown = 0;
        allRows.forEach(function (row) {
            var matchSearch = term === '' || row.dataset.search.indexOf(term) !== -1;
            var matchGrade  = activeGrade === null || parseInt(row.dataset.grade) === activeGrade;
            var show = matchSearch && matchGrade;
            row.style.display = show ? '' : 'none';
            if (show) shown++;
        });
        var gradeLabel = activeGrade !== null ? ' — Grade ' + activeGrade : '';
        titleEl.textContent = 'All Students' + gradeLabel +
            ' (' + (shown === totalCount ? totalCount : shown + ' of ' + totalCount) + ')';
    }

    /* Live search */
    if (searchInp) {
        searchInp.addEventListener('input', applyFilter);
        searchInp.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { searchInp.value = ''; applyFilter(); }
        });
    }
});
</script>
@endsection
