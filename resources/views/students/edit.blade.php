@extends('layouts.app')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            id="backBtn"
            data-back-url="{{ route('students.show', $student) }}">
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
        <div class="card-title">Edit: {{ $student->full_name }}</div>
    </div>
    <div class="card-body">
        <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="student_id">Student ID <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="student_id" name="student_id"
                           class="form-control @error('student_id') is-invalid @enderror"
                           value="{{ old('student_id', $student->student_id) }}" required>
                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="enrollment_date">Enrollment Date <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="enrollment_date" name="enrollment_date" class="form-control"
                           value="{{ old('enrollment_date', $student->enrollment_date->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                           value="{{ old('first_name', $student->first_name) }}" required autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                           value="{{ old('last_name', $student->last_name) }}" required autocomplete="family-name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="{{ old('phone', $student->phone) }}" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label class="form-label" for="come_from">Come From</label>
                    <input type="text" id="come_from" name="come_from" class="form-control"
                           value="{{ old('come_from', $student->come_from) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="male"   {{ old('gender',$student->gender)==='male'?'selected':'' }}>Male</option>
                        <option value="female" {{ old('gender',$student->gender)==='female'?'selected':'' }}>Female</option>
                        <option value="other"  {{ old('gender',$student->gender)==='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="year_level">Grade <span style="color:var(--danger)">*</span></label>
                    <select id="year_level" name="year_level" class="form-control" required>
                        @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}" {{ old('year_level',$student->year_level)==$i?'selected':'' }}>Grade {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="subject">Subject <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="subject" name="subject"
                           class="form-control @error('subject') is-invalid @enderror"
                           value="{{ old('subject', $student->subject) }}" required>
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                           {{ (is_array(old('time_types')) && in_array($value, old('time_types'))) || (is_array($student->time_types) && in_array($value, $student->time_types)) ? 'checked' : '' }}
                                           class="time-slot-checkbox">
                                    <label for="time_slot_{{ $loop->index }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Select all applicable time slots</small>
                    @error('time_types')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        Changing this updates the auto-fill on the next payment form.
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="monthly_payment_day">Payment Day <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="monthly_payment_day" name="monthly_payment_day"
                           class="form-control @error('monthly_payment_day') is-invalid @enderror"
                           value="{{ old('monthly_payment_day', $student->monthly_payment_day ?? 1) }}"
                           min="1" max="31" required>
                    @error('monthly_payment_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="monthly_fee">Monthly Fee <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="monthly_fee" name="monthly_fee" step="0.01"
                           class="form-control @error('monthly_fee') is-invalid @enderror"
                           value="{{ old('monthly_fee', $student->monthly_fee ?? 0) }}" min="0" required>
                    @error('monthly_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            {{-- Discount --}}
            <div class="form-group">
                <label class="form-label" for="discount">
                    Discount (%) <span style="color:var(--text-muted)">(0-100)</span>
                </label>
                <input type="number" id="discount" name="discount" step="0.01"
                       class="form-control @error('discount') is-invalid @enderror"
                       value="{{ old('discount', $student->discount ?? 0) }}" min="0" max="100">
                @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            {{-- Study Status --}}
            <div class="form-group">
                <label class="form-label" for="study_status">{{ __('app.status') }}</label>
                <select id="study_status" name="study_status" class="form-control">
                    <option value="studying" {{ old('study_status', $student->study_status ?? 'studying')==='studying' ? 'selected' : '' }}>
                        🟢 {{ __('app.studying') }}
                    </option>
                    <option value="stopped" {{ old('study_status', $student->study_status ?? 'studying')==='stopped' ? 'selected' : '' }}>
                        🔴 {{ __('app.stopped') }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="photo">New Photo <small style="color:var(--text-muted)">(leave blank to keep current)</small></label>
                <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                @if($student->photo)
                <div style="margin-top:8px">
                    <img src="{{ asset('storage/'.$student->photo) }}" alt="Current photo" style="max-width:120px;border-radius:8px;border:1px solid var(--border)">
                </div>
                @endif
            </div>
            <div style="display:flex;gap:10px;padding-top:6px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Save Changes</button>
                <a href="{{ route('students.show', $student) }}" class="btn btn-outline">Cancel</a>
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
