@extends('layouts.app')
@section('title','Edit Payment Type')
@section('page-title','Edit Payment Type')
@section('topbar-actions')
    <a href="{{ route('payment-types.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
@endsection
@section('content')
<div class="card" style="max-width:600px;margin:0 auto">
    <div class="card-body">
        <form method="POST" action="{{ route('payment-types.update', $paymentType) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $paymentType->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $paymentType->description) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Amount ($)</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-control" value="{{ old('amount', $paymentType->amount) }}" required>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paymentType->is_active) ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>
            <div style="display:flex;gap:10px;margin-top:24px">
                <a href="{{ route('payment-types.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
