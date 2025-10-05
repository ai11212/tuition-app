@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">View Attendance</h1>
<form class="grid md:grid-cols-5 gap-3 mb-4">
  <input type="date" name="from" value="{{ $from }}" class="border p-2" placeholder="From">
  <input type="date" name="to" value="{{ $to }}" class="border p-2" placeholder="To">
  <input name="reference" value="{{ $reference }}" class="border p-2" placeholder="Reference">
  <select name="student_id" class="border p-2">
    <option value="">Any student</option>
    @foreach($students as $s)
      <option value="{{ $s->id }}" @selected($studentId==$s->id)>{{ $s->full_name }} ({{ $s->reference }})</option>
    @endforeach
  </select>
  <input name="teacher" value="{{ $teacher }}" class="border p-2" placeholder="Teacher (optional)">
  <button class="bg-gray-200 rounded px-3 py-2">Filter</button>
</form>
<table class="w-full">
<tr class="bg-gray-50 border-b"><th class="p-2 text-left">Date</th><th>Student</th><th>Subject/Teacher</th><th>Status</th></tr>
@foreach($rows as $r)
<tr class="border-b">
  <td class="p-2">{{ $r->date }}</td>
  <td>{{ $r->student?->full_name }} ({{ $r->student?->reference }})</td>
  <td>{{ $r->subject ?? '-' }} {{ $r->teacher ? '(' . $r->teacher . ')' : '' }}</td>
  <td class="{{ $r->status=='present'?'text-green-700':'text-red-700' }}">{{ ucfirst($r->status) }}</td>
</tr>
@endforeach
</table>
@endsection
