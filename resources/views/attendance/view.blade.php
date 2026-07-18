@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">View Attendance</h1>

<form method="GET">
  @if(!empty($mode))
    <input type="hidden" name="mode" value="{{ $mode }}">
  @endif
  <!-- Row 1: Main Filter Inputs -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-3">
    <div>
      <label class="block text-xs text-gray-600 mb-1">From Date</label>
      <input type="date" name="from" value="{{ $from }}" class="border p-2 w-full">
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">To Date</label>
      <input type="date" name="to" value="{{ $to }}" class="border p-2 w-full">
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Reference</label>
      <input name="reference" value="{{ $reference }}" class="border p-2 w-full" placeholder="Reference">
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Teacher</label>
      <select name="teacher" class="border p-2 w-full" onchange="this.form.submit()">
        <option value="">All Teachers</option>
        @foreach($teachers as $t)
          <option value="{{ $t->name }}" {{ $teacher == $t->name ? 'selected' : '' }}>{{ $t->name }}{{ $t->reference ? ' (' . $t->reference . ')' : '' }}</option>
        @endforeach
      </select>
    </div>
  </div>
  
  <!-- Row 2: Action Buttons & Statistics -->
  <div class="flex gap-3 mb-4 items-center flex-wrap">
    <!-- Filter Button -->
    <button type="submit" class="bg-gray-200 rounded px-4 py-2 hover:bg-gray-300">Filter</button>
    
    <!-- Time Slot Selector -->
    <div class="border rounded p-2 flex items-center gap-2 bg-white min-w-[180px] sm:min-w-[200px]">
      <span class="text-lg">⏰</span>
      <select name="stats_time" id="stats-time" class="border-0 outline-none text-sm bg-transparent flex-1" onchange="this.form.submit()">
        <option value="">All Time Slots</option>
        @foreach($timeSlots as $slot)
          <option value="{{ $slot }}" {{ $statsTime == $slot ? 'selected' : '' }}>{{ $slot }}</option>
        @endforeach
      </select>
    </div>
    
    <!-- Present Count Text -->
    <span class="text-gray-700 text-sm whitespace-nowrap">
      Number of students: <strong>{{ $presentCount }}</strong>
    </span>
    
    <!-- Date Selector -->
    <div class="border rounded p-2 flex items-center gap-2 bg-white min-w-[150px] sm:min-w-[160px]">
      <span class="text-lg">📅</span>
      <select name="stats_date" id="stats-date" class="border-0 outline-none text-sm bg-transparent flex-1" onchange="this.form.submit()">
        <option value="">Select Date</option>
        @foreach($availableDates as $date)
          <option value="{{ $date }}" {{ $statsDate == $date ? 'selected' : '' }}>
            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
          </option>
        @endforeach
      </select>
    </div>
    
    <!-- Present / Absent quick views -->
    <button type="submit" name="mode" value="present"
            class="rounded px-4 py-2 font-medium {{ ($mode ?? null) === 'present' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">Present</button>
    <button type="submit" name="mode" value="absent"
            class="rounded px-4 py-2 font-medium {{ ($mode ?? null) === 'absent' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">Absent</button>

    <!-- Reset Button -->
    <a href="{{ route('attendance.view') }}" class="bg-gray-200 rounded px-4 py-2 hover:bg-gray-300">Reset</a>
  </div>
</form>

@if(($mode ?? null) === 'absent')
<div class="mb-3 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">
  Students who missed their timetable
  @if($absentDate)
    on <strong>{{ \Carbon\Carbon::parse($absentDate)->format('d/m/Y') }}</strong>
  @else
    between <strong>{{ \Carbon\Carbon::parse($absentFrom)->format('d/m/Y') }}</strong> and <strong>{{ \Carbon\Carbon::parse($absentTo)->format('d/m/Y') }}</strong>
  @endif
  — no attendance marked{{ $statsTime ? ' for ' . $statsTime : '' }}.
