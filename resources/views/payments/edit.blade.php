@extends('layouts.app')
@section('title','Edit Payment')
@section('page-title','Edit Payment')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            id="backBtn"
            data-back-url="{{ route('payments.show', $payment) }}">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
    <script>
        document.getElementById('backBtn').addEventListener('click', function() {
            if (history.length > 1) {
                history.back();
            } else {
                window.location = this.dataset.backUrl;
            }
        });
    </script>
@endsection
@section('styles')
<style>
/* Custom Multi-Select Dropdown */
.multi-select {
    position: relative;
    width: 100%;
}
.multi-select-trigger {
    width: 100%;
    padding: 10px 34px 10px 13px;
    border: 1.5px solid var(--border-input);
    border-radius: var(--radius-sm);
    font-size: 14px;
    color: var(--text-primary);
    background: var(--bg-input);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: border 0.15s, box-shadow 0.15s;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%2394a3b8' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
}
.multi-select-trigger:hover {
    border-color: var(--primary);
}
.multi-select-trigger.open {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}
.multi-select-trigger-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.multi-select-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
    z-index: 100;
    display: none;
    max-height: 250px;
    overflow-y: auto;
}
.multi-select-dropdown.open {
    display: block;
}
.multi-select-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 13px;
    cursor: pointer;
    transition: background 0.15s;
}
.multi-select-option:hover {
    background: var(--bg-hover);
}
.multi-select-option input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
    flex-shrink: 0;
}
.multi-select-option label {
    cursor: pointer;
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
}
</style>
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
                    <label class="form-label" for="months_covered">Months Covered <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="months_covered" name="months_covered" class="form-control @error('months_covered') is-invalid @enderror"
                           value="{{ old('months_covered', $payment->months_covered ?? 1) }}" min="1" max="12" required>
                    @error('months_covered')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="time_types">Time Slots <span style="color:var(--danger)">*</span></label>
                    <div class="multi-select" id="timeTypesMultiSelect">
                        <div class="multi-select-trigger" id="timeTypesTrigger">
                            <span class="multi-select-trigger-text" id="timeTypesTriggerText">Select time slots...</span>
                        </div>
                        <div class="multi-select-dropdown" id="timeTypesDropdown">
                            @php
                                $slots = [
                                    'mon-fri 7:00-9:00' => 'Mon-Fri: 7:00 - 9:00 AM',
                                    'mon-fri 9:00-11:00' => 'Mon-Fri: 9:00 - 11:00 AM',
                                    'mon-fri 1:00-3:00' => 'Mon-Fri: 1:00 - 3:00 PM',
                                    'mon-fri 3:00-5:00' => 'Mon-Fri: 3:00 - 5:00 PM',
                                    'mon-fri 5:30-7:30' => 'Mon-Fri: 5:30 - 7:30 PM',
                                    'sat-sun 7:00-11:00' => 'Sat-Sun: 7:00 - 11:00 AM',
                                    'sat-sun 1:00-5:00' => 'Sat-Sun: 1:00 - 5:00 PM',
                                ];
                            @endphp
                            @foreach($slots as $value => $label)
                                <div class="multi-select-option">
                                    <input type="checkbox" id="time_slot_{{ $loop->index }}" name="time_types[]" value="{{ $value }}"
                                           {{ (is_array(old('time_types')) && in_array($value, old('time_types'))) || (is_array($payment->time_types) && in_array($value, $payment->time_types)) ? 'checked' : '' }}
                                           class="time-slot-checkbox">
                                    <label for="time_slot_{{ $loop->index }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Select all applicable time slots</small>
                    @error('time_types')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
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
                        <option value="cash" {{ old('payment_method',$payment->payment_method)==='cash'?'selected':'' }}>Cash</option>
                        <option value="aba" {{ old('payment_method',$payment->payment_method)==='aba'?'selected':'' }}>ABA Bank</option>
                        <option value="ac" {{ old('payment_method',$payment->payment_method)==='ac'?'selected':'' }}>ACLEDA Bank</option>
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
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Multi-select dropdown logic
    var timeTypesTrigger = document.getElementById('timeTypesTrigger');
    var timeTypesDropdown = document.getElementById('timeTypesDropdown');
    var timeTypesTriggerText = document.getElementById('timeTypesTriggerText');
    var timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');

    function updateTriggerText() {
        var selected = Array.from(timeSlotCheckboxes).filter(cb => cb.checked).map(cb => cb.nextElementSibling.textContent);
        if (selected.length === 0) {
            timeTypesTriggerText.textContent = 'Select time slots...';
        } else if (selected.length === 1) {
            timeTypesTriggerText.textContent = selected[0];
        } else {
            timeTypesTriggerText.textContent = selected.length + ' time slots selected';
        }
    }

    timeTypesTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        timeTypesTrigger.classList.toggle('open');
        timeTypesDropdown.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#timeTypesMultiSelect')) {
            timeTypesTrigger.classList.remove('open');
            timeTypesDropdown.classList.remove('open');
        }
    });

    timeSlotCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateTriggerText);
    });

    // Initialize trigger text
    updateTriggerText();
});
</script>
@endsection
