@extends('layouts.app')
@section('title', 'Home')
@section('page-title', 'Home')
@section('content')
<div class="card" style="max-width:600px;margin:0 auto">
    <div class="card-body" style="text-align:center;padding:40px">
        <i class="fas fa-graduation-cap" style="font-size:48px;color:var(--primary);margin-bottom:16px" aria-hidden="true"></i>
        <h2 style="font-size:22px;font-weight:800;color:var(--text-heading);margin-bottom:8px">Welcome to CK Takhmao School</h2>
        <p style="color:var(--text-secondary);margin-bottom:24px">You are logged in successfully.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="fas fa-th-large" aria-hidden="true"></i> Go to Dashboard
        </a>
    </div>
</div>
@endsection
