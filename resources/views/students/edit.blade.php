@extends('layouts.app')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')
@section('topbar-back')
    <button type="button" class="btn btn-outline btn-sm"
            onclick="history.length>1?history.back():window.location='{{ route('students.show', $student) }}'">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
    </button>
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
                    <input type="text" id="subject" name="subject" class="form-control"
                           value="{{ old('subject', $student->subject) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="time_type">Time Type <span style="color:var(--danger)">*</span></label>
                    <select id="time_type" name="time_type" class="form-control @error('time_type') is-invalid @enderror" required>
                        <option value="">-- Select Time Slot --</option>
                        @foreach(['mon-fri 7:00-9:00','mon-fri 9:00-11:00','mon-fri 1:00-3:00','mon-fri 3:00-5:00','mon-fri 5:30-7:30','sat-sun 7:00-11:00','sat-sun 1:00-5:00'] as $slot)
                        <option value="{{ $slot }}" {{ old('time_type', strtolower($student->time_type ?? ''))===$slot?'selected':'' }}>{{ $slot }}</option>
                        @endforeach
                    </select>
                    @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
