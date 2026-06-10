@extends('layouts.app')
@section('title','Edit Payment')
@section('page-title','Edit Payment')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            onclick="history.length>1?history.back():window.location='{{ route('payments.show', $payment) }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
@endsection
@section('content')
<div style="max-width:780px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title">Update Payment — <span class="mono" style="color:var(--primary)">{{ $payment->receipt_number }}</span></div>
    </div>
    <div style="padding:14px 20px;background:var(--bg-muted);border-bottom:1px solid var(--border)">
        <div style="font-weight:600;color:var(--text-primary)">{{ $payment->student->full_name }}</div>
    </div>
    <div class="card-body">
        <form action="{{ route('payments.update', $payment) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="amount_due">Monthly Fee</label>
                    <input type="number" id="amount_due" name="amount_due" step="0.01" class="form-control @error('amount_due') is-invalid @enderror" 
                           value="{{ old('amount_due', number_format($payment->amount_due,2,'.','')) }}" min="0">
                    @error('amount_due')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="admin_fee">Admin Fee</label>
                    <input type="number" id="admin_fee" name="admin_fee" step="0.01" class="form-control @error('admin_fee') is-invalid @enderror" 
                           value="{{ old('admin_fee', number_format($payment->admin_fee ?? 0,2,'.','')) }}" min="0">
                    @error('admin_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="discount">Discount (%)</label>
                    <input type="number" id="discount" name="discount" step="0.01" class="form-control @error('discount') is-invalid @enderror" 
                           value="{{ old('discount', number_format($payment->discount ?? 0,2,'.','')) }}" min="0" max="100">
                    @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount_paid">Total Amount Paid</label>
                    <input type="number" id="amount_paid" name="amount_paid" step="0.01" class="form-control @error('amount_paid') is-invalid @enderror" 
                           value="{{ old('amount_paid', number_format($payment->amount_paid,2,'.','')) }}" min="0">
                    @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="payment_date">Payment Date</label>
                    <input type="date" id="payment_date" name="payment_date" class="form-control"
                           value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="deadline_date">Deadline Date <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="deadline_date" name="deadline_date" class="form-control"
                           value="{{ old('deadline_date', $payment->deadline_date->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="time_type">Time Type <span style="color:var(--danger)">*</span></label>
                    <select id="time_type" name="time_type" class="form-control @error('time_type') is-invalid @enderror" required>
                        <option value="">-- Select Time Slot --</option>
                        @foreach(['mon-fri 7:00-9:00','mon-fri 9:00-11:00','mon-fri 1:00-3:00','mon-fri 3:00-5:00','mon-fri 5:30-7:30','sat-sun 7:00-11:00','sat-sun 1:00-5:00'] as $slot)
                        <option value="{{ $slot }}" {{ old('time_type',$payment->time_type)===$slot?'selected':'' }}>{{ $slot }}</option>
                        @endforeach
                    </select>
                    @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="next_payment_date">Next Payment Date</label>
                    <input type="date" id="next_payment_date" name="next_payment_date" class="form-control"
                           value="{{ old('next_payment_date', $payment->next_payment_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method <span style="color:var(--danger)">*</span></label>
                    <select id="payment_method" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">-- Select --</option>
                        <option value="cash"          {{ old('payment_method',$payment->payment_method)==='cash'?'selected':'' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method',$payment->payment_method)==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="photo">Payment Photo</label>
                    <input type="file" id="photo" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($payment->photo)
                    <div style="margin-top:8px">
                        <img src="{{ asset('storage/'.$payment->photo) }}" alt="Current photo"
                             style="max-width:160px;border-radius:8px;border:1px solid var(--border)">
                    </div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control">{{ old('notes', $payment->notes) }}</textarea>
            </div>
            <div style="display:flex;gap:10px;padding-top:6px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Update Payment</button>
                <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
