@extends('layouts.app')

@section('content')
<div class="container">
  @php
    $a = $admission ?? [];
    $isEdit = isset($a['is_edit']) && $a['is_edit'] === true;
  @endphp
  
  <h2 class="mb-4">{{ $isEdit ? 'Edit Admission' : 'New Admission' }} — Confirm</h2>
  
  @if($isEdit)
    <div class="alert alert-warning">
      <strong>Editing Mode:</strong> You are updating reference <strong>{{ $a['reference'] ?? '' }}</strong>. All existing timetable entries will be replaced with the new selections below.
    </div>
  @endif
  
    {{-- DEBUG: show raw admission payload for troubleshooting --}}
    <div class="alert alert-secondary">
      <strong>Debug: admission payload</strong>
      <pre style="white-space:pre-wrap;word-break:break-word">{{ json_encode($admission ?? [], JSON_PRETTY_PRINT) }}</pre>
    </div>

  @php
    $a = $admission ?? [];
  @endphp

  @php
    // Handle both new admission and edit mode data structures
    if ($isEdit && !empty($a['students'])) {
      // Edit mode: data is in students array
      $firstStudent = $a['students'][0] ?? [];
      $guardianData = $firstStudent;
      $studentData = $firstStudent;
    } else {
      // New admission: data is at root level
      $guardianData = $a;
      $studentData = $a;
    }
  @endphp

  <div class="card mb-4">
    <div class="card-header">Guardian</div>
    <div class="card-body">
      <div><strong>Name:</strong> {{ $guardianData['guardian_name'] ?? '' }}</div>
      <div><strong>Relation:</strong> {{ $guardianData['guardian_relation'] ?? '' }}</div>
      <div><strong>Phone:</strong> {{ $guardianData['guardian_phone'] ?? '' }}</div>
      <div><strong>Email:</strong> {{ $guardianData['guardian_email'] ?? '' }}</div>
      <div><strong>Address:</strong> {{ $guardianData['guardian_address'] ?? '' }}</div>
      <div><strong>City:</strong> {{ $guardianData['guardian_city'] ?? '' }}</div>
      <div><strong>Notes:</strong> {{ $guardianData['guardian_notes'] ?? '' }}</div>
      <div><strong>Post code:</strong> {{ $guardianData['post_code'] ?? '' }}</div>
      <div><strong>Reference:</strong> {{ $a['reference'] ?? '— will be auto-generated —' }}</div>
    </div>
  </div>

  @if($isEdit && !empty($a['students']))
    {{-- Edit mode: show all students from students array --}}
    @foreach($a['students'] as $idx => $student)
      <div class="card mb-4">
        <div class="card-header">{{ $idx == 0 ? 'Student 1' : 'Sibling ' . ($idx + 1) }}</div>
        <div class="card-body">
          <div><strong>Name:</strong> {{ ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '') }}</div>
          <div><strong>Gender:</strong> {{ $student['gender'] ?? '' }}</div>
          <div><strong>DOB:</strong> {{ $student['dob'] ?? '' }}</div>
          @if($idx == 0)
            <div><strong>Enroll date:</strong> {{ $student['enroll_date'] ?? '' }}</div>
            <div><strong>Start date:</strong> {{ $student['start_date'] ?? '' }}</div>
            <div><strong>Deposit:</strong> {{ $student['deposit'] ?? '' }}</div>
            <div><strong>Deposit Paid:</strong> {{ isset($student['deposit_paid']) && $student['deposit_paid'] == '1' ? 'Yes' : 'No' }}</div>
            <div><strong>Payment:</strong> {{ $student['fee_amount'] ?? $student['payment'] ?? '' }}</div>
            <div><strong>Period:</strong> {{ $student['period'] ?? '' }}</div>
          @endif
        </div>
      </div>
    @endforeach
  @else
    {{-- New admission mode: old structure --}}
    <div class="card mb-4">
      <div class="card-header">Student 1</div>
      <div class="card-body">
        <div><strong>Name:</strong> {{ ($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '') }}</div>
        <div><strong>Gender:</strong> {{ $a['gender'] ?? '' }}</div>
        <div><strong>DOB:</strong> {{ $a['dob'] ?? '' }}</div>
        <div><strong>Enroll date:</strong> {{ $a['enroll_date'] ?? '' }}</div>
        <div><strong>Start date:</strong> {{ $a['start_date'] ?? '' }}</div>
        <div><strong>Deposit:</strong> {{ $a['deposit'] ?? '' }}</div>
        <div><strong>Deposit Paid:</strong> {{ isset($a['deposit_paid']) && $a['deposit_paid'] == '1' ? 'Yes' : 'No' }}</div>
        <div><strong>Payment:</strong> {{ $a['payment'] ?? '' }}</div>
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
  @endif

  <form method="POST" action="{{ $isEdit ? route('students.update', $a['reference']) : route('students.store') }}">
    @csrf
    @if($isEdit)
      @method('PUT')
    @endif
    <div class="mb-3">
      <a href="{{ $isEdit ? route('students.edit', $a['reference']) : route('students.create') }}" class="btn btn-outline-secondary">Back</a>
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
      // Build students array - handle both new admission and edit mode
      $students = [];
      if ($isEdit && !empty($a['students'])) {
        // Edit mode: use students array
        foreach ($a['students'] as $s) {
          $students[] = ['name' => ($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')];
        }
      } else {
        // New admission: use first_name + siblings
        $students[] = ['name' => ($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')];
        foreach ($a['siblings'] ?? [] as $s) {
          $students[] = ['name' => ($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')];
        }
      }
    @endphp

    @php
      $dayOrder = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
      $dayNames = ['Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday','Sun'=>'Sunday'];
      $slotMap = [
        'Mon'=>['12–2pm','2:15–4:15pm','4:45–6:45pm','7–9pm'],
        'Tue'=>['12–2pm','2:15–4:15pm','4:45–6:45pm','7–9pm'],
        'Wed'=>['12–2pm','2:15–4:15pm','4:45–6:45pm','7–9pm'],
        'Thu'=>['12–2pm','2:15–4:15pm','4:45–6:45pm','7–9pm'],
        'Fri'=>['9–11am','11:15–1:15pm','4:45–6:45pm','7–9pm'],
        'Sat'=>['9–11am','11:15–1:15pm','2:15–4:15pm','4:30–6:30pm'],
        'Sun'=>['9–11am','11:15–1:15pm','2:15–4:15pm','4:30–6:30pm'],
      ];
    @endphp
    @foreach($students as $si => $stu)
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Timetable — {{ $stu['name'] ?: 'Student '.($si+1) }}</span>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.printTimetable{{ $si }}()">Print</button>
        </div>
        <div class="card-body">
          <div id="timetable-print-{{ $si }}">
            <div class="mb-2">
              <strong>Student:</strong> {{ $stu['name'] ?: 'Student '.($si+1) }}<br>
              <strong>Reference:</strong> {{ $a['reference'] ?? '—' }}
            </div>
            <table class="table table-bordered text-center align-middle">
              <thead>
                <tr>
                  @foreach($dayOrder as $d)
                    <th>{{ $dayNames[$d] }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @for($slot=0;$slot<4;$slot++)
                  <tr>
                    @foreach($dayOrder as $d)
                    <td>
                      <div class="fw-bold small mb-1">{{ $slotMap[$d][$slot] }}</div>
                      <select name="timetable[{{ $si }}][{{ $d }}][{{ $slot }}]" class="form-select">
                        <option value="">—</option>
                        @foreach($subjects as $sub)
                          @php
                            // Check if there's existing timetable data (edit mode)
                            $existingValue = $a['timetable'][$si][$d][$slot] ?? null;
                            $oldValue = old('timetable.'.$si.'.'.$d.'.'.$slot);
                            $selectedValue = $oldValue ?? $existingValue;
                          @endphp
                          <option value="{{ $sub }}" @if($selectedValue == $sub) selected @endif>{{ $sub }}</option>
                        @endforeach
                      </select>
                      <span class="print-subject d-none" id="print-subject-{{ $si }}-{{ $d }}-{{ $slot }}">
                        @php
                          $existingValue = $a['timetable'][$si][$d][$slot] ?? null;
                          $displayValue = old('timetable.'.$si.'.'.$d.'.'.$slot) ?? $existingValue ?? '—';
                        @endphp
                        {{ $displayValue }}
                      </span>
                    </td>
                    @endforeach
                  </tr>
                @endfor
              </tbody>
            </table>
          </div>
        </div>
        <script>
        window.printTimetable{{ $si }} = function() {
          var studentName = '{{ $stu["name"] ?: "Student ".($si+1) }}';
          var reference = '{{ $a["reference"] ?? "—" }}';
          
          // Build timetable HTML with selected values
          var html = '<html><head><title>Timetable - ' + studentName + '</title>';
          html += '<style>body{font-family:Arial,sans-serif;margin:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #000;padding:8px;text-align:center;}th{background:#f5f5f5;}</style>';
          html += '</head><body>';
          html += '<h3>Timetable</h3>';
          html += '<p><strong>Student:</strong> ' + studentName + '<br><strong>Reference:</strong> ' + reference + '</p>';
          html += '<table>';
          html += '<thead><tr><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th><th>Sunday</th></tr></thead>';
          html += '<tbody>';
          
          var days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
          var slots = [
            ['12–2pm','12–2pm','12–2pm','12–2pm','9–11am','9–11am','9–11am'],
            ['2:15–4:15pm','2:15–4:15pm','2:15–4:15pm','2:15–4:15pm','11:15–1:15pm','11:15–1:15pm','11:15–1:15pm'],
            ['4:45–6:45pm','4:45–6:45pm','4:45–6:45pm','4:45–6:45pm','4:45–6:45pm','2:15–4:15pm','2:15–4:15pm'],
            ['7–9pm','7–9pm','7–9pm','7–9pm','7–9pm','4:30–6:30pm','4:30–6:30pm']
          ];
          
          for(var slot = 0; slot < 4; slot++) {
            html += '<tr>';
            for(var dayIdx = 0; dayIdx < 7; dayIdx++) {
              var day = days[dayIdx];
              var select = document.querySelector('select[name="timetable[{{ $si }}][' + day + '][' + slot + ']"]');
              var subject = select ? select.value : '—';
              if(!subject) subject = '—';
              html += '<td><div style="font-weight:bold;font-size:12px;margin-bottom:4px;">' + slots[slot][dayIdx] + '</div>' + subject + '</td>';
            }
            html += '</tr>';
          }
          
          html += '</tbody></table>';
          html += '<div style="margin-top:20px;text-align:center;">';
          html += '<button onclick="window.print()" style="padding:10px 20px;margin-right:10px;">Print</button>';
          html += '<button onclick="window.close()" style="padding:10px 20px;">Close</button>';
          html += '</div>';
          html += '</body></html>';
          
          var printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
          if (printWindow) {
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
          } else {
            alert('Please allow popups for this site to print the timetable.');
          }
        }
        </script>
      </div>
    @endforeach

    <button type="submit" class="btn btn-success">Save Admission</button>
  </form>
</div>
@endsection
