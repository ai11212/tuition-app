@php
  // Subjects shown in each dropdown (edit freely)
  $subjects = [
    'Maths','English','Science','Chemistry','Physics','Biology','Psychology','Economics'
  ];

  // Timetable per day (columns). Each day has its own list of slot labels (rows).
  $grid = [
    'Monday'    => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
    'Tuesday'   => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
    'Wednesday' => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
    'Thursday'  => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
    'Friday'    => ['9–11am','11:15am–1:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
    'Saturday'  => ['9–11am','11:15am–1:15pm','2:15pm–4:15pm','4:30pm–6:30pm'],
    'Sunday'    => ['9–11am','11:15am–1:15pm','2:15pm–4:15pm','4:30pm–6:30pm'],
  ];

  // If this grid should be attached to a specific student row,
  // pass $studentIndex into this include:
  // @include('students._schedule', ['studentIndex' => $i])
  $baseName = isset($studentIndex)
    ? "students[$studentIndex][schedule]"
    : "schedule";
@endphp

<style>
  .schedule-wrap { margin-top: 1.25rem; }
  .schedule-grid { width:100%; border-collapse: collapse; table-layout: fixed; }
  .schedule-grid th, .schedule-grid td { border:1px solid #e5e7eb; padding:.5rem; vertical-align: top; }
  .schedule-grid th { background:#f9fafb; text-align:center; font-weight:600; }
  .slot-controls { display:flex; align-items:center; gap:.5rem; margin-top:.25rem; }
  .slot-controls .choice { display:flex; align-items:center; gap:.25rem; font-size:.9rem; }
  .slot-subject { width:100%; }
  @media (max-width: 900px){
    .schedule-grid { font-size:.92rem; }
  }
</style>

<div class="schedule-wrap">
  <h3 style="font-size:1.1rem; font-weight:600; margin-bottom:.5rem;">Availability & Subjects</h3>
  <div style="overflow-x:auto;">
    <table class="schedule-grid">
      <thead>
        <tr>
          <th style="width:9rem;">Time</th>
          @foreach(array_keys($grid) as $day)
            <th>{{ $day }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @php
          // Determine max rows to print (so we can render every day's slots)
          $maxRows = max(array_map('count', $grid));
        @endphp

        @for($row=0; $row < $maxRows; $row++)
          <tr>
            {{-- First column: the time label (taken from Monday if present, else any day that has this row) --}}
            @php
              $timeLabel = null;
              foreach ($grid as $d => $slots) {
                if (isset($slots[$row])) { $timeLabel = $slots[$row]; break; }
              }
            @endphp
            <td><strong>{{ $timeLabel ?? '' }}</strong></td>

            {{-- One cell per day --}}
            @foreach($grid as $day => $slots)
              <td>
                @if(isset($slots[$row]))
                  @php
                    $slotKey = $slots[$row];  // e.g. "12–2pm"
                  @endphp

                  {{-- Subject dropdown --}}
                  <select
                    class="slot-subject"
                    name="{{ $baseName }}[{{ $day }}][{{ $slotKey }}][subject]"
                  >
                    <option value="">— choose subject —</option>
                    @foreach($subjects as $subj)
                      <option value="{{ $subj }}">{{ $subj }}</option>
                    @endforeach
                  </select>

                  {{-- Radio choice (Attend / Skip). Change values/labels as needed --}}
                  <div class="slot-controls">
                    <label class="choice">
                      <input type="radio"
                             name="{{ $baseName }}[{{ $day }}][{{ $slotKey }}][choice]"
                             value="attend">
                      Attend
                    </label>
                    <label class="choice">
                      <input type="radio"
                             name="{{ $baseName }}[{{ $day }}][{{ $slotKey }}][choice]"
                             value="skip">
                      Skip
                    </label>
                  </div>
                @else
                  {{-- Empty when this day has no slot for this row --}}
                  <div style="color:#9ca3af;">—</div>
                @endif
              </td>
            @endforeach
          </tr>
        @endfor
      </tbody>
    </table>
  </div>
</div>
