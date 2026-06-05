@extends('layouts.app')
@section('title','Grade Levels — '.$date->format('F Y'))
@section('page-title','Grade Levels — '.$date->format('F Y'))
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm" onclick="history.length>1?history.back():window.location='{{ route('history.monthly') }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('styles')
<style>
.grade-hist-btn {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius); padding:20px; text-align:center;
    text-decoration:none; color:inherit; display:flex;
    flex-direction:column; align-items:center; gap:8px;
    transition:box-shadow 0.15s, transform 0.15s, border-color 0.15s;
}
.grade-hist-btn:hover {
    box-shadow:var(--shadow-md); transform:translateY(-2px);
    border-color:var(--primary); color:var(--primary);
}
.grade-hist-btn i { font-size:26px; color:var(--primary); }
.grade-hist-btn span { font-weight:600; font-size:14px; }
</style>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-layer-group" aria-hidden="true"></i> Select a Grade Level</div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px">
            @forelse($gradeLevels as $grade)
                <a href="{{ route('history.students', [$yearMonth, $grade]) }}" class="grade-hist-btn"
                   aria-label="View Grade {{ $grade }}">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <span>Grade {{ $grade }}</span>
                </a>
            @empty
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    <p>No grade levels found</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
