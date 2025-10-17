@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Attendance</h1>
<form class="flex gap-3 mb-4">
  <input type="date" name="date" id="attendance-date" value="{{ $date }}" class="border p-2">
  <input name="reference" placeholder="Reference" value="{{ $reference }}" class="border p-2">
  <select name="time" id="time-select" class="border p-2">
    <option value="">Select Time</option>
    @if($isWeekend)
      {{-- Weekend Times (Saturday & Sunday) --}}
      <option value="9:00 AM — 11:00 AM" {{ (request('time')=='9:00 AM — 11:00 AM') ? 'selected' : '' }}>9:00 AM — 11:00 AM</option>
      <option value="11:15 AM — 1:15 PM" {{ (request('time')=='11:15 AM — 1:15 PM') ? 'selected' : '' }}>11:15 AM — 1:15 PM</option>
      <option value="2:15 PM — 4:15 PM" {{ (request('time')=='2:15 PM — 4:15 PM') ? 'selected' : '' }}>2:15 PM — 4:15 PM</option>
      <option value="4:30 PM — 6:30 PM" {{ (request('time')=='4:30 PM — 6:30 PM') ? 'selected' : '' }}>4:30 PM — 6:30 PM</option>
    @elseif($isFriday)
      {{-- Friday Times --}}
      <option value="9:00 AM — 11:00 AM" {{ (request('time')=='9:00 AM — 11:00 AM') ? 'selected' : '' }}>9:00 AM — 11:00 AM</option>
      <option value="11:15 AM — 1:15 PM" {{ (request('time')=='11:15 AM — 1:15 PM') ? 'selected' : '' }}>11:15 AM — 1:15 PM</option>
      <option value="4:45 PM — 6:45 PM" {{ (request('time')=='4:45 PM — 6:45 PM') ? 'selected' : '' }}>4:45 PM — 6:45 PM</option>
      <option value="7:00 PM — 9:00 PM" {{ (request('time')=='7:00 PM — 9:00 PM') ? 'selected' : '' }}>7:00 PM — 9:00 PM</option>
    @else
      {{-- Weekday Times (Monday - Thursday) --}}
      <option value="12:00 PM — 2:00 PM" {{ (request('time')=='12:00 PM — 2:00 PM') ? 'selected' : '' }}>12:00 PM — 2:00 PM</option>
      <option value="2:15 PM — 4:15 PM" {{ (request('time')=='2:15 PM — 4:15 PM') ? 'selected' : '' }}>2:15 PM — 4:15 PM</option>
      <option value="4:45 PM — 6:45 PM" {{ (request('time')=='4:45 PM — 6:45 PM') ? 'selected' : '' }}>4:45 PM — 6:45 PM</option>
      <option value="7:00 PM — 9:00 PM" {{ (request('time')=='7:00 PM — 9:00 PM') ? 'selected' : '' }}>7:00 PM — 9:00 PM</option>
    @endif
  </select>
  <input name="subject" placeholder="Subject/Theme" value="{{ $subject }}" class="border p-2">
  <button class="px-3 py-2 bg-gray-200 rounded">Load</button>
</form>
@if(request('reference') && $students->count() === 1)
  <form method="POST" action="{{ route('attendance.save') }}">@csrf
    <input type="hidden" name="date" value="{{ $date }}">
    <input type="hidden" name="subject" value="{{ $subject }}">
    <input type="hidden" name="time" value="{{ request('time') }}">
    <table class="w-full">
      <tr class="bg-gray-50 border-b"><th class="p-2 text-left">Student</th><th>Present</th><th>Absent</th></tr>
      @php $s = $students->first(); $sel = optional(\App\Models\StudentAttendance::where(['student_id'=>$s->id,'date'=>$date,'subject'=>$subject,'time'=>request('time')])->first())->status; @endphp
      <tr class="border-b">
        <td class="p-2">{{ $s->full_name }} ({{ $s->reference }})</td>
        <td class="text-center"><input type="radio" name="statuses[{{ $s->id }}]" value="present" {{ $sel=='present'?'checked':'' }}></td>
        <td class="text-center"><input type="radio" name="statuses[{{ $s->id }}]" value="absent"  {{ $sel=='absent'?'checked':'' }}></td>
      </tr>
    </table>
    <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Save</button>
  </form>
@elseif(request('reference'))
  <div class="text-gray-600">No student found for this reference.</div>
@else
  <div class="text-gray-600">Enter a reference number and time slot to log attendance.</div>
@endif

<script>
// Dynamic time slot update based on selected date
document.getElementById('attendance-date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const dayOfWeek = selectedDate.getDay(); // 0=Sunday, 1=Monday, ..., 5=Friday, 6=Saturday
    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
    const isFriday = (dayOfWeek === 5);
    
    const timeSelect = document.getElementById('time-select');
    const currentValue = timeSelect.value;
    
    // Define time slot options
    const weekendTimes = [
        { value: '9:00 AM — 11:00 AM', label: '9:00 AM — 11:00 AM' },
        { value: '11:15 AM — 1:15 PM', label: '11:15 AM — 1:15 PM' },
        { value: '2:15 PM — 4:15 PM', label: '2:15 PM — 4:15 PM' },
        { value: '4:30 PM — 6:30 PM', label: '4:30 PM — 6:30 PM' }
    ];
    
    const fridayTimes = [
        { value: '9:00 AM — 11:00 AM', label: '9:00 AM — 11:00 AM' },
        { value: '11:15 AM — 1:15 PM', label: '11:15 AM — 1:15 PM' },
        { value: '4:45 PM — 6:45 PM', label: '4:45 PM — 6:45 PM' },
        { value: '7:00 PM — 9:00 PM', label: '7:00 PM — 9:00 PM' }
    ];
    
    const weekdayTimes = [
        { value: '12:00 PM — 2:00 PM', label: '12:00 PM — 2:00 PM' },
        { value: '2:15 PM — 4:15 PM', label: '2:15 PM — 4:15 PM' },
        { value: '4:45 PM — 6:45 PM', label: '4:45 PM — 6:45 PM' },
        { value: '7:00 PM — 9:00 PM', label: '7:00 PM — 9:00 PM' }
    ];
    
    // Build new options HTML
    let optionsHtml = '<option value="">Select Time</option>';
    let timesToUse;
    
    if (isWeekend) {
        timesToUse = weekendTimes;
    } else if (isFriday) {
        timesToUse = fridayTimes;
    } else {
        timesToUse = weekdayTimes;
    }
    
    timesToUse.forEach(time => {
        optionsHtml += `<option value="${time.value}">${time.label}</option>`;
    });
    
    // Update the select element
    timeSelect.innerHTML = optionsHtml;
    
    // Try to restore previous selection if it exists in new options
    if (currentValue) {
        const optionExists = Array.from(timeSelect.options).some(opt => opt.value === currentValue);
        if (optionExists) {
            timeSelect.value = currentValue;
        }
    }
    
    // Show visual feedback
    timeSelect.classList.add('border-blue-500');
    setTimeout(() => {
        timeSelect.classList.remove('border-blue-500');
    }, 300);
});

// Trigger on page load if date is pre-selected
window.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('attendance-date');
    if (dateInput.value) {
        // Trigger change event to set correct times on initial load
        dateInput.dispatchEvent(new Event('change'));
    }
});
</script>

@endsection
