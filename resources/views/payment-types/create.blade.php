@extends('layouts.app')
@section('title','Create Payment Type')
@section('page-title','Create Payment Type')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            onclick="history.length>1?history.back():window.location='{{ route('payment-types.index') }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('content')
<div style="max-width:600px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-tag" style="color:var(--primary)" aria-hidden="true"></i> New Payment Type</div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('payment-types.store') }}" novalidate>
            @csrf
            <div class="form-group">
                <label class="form-label" for="name">Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required maxlength="100">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description"
                          class="form-control @error('description') is-invalid @enderror"
                          rows="3" maxlength="500">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="amount">Amount ($) <span style="color:var(--danger)">*</span></label>
                <input type="number" id="amount" name="amount" step="0.01" min="0"
                       class="form-control @error('amount') is-invalid @enderror"
                       value="{{ old('amount', 0) }}" required>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--primary)">
                    <span style="font-size:14px;font-weight:600;color:var(--text-secondary)">Active</span>
                </label>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Save</button>
                <a href="{{ route('payment-types.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
