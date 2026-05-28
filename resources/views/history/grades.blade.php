@extends('layouts.app')
@section('title','Grade Levels - ' . $date->format('F Y'))
@section('page-title','Grade Levels - ' . $date->format('F Y'))
@section('topbar-actions')
    <a href="{{ route('history.monthly') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Months</a>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-layer-group"></i> Select a Grade Level</div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px">
            @forelse($gradeLevels as $grade)
                <a href="{{ route('history.students', [$yearMonth, $grade]) }}" class="btn btn-outline" style="justify-content:center;flex-direction:column;gap:8px;padding:20px;text-align:center">
                    <i class="fas fa-graduation-cap" style="font-size:28px;color:var(--primary)"></i>
                    <div style="font-weight:600">Grade {{ $grade }}</div>
                </a>
            @empty
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fas fa-layer-group"></i>
                    <p>No grade levels found</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
