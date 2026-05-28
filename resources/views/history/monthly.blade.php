@extends('layouts.app')
@section('title','Monthly History')
@section('page-title','Monthly Payment History')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-calendar-alt"></i> Select a Month</div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px">
            @forelse($months as $month)
                <a href="{{ route('history.month', $month['slug']) }}" class="btn btn-outline" style="justify-content:center;flex-direction:column;gap:8px;padding:20px;text-align:center">
                    <i class="fas fa-folder-open" style="font-size:32px;color:var(--primary)"></i>
                    <div style="font-weight:600">{{ $month['label'] }}</div>
                </a>
            @empty
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fas fa-calendar-times"></i>
                    <p>No payment history found yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
