@extends('layouts.app')
@section('title','Edit Payment')
@section('page-title','Edit Payment')
@section('content')
<div style="max-width:780px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title">Update Payment — <span style="font-family:'JetBrains Mono',monospace;color:var(--primary)">{{ $payment->receipt_number }}</span></div>
    </div>
    <div style="padding:16px 20px;background:var(--gray-50);border-bottom:1px solid var(--gray-200)">
        <div style="font-weight:600">{{ $payment->student->full_name }}</div>
        <div style="font-size:12px;color:var(--gray-500)">{{ $payment->paymentType?->name ?? '—' }} — Amount Due: <strong>${{ number_format($payment->amount_due,2) }}</strong></div>
    </div>
    <div class="card-body">
        <form action="{{ route('payments.update', $payment) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Monthly Fee</label>
                    <input type="number" class="form-control" value="{{ number_format($payment->amount_due, 2) }}" readonly style="background:var(--gray-50);font-weight:700">
                </div>
                <div class="form-group">
                    <label class="form-label">Admin Fee ($20)</label>
                    <input type="number" class="form-control" value="{{ number_format($payment->admin_fee ?? 20, 2) }}" readonly style="background:var(--gray-50);font-weight:700">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Total Amount Paid</label>
                <input type="number" class="form-control" value="{{ number_format($payment->amount_paid, 2) }}" readonly style="background:var(--gray-50);font-weight:700">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control"
                           value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Deadline Date *</label>
                    <input type="date" name="deadline_date" class="form-control"
                           value="{{ old('deadline_date', $payment->deadline_date->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Time Type *</label>
                    <select name="time_type" class="form-control @error('time_type') is-invalid @enderror" required>
                        <option value="">-- Select Time Slot --</option>
                        <option value="mon-fri 7:00-9:00" {{ old('time_type', $payment->time_type)=='mon-fri 7:00-9:00'?'selected':'' }}>Mon-Fri 7:00-9:00</option>
                        <option value="mon-fri 9:00-11:00" {{ old('time_type', $payment->time_type)=='mon-fri 9:00-11:00'?'selected':'' }}>Mon-Fri 9:00-11:00</option>
                        <option value="mon-fri 1:00-3:00" {{ old('time_type', $payment->time_type)=='mon-fri 1:00-3:00'?'selected':'' }}>Mon-Fri 1:00-3:00</option>
                        <option value="mon-fri 3:00-5:00" {{ old('time_type', $payment->time_type)=='mon-fri 3:00-5:00'?'selected':'' }}>Mon-Fri 3:00-5:00</option>
                        <option value="mon-fri 5:30-7:30" {{ old('time_type', $payment->time_type)=='mon-fri 5:30-7:30'?'selected':'' }}>Mon-Fri 5:30-7:30</option>
                        <option value="sat-sun 7:00-11:00" {{ old('time_type', $payment->time_type)=='sat-sun 7:00-11:00'?'selected':'' }}>Sat-Sun 7:00-11:00</option>
                        <option value="sat-sun 1:00-5:00" {{ old('time_type', $payment->time_type)=='sat-sun 1:00-5:00'?'selected':'' }}>Sat-Sun 1:00-5:00</option>
                    </select>
                    @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Next Payment Date</label>
                    <input type="date" name="next_payment_date" class="form-control"
                           value="{{ old('next_payment_date', $payment->next_payment_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="cash" {{ old('payment_method', $payment->payment_method)=='cash'?'selected':'' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $payment->payment_method)=='bank_transfer'?'selected':'' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Photo</label>
                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($payment->photo)
                        <div style="margin-top:8px">
                            <img src="{{ asset('storage/' . $payment->photo) }}" alt="Payment Photo" style="max-width:200px;border-radius:8px">
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control">{{ old('notes', $payment->notes) }}</textarea>
            </div>
            <div style="display:flex;gap:12px;padding-top:8px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Payment</button>
                <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection