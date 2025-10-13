@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Admission  Step 1 (Reference: {{ $reference }})</h2>

    <div class="alert alert-info">
        <strong>Note:</strong> You are editing all students under reference {{ $reference }}. Any changes will affect all siblings in this group.
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the following:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('students.next.edit', $reference) }}" id="admission-form">
        @csrf

        {{-- Period Selection --}}
        <div class="card mb-4">
            <div class="card-header">Period / Schedule Type</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Select period *</label>
                    <select name="period" class="form-select" required>
                        <option value="weekly" {{ old('period', $admission['period'] ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('period', $admission['period'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly (4 weeks)</option>
                    </select>
                </div>
            </div>
        </div>

        <input type="hidden" name="count" value="{{ count($students) }}">

        @foreach($students as $index => $student)
        
        {{-- Guardian Details Section --}}
        @if($index == 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="bi bi-person-circle"></i> Guardian Details (All Siblings)
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Guardian Name</label>
                        <input type="text" name="students[{{ $index }}][guardian_name]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_name', $student->guardian_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian Phone</label>
                        <input type="text" name="students[{{ $index }}][guardian_phone]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_phone', $student->guardian_phone) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Guardian Email</label>
                        <input type="email" name="students[{{ $index }}][guardian_email]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_email', $student->guardian_email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="students[{{ $index }}][city]" class="form-control" 
                               value="{{ old('students.'.$index.'.city', $student->city) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Post Code</label>
                        <input type="text" name="students[{{ $index }}][post_code]" class="form-control" 
                               value="{{ old('students.'.$index.'.post_code', $student->post_code) }}">
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Student Details Section --}}
        <div class="card mb-4" id="student-{{ $index }}">
            <div class="card-header">
                <i class="bi bi-person-badge"></i> Student {{ $index + 1 }} {{ $index == 0 ? '(required)' : '' }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Student Name --}}
                    <div class="col-md-6">
                        <label class="form-label">First name *</label>
                        <input type="text" name="students[{{ $index }}][first_name]" class="form-control" 
                               value="{{ old('students.'.$index.'.first_name', $student->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last name *</label>
                        <input type="text" name="students[{{ $index }}][last_name]" class="form-control" 
                               value="{{ old('students.'.$index.'.last_name', $student->last_name) }}" required>
                    </div>

                    {{-- Student Personal Info --}}
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="students[{{ $index }}][gender]" class="form-select">
                            <option value="">— Select —</option>
                            <option value="male" {{ strtolower(old('students.'.$index.'.gender', $student->gender)) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ strtolower(old('students.'.$index.'.gender', $student->gender)) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ strtolower(old('students.'.$index.'.gender', $student->gender)) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of birth</label>
                        <input type="date" name="students[{{ $index }}][dob]" class="form-control" 
                               value="{{ old('students.'.$index.'.dob', $student->dob) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enroll date</label>
                        <input type="date" name="students[{{ $index }}][enroll_date]" class="form-control" 
                               value="{{ old('students.'.$index.'.enroll_date', $student->enroll_date) }}">
                    </div>

                    {{-- Financial Information --}}
                    <div class="col-md-4">
                        <label class="form-label">Start date</label>
                        <input type="date" name="students[{{ $index }}][start_date]" class="form-control" 
                               value="{{ old('students.'.$index.'.start_date', $student->start_date) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Deposit (£)</label>
                        <input type="number" step="0.01" min="0" name="students[{{ $index }}][deposit]" class="form-control" 
                               value="{{ old('students.'.$index.'.deposit', $student->deposit) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment (£)</label>
                        <input type="number" step="0.01" min="0" name="students[{{ $index }}][fee_amount]" class="form-control" 
                               value="{{ old('students.'.$index.'.fee_amount', $student->fee_amount) }}">
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="students[{{ $index }}][notes]" class="form-control" rows="2">{{ old('students.'.$index.'.notes', $student->notes) }}</textarea>
                    </div>

                    {{-- Hidden guardian fields for siblings to maintain data --}}
                    @if($index > 0)
                        <input type="hidden" name="students[{{ $index }}][guardian_name]" value="{{ $students->first()->guardian_name }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_phone]" value="{{ $students->first()->guardian_phone }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_email]" value="{{ $students->first()->guardian_email }}">
                        <input type="hidden" name="students[{{ $index }}][city]" value="{{ $students->first()->city }}">
                        <input type="hidden" name="students[{{ $index }}][post_code]" value="{{ $students->first()->post_code }}">
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Next  Timetable</button>
        </div>
    </form>
</div>
@endsection
