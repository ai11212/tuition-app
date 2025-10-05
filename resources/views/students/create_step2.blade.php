@extends('layouts.app')
@section('content')

<h1 class="text-2xl font-semibold mb-4">New Admission — Step 2</h1>

<div class="bg-white border rounded-xl p-4 mb-6">
  <div class="text-sm text-gray-600">Auto-generated Reference</div>
  <div class="text-xl font-semibold">{{ $reference ?? '' }}</div>
</div>

<form method="POST" action="{{ route('students.store') }}" class="space-y-6 admission-form">
  @csrf

  @php
    // Subjects list for every cell (can be moved to config later)
    $subjects = ['Maths','English','Science','Chemistry','Physics','Biology','Psychology','Economics'];

    // Timetable slots by day (exactly as in your image)
    $slots = [
      'Mon' => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
      'Tue' => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
      'Wed' => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
      'Thu' => ['12–2pm','2:15pm–4:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
      'Fri' => ['9–11am','11:15am–1:15pm','4:45pm–6:45pm','7:00pm–9:00pm'],
      'Sat' => ['9–11am','11:15am–1:15pm','2:15pm–4:15pm','4:30pm–6:30pm'],
      'Sun' => ['9–11am','11:15am–1:15pm','2:15pm–4:15pm','4:30pm–6:30pm'],
    ];
    $days = array_keys($slots);
  @endphp

  <div class="bg-white border rounded-xl p-0 overflow-x-auto">
    <table class="w-full border-collapse timetable">
      <thead>
        <tr>
          @foreach($days as $d)
            <th class="p-3 text-center border-b border-r bg-gray-50">
              <div class="font-semibold tracking-wide">
                {{ ['Mon'=>'MONDAY','Tue'=>'TUESDAY','Wed'=>'WEDNESDAY','Thu'=>'THURSDAY','Fri'=>'FRIDAY','Sat'=>'SATURDAY','Sun'=>'SUNDAY'][$d] }}
              </div>
              <div class="text-sm text-gray-600 mt-1">{{ $slots[$d][0] }}</div>
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        {{-- Row of "Subjects" drop-downs (top block) --}}
        <tr>
          @foreach($days as $d)
            <td class="align-top border-r border-b p-3">
              <div class="text-center font-semibold mb-2">Subjects</div>
              <div class="flex items-center gap-2 mb-2 justify-center">
                {{-- “Attend / Skip” choice for this top block --}}
                <label class="inline-flex items-center gap-1">
                  <input type="radio" name="schedule[{{ strtolower($d) }}][0][choice]" value="attend">
                  <span class="text-sm">Attend</span>
                </label>
                <label class="inline-flex items-center gap-1">
                  <input type="radio" name="schedule[{{ strtolower($d) }}][0][choice]" value="skip">
                  <span class="text-sm">Skip</span>
                </label>
              </div>
              <select name="schedule[{{ strtolower($d) }}][0][subject]" class="w-full border rounded px-2 py-2">
                <option value="">— Select subject —</option>
                @foreach($subjects as $s)<option>{{ $s }}</option>@endforeach
              </select>
            </td>
          @endforeach
        </tr>

        {{-- Remaining three time slots per day --}}
        @for($i=1; $i<4; $i++)
          <tr>
            @foreach($days as $d)
              <td class="align-top border-r border-b p-3">
                <div class="text-center text-sm text-gray-700 mb-2">{{ $slots[$d][$i] }}</div>
                <div class="flex items-center gap-2 mb-2 justify-center">
                  <label class="inline-flex items-center gap-1">
                    <input type="radio" name="schedule[{{ strtolower($d) }}][{{ $i }}][choice]" value="attend">
                    <span class="text-sm">Attend</span>
                  </label>
                  <label class="inline-flex items-center gap-1">
                    <input type="radio" name="schedule[{{ strtolower($d) }}][{{ $i }}][choice]" value="skip">
                    <span class="text-sm">Skip</span>
                  </label>
                </div>
                <select name="schedule[{{ strtolower($d) }}][{{ $i }}][subject]" class="w-full border rounded px-2 py-2">
                  <option value="">— Select subject —</option>
                  @foreach($subjects as $s)<option>{{ $s }}</option>@endforeach
                </select>
              </td>
            @endforeach
          </tr>
        @endfor
      </tbody>
    </table>
  </div>

  <div class="bg-white border rounded-xl p-4">
    <label class="text-sm">Deposit (£)</label>
    <input name="deposit" class="border rounded w-full p-2" placeholder="0.00" inputmode="decimal">
    <p class="text-xs text-gray-500 mt-1">If set, a paid invoice + transaction is recorded automatically.</p>
  </div>

  <div class="flex justify-between">
    <a href="{{ route('students.create') }}" class="px-4 py-2 rounded bg-gray-200">← Back</a>
    <button class="px-6 py-2 rounded bg-green-600 text-white hover:bg-green-700">Save</button>
  </div>
</form>

{{-- tiny styles to make the grid look neat --}}
<style>
  table.timetable th, table.timetable td { border-color:#e5e7eb; } /* gray-200 */
  table.timetable th { font-size:14px; }
  .admission-form input[type="radio"]{ width:16px; height:16px; }
  .admission-form input[type="checkbox"]{ width:16px; height:16px; margin-right:.5rem; vertical-align:middle; } /* spacing fix */
</style>

@endsection
