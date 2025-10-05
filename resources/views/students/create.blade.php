@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">New Admission — Step 1</h2>

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

    <form method="POST" action="{{ route('students.next') }}" id="admission-form">
        @csrf

        <div class="card mb-4">
            <div class="card-header">Guardian details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Guardian name *</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Relationship</label>
                        <input type="text" name="guardian_relation" class="form-control" value="{{ old('guardian_relation') }}" placeholder="e.g., Mother, Father, Aunt">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian phone</label>
                        <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian email</label>
                        <input type="email" name="guardian_email" class="form-control" value="{{ old('guardian_email') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Guardian address</label>
                        <input type="text" name="guardian_address" class="form-control" value="{{ old('guardian_address') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Post code (guardian)</label>
                        <input type="text" name="post_code" class="form-control" value="{{ old('post_code') }}">
                        <div class="form-text">Max 120 characters.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Reference (optional)</label>
                        <input type="text" name="reference" class="form-control" placeholder="Leave blank to auto-generate" value="{{ old('reference') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" id="student-0">
            <div class="card-header">Student 1 (required)</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First name *</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last name *</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">— Select —</option>
                            <option value="male"   @selected(old('gender')==='male')>Male</option>
                            <option value="female" @selected(old('gender')==='female')>Female</option>
                            <option value="other"  @selected(old('gender')==='other')>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of birth</label>
                        <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
                    </div>
                </div>
            </div>
        </div>

        <div id="siblings-wrap"></div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" id="add-sibling">+ Add another sibling</button>
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
</div>

<script>
(function(){
  let i = 0;
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
              <input type="text" name="siblings[${i}][first_name]" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Last name</label>
              <input type="text" name="siblings[${i}][last_name]" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select name="siblings[${i}][gender]" class="form-select">
                <option value="">— Select —</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date of birth</label>
              <input type="date" name="siblings[${i}][dob]" class="form-control">
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
