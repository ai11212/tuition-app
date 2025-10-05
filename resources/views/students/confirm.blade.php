@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">New Admission — Confirm</h2>

  @php($a = $admission ?? [])

  <div class="card mb-4">
    <div class="card-header">Guardian</div>
    <div class="card-body">
      <div><strong>Name:</strong> {{ $a['guardian_name'] ?? '' }}</div>
      <div><strong>Relation:</strong> {{ $a['guardian_relation'] ?? '' }}</div>
      <div><strong>Phone:</strong> {{ $a['guardian_phone'] ?? '' }}</div>
      <div><strong>Email:</strong> {{ $a['guardian_email'] ?? '' }}</div>
      <div><strong>Address:</strong> {{ $a['guardian_address'] ?? '' }}</div>
      <div><strong>Post code:</strong> {{ $a['post_code'] ?? '' }}</div>
      <div><strong>Reference (optional):</strong> {{ $a['reference'] ?? '— will be auto-generated —' }}</div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">Student 1</div>
    <div class="card-body">
      <div><strong>Name:</strong> {{ ($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '') }}</div>
      <div><strong>Gender:</strong> {{ $a['gender'] ?? '' }}</div>
      <div><strong>DOB:</strong> {{ $a['dob'] ?? '' }}</div>
    </div>
  </div>

  @if(!empty($a['siblings']))
    @foreach($a['siblings'] as $idx => $s)
      <div class="card mb-3">
        <div class="card-header">Sibling {{ $idx+2 }}</div>
        <div class="card-body">
          <div><strong>Name:</strong> {{ ($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '') }}</div>
          <div><strong>Gender:</strong> {{ $s['gender'] ?? '' }}</div>
          <div><strong>DOB:</strong> {{ $s['dob'] ?? '' }}</div>
        </div>
      </div>
    @endforeach
  @endif

  <form method="POST" action="{{ route('students.store') }}" class="d-flex gap-2">
    @csrf
    <a href="{{ route('students.create') }}" class="btn btn-outline-secondary">Back</a>
    <button type="submit" class="btn btn-success">Save Admission</button>
  </form>
</div>
@endsection
