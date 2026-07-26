@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Admission — Step 1 (Reference: {{ $reference }})</h2>

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

        <input type="hidden" name="count" value="{{ count($students) }}">

        @foreach($students as $index => $student)
        
        {{-- Guardian Details Section --}}
        @if($index == 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="bi bi-person-circle"></i> Guardian details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Guardian name *</label>
                        <input type="text" name="students[{{ $index }}][guardian_name]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_name', $student->guardian_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="students[{{ $index }}][guardian_relation]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_relation', $student->guardian_relation) }}" 
                               placeholder="e.g., Mother, Father, Aunt">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian phone</label>
                        <input type="text" name="students[{{ $index }}][guardian_phone]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_phone', $student->guardian_phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian email</label>
                        <input type="email" name="students[{{ $index }}][guardian_email]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_email', $student->guardian_email) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Guardian address</label>
                        <input type="text" name="students[{{ $index }}][guardian_address]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_address', $student->guardian_address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian city</label>
                        <input type="text" name="students[{{ $index }}][guardian_city]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_city', $student->guardian_city) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian notes</label>
                        <input type="text" name="students[{{ $index }}][guardian_notes]" class="form-control" 
                               value="{{ old('students.'.$index.'.guardian_notes', $student->guardian_notes) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Post code (guardian)</label>
                        <input type="text" name="students[{{ $index }}][post_code]" class="form-control" 
                               value="{{ old('students.'.$index.'.post_code', $student->post_code) }}">
                        <div class="form-text">Max 120 characters.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Reference</label>
                        <input type="text" class="form-control" value="{{ $reference }}" disabled>
                        <div class="form-text">Reference cannot be changed during edit.</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Student Details Section --}}
        <div class="card mb-4" id="student-{{ $index }}">
            <div class="card-header">
                <i class="bi bi-person-badge"></i> @if($index == 0) Student {{ $index + 1 }} (required) @else Sibling {{ $index + 1 }} @endif
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Student Name --}}
                    <div class="col-md-6">
                        <label class="form-label">First name @if($index == 0)*@endif</label>
                        <input type="text" name="students[{{ $index }}][first_name]" class="form-control" 
                               value="{{ old('students.'.$index.'.first_name', $student->first_name) }}" {{ $index == 0 ? 'required' : '' }}>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last name @if($index == 0)*@endif</label>
                        <input type="text" name="students[{{ $index }}][last_name]" class="form-control" 
                               value="{{ old('students.'.$index.'.last_name', $student->last_name) }}" {{ $index == 0 ? 'required' : '' }}>
                    </div>

                    {{-- Student Personal Info --}}
                    <div class="col-md-{{ $index == 0 ? '3' : '4' }}">
                        <label class="form-label">Gender</label>
                        <select name="students[{{ $index }}][gender]" class="form-select">
                            <option value="">— Select —</option>
                            <option value="male" {{ strtolower(old('students.'.$index.'.gender', $student->gender)) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ strtolower(old('students.'.$index.'.gender', $student->gender)) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ strtolower(old('students.'.$index.'.gender', $student->gender)) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-{{ $index == 0 ? '3' : '4' }}">
                        <label class="form-label">Date of birth</label>
                        <input type="date" name="students[{{ $index }}][dob]" class="form-control" 
                               value="{{ old('students.'.$index.'.dob', $student->dob) }}">
                    </div>
                    <div class="col-md-{{ $index == 0 ? '3' : '4' }}">
                        <label class="form-label">Year</label>
                        <input type="number" name="students[{{ $index }}][year]" min="1" max="30" class="form-control"
                               value="{{ old('students.'.$index.'.year', $student->year) }}" placeholder="1-30">
                    </div>
                    <div class="col-md-{{ $index == 0 ? '3' : '4' }}">
                        <label class="form-label">Hourly Rate</label>
                        <input type="number" step="0.01" min="0" name="students[{{ $index }}][hourly_rate]" class="form-control"
                               value="{{ old('students.'.$index.'.hourly_rate', $student->hourly_rate) }}">
                    </div>

                    @if($index == 0)
                    {{-- Only show these fields for primary student --}}
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
                    <div class="col-md-3">
                        <label class="form-label">Deposit</label>
                        <input type="number" step="0.01" min="0" name="students[{{ $index }}][deposit]" class="form-control" 
                               value="{{ old('students.'.$index.'.deposit', $student->deposit) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Deposit Paid?</label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="students[{{ $index }}][deposit_paid]" id="deposit_yes_{{ $index }}" value="1" 
                                       {{ old('students.'.$index.'.deposit_paid', $student->deposit_paid) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="deposit_yes_{{ $index }}">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="students[{{ $index }}][deposit_paid]" id="deposit_no_{{ $index }}" value="0" 
                                       {{ old('students.'.$index.'.deposit_paid', $student->deposit_paid) == '0' || old('students.'.$index.'.deposit_paid', $student->deposit_paid) === null ? 'checked' : '' }}>
                                <label class="form-check-label" for="deposit_no_{{ $index }}">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment</label>
                        {{-- Informational field — reads/writes the stored `payment` column (what the Student List shows) --}}
                        <input type="number" step="0.01" min="0" name="students[{{ $index }}][fee_amount]" class="form-control"
                               value="{{ old('students.'.$index.'.fee_amount', $student->payment) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Period</label>
                        <select name="students[{{ $index }}][period]" class="form-select">
                            <option value="">— Select —</option>
                            <option value="weekly" {{ old('students.'.$index.'.period', $student->period) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('students.'.$index.'.period', $student->period) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    @else
                    {{-- Hidden fields for siblings --}}
                    <input type="hidden" name="students[{{ $index }}][enroll_date]" value="{{ $student->enroll_date }}">
                    <input type="hidden" name="students[{{ $index }}][start_date]" value="{{ $student->start_date }}">
                    <input type="hidden" name="students[{{ $index }}][deposit]" value="{{ $student->deposit }}">
                    <input type="hidden" name="students[{{ $index }}][deposit_paid]" value="{{ $student->deposit_paid }}">
                    <input type="hidden" name="students[{{ $index }}][fee_amount]" value="{{ $student->payment }}">
                    <input type="hidden" name="students[{{ $index }}][period]" value="{{ $student->period }}">
                    @endif

                    {{-- Hidden guardian fields for siblings to maintain data --}}
                    @if($index > 0)
                        <input type="hidden" name="students[{{ $index }}][guardian_name]" value="{{ $students->first()->guardian_name }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_relation]" value="{{ $students->first()->guardian_relation }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_phone]" value="{{ $students->first()->guardian_phone }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_email]" value="{{ $students->first()->guardian_email }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_address]" value="{{ $students->first()->guardian_address }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_city]" value="{{ $students->first()->guardian_city }}">
                        <input type="hidden" name="students[{{ $index }}][guardian_notes]" value="{{ $students->first()->guardian_notes }}">
                        <input type="hidden" name="students[{{ $index }}][post_code]" value="{{ $students->first()->post_code }}">
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <div id="siblings-wrap"></div>

        <div class="mb-4">
            <button type="button" class="btn btn-outline-secondary" id="add-sibling">+ Add another sibling</button>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Next  Timetable</button>
        </div>
    </form>
</div>

<script>
(function(){
  // Start from the number of existing siblings
  let i = {{ $students->count() - 1 }};
  const wrap = document.getElementById('siblings-wrap');
  document.getElementById('add-sibling').addEventListener('click', function(){
    i++;
    const html = `
      <div class="card mb-4">
        <div class="card-header">Sibling ${i+1}</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">First name</label>
              <input type="text" name="students[${i}][first_name]" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Last name</label>
              <input type="text" name="students[${i}][last_name]" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select name="students[${i}][gender]" class="form-select">
                <option value="">— Select —</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date of birth</label>
              <input type="date" name="students[${i}][dob]" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Year</label>
              <input type="number" name="students[${i}][year]" min="1" max="30" class="form-control" placeholder="1-30">
            </div>
            <div class="col-md-4">
              <label class="form-label">Hourly Rate</label>
              <input type="number" step="0.01" min="0" name="students[${i}][hourly_rate]" class="form-control">
            </div>
          </div>
        </div>
      </div>`;
    const div = document.createElement('div');
    div.innerHTML = html;
    wrap.appendChild(div);
  });
})();
</script>
@endsection
