@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">View Attendance</h1>

<form method="GET">
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
    <input name="reference" value="{{ $reference }}" class="border p-2" placeholder="Reference">
    <input name="teacher" value="{{ $teacher }}" class="border p-2" placeholder="Teacher (optional)">
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
    
    <!-- Reset Button -->
    <a href="{{ route('attendance.view') }}" class="bg-gray-200 rounded px-4 py-2 hover:bg-gray-300">Reset</a>
  </div>
</form>
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