</div>
<div class="overflow-x-auto">
  <table class="w-full min-w-[900px]">
  <tr class="bg-gray-50 border-b"><th class="p-2 text-left">Date</th><th>Student Name</th><th>Parent Phone</th><th>Reference</th><th>Scheduled slot</th><th>Subject</th><th>Status</th></tr>
  @forelse($absentRows as $a)
  <tr class="border-b">
    <td class="p-2">{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y') }}</td>
    <td>{{ $a->student->first_name }} {{ $a->student->last_name }}</td>
    <td>
      @if($a->student->guardian_phone)
        <a href="tel:{{ $a->student->guardian_phone }}" class="text-blue-600 hover:underline" title="Call {{ $a->student->guardian_name ?: 'parent' }}">📞 {{ $a->student->guardian_phone }}</a>
      @else
        <span class="text-gray-400">-</span>
      @endif
    </td>
    <td>{{ $a->reference }}</td>
    <td>{{ $a->slot }}</td>
    <td>{{ $a->subject ?? '-' }}</td>
    <td><span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Absent</span></td>
  </tr>
  @empty
  <tr><td colspan="7" class="p-4 text-center text-gray-500">Everyone scheduled for this day has attendance marked 🎉</td></tr>
  @endforelse
  </table>
</div>
@else
@if(($mode ?? null) === 'present')
<div class="mb-3 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">Showing <strong>Present</strong> records only.</div>
@endif
<div class="overflow-x-auto">
  <table class="w-full min-w-[900px]">
  <tr class="bg-gray-50 border-b"><th class="p-2 text-left">Date</th><th>Student Name</th><th>Reference</th><th>Time slot</th><th>Subject</th><th>Teacher</th><th>Status</th><th class="text-center">Actions</th></tr>
  @forelse($rows as $r)
<tr class="border-b">
  <td class="p-2">{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
  <td>{{ $r->student ? $r->student->first_name . ' ' . $r->student->last_name : '' }}</td>
  <td>{{ $r->student?->reference }}</td>
  <td>{{ $r->time ?? '-' }}</td>
  <td>{{ $r->subject ?? '-' }}</td>
  <td>{{ $r->teacher ?? '-' }}</td>
  <td class="{{ $r->status=='present'?'text-green-700':'text-red-700' }}">{{ ucfirst($r->status) }}</td>
  <td class="text-center">
    <button type="button" class="text-blue-600 hover:text-blue-800 mr-2" title="Edit"
            data-bs-toggle="modal" data-bs-target="#editAttendanceModal"
            onclick="editAttendance({{ $r->id }}, '{{ $r->date }}', '{{ $r->status }}', '{{ addslashes($r->subject ?? '') }}', '{{ addslashes($r->time ?? '') }}', '{{ addslashes($r->teacher ?? '') }}', '{{ addslashes($r->student ? $r->student->first_name . ' ' . $r->student->last_name : '') }}')">
      ✏️
    </button>
    <form method="POST" action="{{ route('attendance.destroy', $r->id) }}" onsubmit="return confirm('Are you sure you want to delete this attendance record?');" style="display:inline;">
      @csrf
      @method('DELETE')
      <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
        🗑️
      </button>
    </form>
  </td>
</tr>
@empty
<tr>
  <td colspan="8" class="p-4 text-center text-gray-500">No records found</td>
</tr>
@endforelse
  </table>
</div>
@endif

{{-- Edit Attendance Modal --}}
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="edit-attendance-form">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Attendance — <span id="edit-att-student"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Date *</label>
            <input type="date" name="date" id="edit-att-date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status *</label>
            <select name="status" id="edit-att-status" class="form-select" required>
              <option value="present">Present</option>
              <option value="absent">Absent</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Time slot</label>
            <select name="time" id="edit-att-time" class="form-select">
              <option value="">— None —</option>
              @foreach($allTimeSlots as $slot)
                <option value="{{ $slot }}">{{ $slot }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" id="edit-att-subject" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Teacher</label>
            <select name="teacher" id="edit-att-teacher" class="form-select">
              <option value="">— None —</option>
              @foreach($teachers as $t)
                <option value="{{ $t->name }}">{{ $t->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Prefill and open the edit-attendance modal
function setSelectValue(select, value) {
    if (value && !Array.from(select.options).some(function (o) { return o.value === value; })) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = value;
        select.appendChild(opt);
    }
    select.value = value || '';
}
function editAttendance(id, date, status, subject, time, teacher, studentName) {
    document.getElementById('edit-attendance-form').action = '/attendance/' + id;
    document.getElementById('edit-att-student').textContent = studentName;
    document.getElementById('edit-att-date').value = date;
    document.getElementById('edit-att-status').value = status;
    document.getElementById('edit-att-subject').value = subject;
    setSelectValue(document.getElementById('edit-att-time'), time);
    setSelectValue(document.getElementById('edit-att-teacher'), teacher);
}
</script>

<script>
// Dynamic time slot update based on selected date (same logic as attendance sheet)
document.getElementById('stats-date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const dayOfWeek = selectedDate.getDay(); // 0=Sunday, 1=Monday, ..., 5=Friday, 6=Saturday
    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
    const isFriday = (dayOfWeek === 5);
    
    const timeSelect = document.getElementById('stats-time');
    const currentValue = timeSelect.value;
    
    // Define time slot options
    const weekendTimes = [
        '9:00 AM — 11:00 AM',
        '11:15 AM — 1:15 PM',
        '2:15 PM — 4:15 PM',
        '4:30 PM — 6:30 PM'
    ];
    
    const fridayTimes = [
        '9:00 AM — 11:00 AM',
        '11:15 AM — 1:15 PM',
        '4:45 PM — 6:45 PM',
        '7:00 PM — 9:00 PM'
    ];
    
    const weekdayTimes = [
        '12:00 PM — 2:00 PM',
        '2:15 PM — 4:15 PM',
        '4:45 PM — 6:45 PM',
        '7:00 PM — 9:00 PM'
    ];
    
    // Build new options HTML
    let optionsHtml = '<option value="">All Time Slots</option>';
    let timesToUse;
    
    if (isWeekend) {
        timesToUse = weekendTimes;
    } else if (isFriday) {
        timesToUse = fridayTimes;
    } else {
        timesToUse = weekdayTimes;
    }
    
    timesToUse.forEach(time => {
        optionsHtml += `<option value="${time}">${time}</option>`;
    });
    
    // Update the select element
    timeSelect.innerHTML = optionsHtml;
    
    // Auto-submit form to get updated count
    this.form.submit();
});
</script>

@endsection
