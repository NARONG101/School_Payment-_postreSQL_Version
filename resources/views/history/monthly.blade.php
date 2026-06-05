@extends('layouts.app')
@section('title','Monthly History')
@section('page-title','Monthly Payment History')
@section('styles')
<style>
.month-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius); padding:18px 14px; text-align:center;
    text-decoration:none; color:inherit; display:flex;
    flex-direction:column; align-items:center; gap:8px;
    transition:box-shadow 0.15s, transform 0.15s, border-color 0.15s;
    position:relative;
}
.month-card:hover {
    box-shadow:var(--shadow-md); transform:translateY(-2px);
    border-color:var(--primary);
}
.month-card:hover .month-icon { color:var(--primary-dark); }
.month-icon { font-size:32px; color:var(--primary); transition:color 0.15s; }
.month-label { font-weight:700; font-size:14px; color:var(--text-primary); }
.month-count {
    font-size:10px; color:var(--text-muted); font-weight:500;
}
.month-badge {
    position:absolute; top:10px; right:10px;
    background:var(--primary); color:#fff;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:20px;
    min-width:20px;
}
</style>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            Select a Month
        </div>
        <div style="font-size:13px;color:var(--text-muted)">{{ $months->count() }} months with payments</div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px">
            @forelse($months as $month)
                <a href="{{ route('history.month', $month['slug']) }}" class="month-card"
                   aria-label="View history for {{ $month['label'] }}, {{ $month['count'] ?? 0 }} payments">
                    <i class="fas fa-folder-open month-icon" aria-hidden="true"></i>
                    <span class="month-label">{{ $month['label'] }}</span>
                    <span class="month-count">{{ $month['count'] ?? 0 }} payment{{ ($month['count'] ?? 0) !== 1 ? 's' : '' }}</span>
                    @if(($month['count'] ?? 0) > 0)
                        <span class="month-badge">{{ $month['count'] }}</span>
                    @endif
                </a>
            @empty
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fas fa-calendar-times" aria-hidden="true"></i>
                    <p>No payment history found yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
