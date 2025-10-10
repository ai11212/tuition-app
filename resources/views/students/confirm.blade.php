@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">New Admission — Confirm</h2>
    {{-- DEBUG: show raw admission payload for troubleshooting --}}
    <div class="alert alert-secondary">
      <strong>Debug: admission payload</strong>
      <pre style="white-space:pre-wrap;word-break:break-word">{{ json_encode($admission ?? [], JSON_PRETTY_PRINT) }}</pre>
    </div>

  @php
    $a = $admission ?? [];
  @endphp

  <div class="card mb-4">
    <div class="card-header">Guardian</div>
    <div class="card-body">
      <div><strong>Name:</strong> {{ $a['guardian_name'] ?? '' }}</div>
      <div><strong>Relation:</strong> {{ $a['guardian_relation'] ?? '' }}</div>
      <div><strong>Phone:</strong> {{ $a['guardian_phone'] ?? '' }}</div>
      <div><strong>Email:</strong> {{ $a['guardian_email'] ?? '' }}</div>
      <div><strong>Address:</strong> {{ $a['guardian_address'] ?? '' }}</div>
      <div><strong>City:</strong> {{ $a['guardian_city'] ?? '' }}</div>
      <div><strong>Notes:</strong> {{ $a['guardian_notes'] ?? '' }}</div>
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
      <div><strong>Enroll date:</strong> {{ $a['enroll_date'] ?? '' }}</div>
      <div><strong>Start date:</strong> {{ $a['start_date'] ?? '' }}</div>
      <div><strong>Deposit:</strong> {{ $a['deposit'] ?? '' }}</div>
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

  <form method="POST" action="{{ route('students.store') }}">
    @csrf
    <div class="mb-3">
      <a href="{{ route('students.create') }}" class="btn btn-outline-secondary">Back</a>
    </div>
    {{-- Timetable selection for each student (primary + siblings) --}}
    @php
      $subjects = ['Maths','English','Science','Chemistry','Physics','Biology','Psychology','Economics'];
      $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
      $slots = [
        ['start'=>'12:00','end'=>'14:00'],
        ['start'=>'14:15','end'=>'16:15'],
        ['start'=>'16:45','end'=>'18:45'],
        ['start'=>'19:00','end'=>'21:00'],
      ];
      // Build students array: primary then siblings
      $students = [];
      $students[] = ['name'=>($a['first_name'] ?? '').' '.($a['last_name'] ?? '')];
      foreach($a['siblings'] ?? [] as $s) {
        $students[] = ['name'=>($s['first_name'] ?? '').' '.($s['last_name'] ?? '')];
      }
    @endphp

    @foreach($students as $si => $stu)
      <div class="card mb-3">
        <div class="card-header">Timetable — {{ $stu['name'] ?: 'Student '.($si+1) }}</div>
        <div class="card-body">
          <div class="text-sm text-gray-600 mb-2">Select subject for each day & slot (leave empty if not attending).</div>
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Day</th>
                @foreach($slots as $slot)
                  <th>{{ \Carbon\Carbon::createFromFormat('H:i',$slot['start'])->format('g:i A') }} — {{ \Carbon\Carbon::createFromFormat('H:i',$slot['end'])->format('g:i A') }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($days as $di => $d)
                <tr>
                  <td class="align-middle">{{ $d }}</td>
                  @foreach($slots as $si2 => $sl)
                    <td>
                      <select name="timetable[{{ $si }}][{{ $di+1 }}][{{ $si2+1 }}]" class="form-control">
                        <option value="">—</option>
                        @foreach($subjects as $sub)
                          <option value="{{ $sub }}">{{ $sub }}</option>
                        @endforeach
                      </select>
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endforeach

    <button type="submit" class="btn btn-success">Save Admission</button>
  </form>
</div>
@endsection
