@extends('layouts.app')
@section('title', 'Students')
@section('page-title', 'Students')
@section('topbar-actions')
    <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus"></i> Enroll Student
    </a>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-graduation-cap"></i> Select a Grade</div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px">
            @forelse($grades as $grade)
                @php
                    $count = $students->has($grade) ? $students[$grade]->count() : 0;
                @endphp
                <a href="#grade-{{ $grade }}" class="btn btn-outline grade-folder" data-grade="{{ $grade }}" style="justify-content:center;flex-direction:column;gap:8px;padding:20px;text-align:center">
                    <i class="fas fa-folder-open" style="font-size:32px;color:var(--primary)"></i>
                    <div style="font-weight:600">Grade {{ $grade }}</div>
                    <div style="font-size:12px;color:var(--gray-500)">{{ $count }} students</div>
                </a>
            @empty
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fas fa-user-graduate"></i>
                    <p>No grades found</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@foreach($grades as $grade)
    @if($students->has($grade))
        <div id="grade-section-{{ $grade }}" class="grade-section" style="display:none;margin-top:20px">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-sm btn-icon btn-outline back-btn" style="margin-right:12px">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="card-title"><i class="fas fa-folder-open"></i> Grade {{ $grade }} ({{ $students[$grade]->count() }} students)</div>
                </div>
                <div class="card-body">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>ID</th>
                                    <th>Come From</th>
                                    <th>Subject</th>
                                    <th>Enrolled</th>
                                    <th>Payments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students[$grade] as $student)
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:10px">
                                                <div style="width:36px;height:36px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);font-size:13px;flex-shrink:0">
                                                    {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight:600">{{ $student->full_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span style="font-family:'JetBrains Mono',monospace;font-size:12px">{{ $student->student_id }}</span></td>
                                        <td>{{ $student->come_from ?? '-' }}</td>
                                        <td>{{ $student->subject ?? '-' }}</td>
                                        <td style="font-size:12px">{{ $student->enrollment_date->format('M d, Y') }}</td>
                                        <td><span class="badge badge-primary">{{ $student->payments_count }} records</span></td>
                                        <td>
                                            <div style="display:flex;gap:4px">
                                                <a href="{{ route('students.show', $student) }}" class="btn btn-icon btn-outline" title="View"><i class="fas fa-eye" style="font-size:12px"></i></a>
                                                <a href="{{ route('students.edit', $student) }}" class="btn btn-icon btn-outline" title="Edit"><i class="fas fa-edit" style="font-size:12px"></i></a>
                                                <a href="{{ route('payments.create', ['student_id'=>$student->id]) }}" class="btn btn-icon btn-outline" title="Add Payment" style="color:var(--success)"><i class="fas fa-plus" style="font-size:12px"></i></a>
                                                <button type="button" class="btn btn-icon btn-outline delete-btn" title="Delete" style="color:var(--danger)" 
                                                        data-action="{{ route('students.destroy', $student) }}"
                                                        data-title="Delete Student"
                                                        data-body="Are you sure you want to delete {{ $student->full_name }}? This action cannot be undone and all associated payments will also be deleted.">
                                                    <i class="fas fa-trash" style="font-size:12px"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainCard = document.querySelector('.card');
    const gradeFolders = document.querySelectorAll('.grade-folder');
    const gradeSections = document.querySelectorAll('.grade-section');
    const backBtns = document.querySelectorAll('.back-btn');

    function showGrade(grade) {
        mainCard.style.display = 'none';
        document.querySelectorAll('.grade-section').forEach(sec => sec.style.display = 'none');
        const target = document.getElementById('grade-section-' + grade);
        if (target) target.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showMain() {
        mainCard.style.display = 'block';
        document.querySelectorAll('.grade-section').forEach(sec => sec.style.display = 'none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    gradeFolders.forEach(folder => {
        folder.addEventListener('click', function(e) {
            e.preventDefault();
            showGrade(this.dataset.grade);
        });
    });

    backBtns.forEach(btn => {
        btn.addEventListener('click', showMain);
    });
});
</script>
@endsection
