@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Attendance</h1>
<form class="flex gap-3 mb-4">
  <input type="date" name="date" value="{{ $date }}" class="border p-2">
  <input name="reference" placeholder="Reference" value="{{ $reference }}" class="border p-2">
  <input name="subject" placeholder="Subject/Theme" value="{{ $subject }}" class="border p-2">
  <button class="px-3 py-2 bg-gray-200 rounded">Load</button>
</form>
@if($students->count())
<form method="POST" action="{{ route('attendance.save') }}">@csrf
  <input type="hidden" name="date" value="{{ $date }}">
  <input type="hidden" name="subject" value="{{ $subject }}">
  <table class="w-full">
    <tr class="bg-gray-50 border-b"><th class="p-2 text-left">Student</th><th>Present</th><th>Absent</th></tr>
    @foreach($students as $s)
      @php $sel = optional(\App\Models\StudentAttendance::where(['student_id'=>$s->id,'date'=>$date,'subject'=>$subject])->first())->status; @endphp
      <tr class="border-b">
        <td class="p-2">{{ $s->full_name }} ({{ $s->reference }})</td>
        <td class="text-center"><input type="radio" name="statuses[{{ $s->id }}]" value="present" {{ $sel=='present'?'checked':'' }}></td>
        <td class="text-center"><input type="radio" name="statuses[{{ $s->id }}]" value="absent"  {{ $sel=='absent'?'checked':'' }}></td>
      </tr>
    @endforeach
  </table>
  <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Save</button>
</form>
@else
<div class="text-gray-600">No students for this filter yet.</div>
@endif
@endsection
