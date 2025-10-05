@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">Confirm Admission</h2>

  <div class="card mb-4">
    <div class="card-header">Guardian</div>
    <div class="card-body">
      <div><strong>Name:</strong> {{ $data['guardian_name'] ?? '-' }}</div>
      <div><strong>Relation:</strong> {{ $data['guardian_relation'] ?? '-' }}</div>
      <div><strong>Phone:</strong> {{ $data['guardian_phone'] ?? '-' }}</div>
      <div><strong>Email:</strong> {{ $data['guardian_email'] ?? '-' }}</div>
      <div><strong>Address:</strong> {{ $data['guardian_address'] ?? '-' }}</div>
      <div><strong>Post code:</strong> {{ $data['post_code'] ?? '-' }}</div>
      <div class="mt-2"><strong>Reference:</strong> {{ $data['reference'] ?? '-' }}</div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">Students</div>
    <div class="card-body">
      @foreach(($data['students'] ?? []) as $i => $s)
        <div class="mb-3 pb-3 border-bottom">
          <div><strong>Student {{ $i+1 }}</strong></div>
          <div>{{ $s['first_name'] ?? '' }} {{ $s['last_name'] ?? '' }}</div>
          <div>Gender: {{ $s['gender'] ?? '-' }}</div>
          <div>DOB: {{ $s['dob'] ?? '-' }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <form method="POST" action="{{ route('students.store') }}">
    @csrf
    <input type="hidden" name="reference" value="{{ $data['reference'] ?? '' }}">
    <input type="hidden" name="guardian_name" value="{{ $data['guardian_name'] ?? '' }}">
    <input type="hidden" name="guardian_relation" value="{{ $data['guardian_relation'] ?? '' }}">
    <input type="hidden" name="guardian_phone" value="{{ $data['guardian_phone'] ?? '' }}">
    <input type="hidden" name="guardian_email" value="{{ $data['guardian_email'] ?? '' }}">
    <input type="hidden" name="guardian_address" value="{{ $data['guardian_address'] ?? '' }}">
    <input type="hidden" name="post_code" value="{{ $data['post_code'] ?? '' }}">

    @foreach(($data['students'] ?? []) as $i => $s)
      <input type="hidden" name="students[{{ $i }}][first_name]" value="{{ $s['first_name'] ?? '' }}">
      <input type="hidden" name="students[{{ $i }}][last_name]" value="{{ $s['last_name'] ?? '' }}">
      <input type="hidden" name="students[{{ $i }}][gender]" value="{{ $s['gender'] ?? '' }}">
      <input type="hidden" name="students[{{ $i }}][dob]" value="{{ $s['dob'] ?? '' }}">
    @endforeach

    <div class="d-flex gap-2">
      <a href="{{ route('students.create') }}" class="btn btn-outline-secondary">Back</a>
      <button type="submit" class="btn btn-success">Save Admission</button>
    </div>
  </form>
</div>
@endsection
