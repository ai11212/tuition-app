@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Reference: {{ $reference }}</h1>
@if($student)
  <div class="mb-4 p-4 bg-gray-50 rounded">
    <div class="font-semibold">{{ $student->full_name }}</div>
    <div>{{ $student->phone }} {{ $student->email ? '• '.$student->email : '' }}</div>
    <a class="text-blue-600" href="{{ route('students.edit',$student) }}">Edit student</a>
  </div>
@else
  <div class="mb-4 text-gray-600">No student found for this reference.</div>
@endif
<h2 class="font-semibold mb-2">Time Table</h2>
<table class="w-full">
<tr class="bg-gray-50 border-b"><th class="p-2 text-left">Day</th><th>Start</th><th>End</th><th>Subject</th><th>Teacher</th><th>Room</th></tr>
@foreach($timetable as $t)
<tr class="border-b"><td class="p-2">{{ $t->day_of_week }}</td><td>{{ $t->start_time }}</td><td>{{ $t->end_time }}</td><td>{{ $t->subject }}</td><td>{{ $t->teacher_name }}</td><td>{{ $t->room }}</td></tr>
@endforeach
</table>
@endsection
