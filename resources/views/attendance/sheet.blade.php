@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Attendance</h1>
<form class="flex gap-3 mb-4">
  <input type="date" name="date" value="{{ $date }}" class="border p-2">
  <input name="reference" placeholder="Reference" value="{{ $reference }}" class="border p-2">
  <select name="time" class="border p-2">
    <option value="">Select Time</option>
    <option value="12:00 PM — 2:00 PM" {{ (request('time')=='12:00 PM — 2:00 PM') ? 'selected' : '' }}>12:00 PM — 2:00 PM</option>
    <option value="2:15 PM — 4:15 PM" {{ (request('time')=='2:15 PM — 4:15 PM') ? 'selected' : '' }}>2:15 PM — 4:15 PM</option>
    <option value="4:45 PM — 6:45 PM" {{ (request('time')=='4:45 PM — 6:45 PM') ? 'selected' : '' }}>4:45 PM — 6:45 PM</option>
    <option value="7:00 PM — 9:00 PM" {{ (request('time')=='7:00 PM — 9:00 PM') ? 'selected' : '' }}>7:00 PM — 9:00 PM</option>
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
@endsection
