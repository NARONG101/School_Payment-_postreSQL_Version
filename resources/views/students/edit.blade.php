@extends('layouts.app')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')
@section('content')
<div style="max-width:780px;margin:0 auto">
<div class="card">
    <div class="card-header">
        <div class="card-title">Edit: {{ $student->full_name }}</div>
    </div>
    <div class="card-body">
        <form action="{{ route('students.update', $student) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Student ID *</label>
                    <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $student->student_id) }}" required>
                    @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Enrollment Date *</label>
                    <input type="date" name="enrollment_date" class="form-control" value="{{ old('enrollment_date', $student->enrollment_date->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Come From</label>
                <input type="text" name="come_from" class="form-control" value="{{ old('come_from', $student->come_from) }}">
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender',$student->gender)=='male'?'selected':'' }}>Male</option>
                        <option value="female" {{ old('gender',$student->gender)=='female'?'selected':'' }}>Female</option>
                        <option value="other" {{ old('gender',$student->gender)=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Subject *</label>
                    <input type="text" name="subject" class="form-control" value="{{ old('subject', $student->subject) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Grade *</label>
                    <select name="year_level" class="form-control" required>
                        @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}" {{ old('year_level',$student->year_level)==$i?'selected':'' }}>Grade {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Monthly Payment Day <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="monthly_payment_day" class="form-control @error('monthly_payment_day') is-invalid @enderror"
                           value="{{ old('monthly_payment_day', $student->monthly_payment_day ?? 1) }}" min="1" max="31" required>
                    @error('monthly_payment_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Fee <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="monthly_fee" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                           value="{{ old('monthly_fee', $student->monthly_fee ?? 0) }}" required>
                    @error('monthly_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Time Type <span style="color:var(--danger)">*</span></label>
                <select name="time_type" class="form-control @error('time_type') is-invalid @enderror" required>
                    <option value="">-- Select Time Slot --</option>
                    <option value="mon-fri 7:00-9:00" {{ old('time_type', $student->time_type)=='mon-fri 7:00-9:00'?'selected':'' }}>Mon-Fri 7:00-9:00</option>
                    <option value="mon-fri 9:00-11:00" {{ old('time_type', $student->time_type)=='mon-fri 9:00-11:00'?'selected':'' }}>Mon-Fri 9:00-11:00</option>
                    <option value="mon-fri 1:00-3:00" {{ old('time_type', $student->time_type)=='mon-fri 1:00-3:00'?'selected':'' }}>Mon-Fri 1:00-3:00</option>
                    <option value="mon-fri 3:00-5:00" {{ old('time_type', $student->time_type)=='mon-fri 3:00-5:00'?'selected':'' }}>Mon-Fri 3:00-5:00</option>
                    <option value="mon-fri 5:30-7:30" {{ old('time_type', $student->time_type)=='mon-fri 5:30-7:30'?'selected':'' }}>Mon-Fri 5:30-7:30</option>
                    <option value="sat-sun 7:00-11:00" {{ old('time_type', $student->time_type)=='sat-sun 7:00-11:00'?'selected':'' }}>Sat-Sun 7:00-11:00</option>
                    <option value="sat-sun 1:00-5:00" {{ old('time_type', $student->time_type)=='sat-sun 1:00-5:00'?'selected':'' }}>Sat-Sun 1:00-5:00</option>
                </select>
                @error('time_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">New Photo (leave blank to keep current)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div style="display:flex;gap:12px;padding-top:8px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="{{ route('students.show', $student) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
