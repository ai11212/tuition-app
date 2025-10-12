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
                          <option value="{{ $sub }}" @if(old('timetable.'.$si.'.'.$d.'.'.$slot)==$sub) selected @endif>{{ $sub }}</option>
                        @endforeach
                      </select>
                      <span class="print-subject d-none" id="print-subject-{{ $si }}-{{ $d }}-{{ $slot }}">
                        {{ old('timetable.'.$si.'.'.$d.'.'.$slot) ?? '—' }}
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
