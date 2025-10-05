@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Staff Attendance</h1>
<form class="mb-4">
  <input type="date" name="date" value="{{ $date }}" class="border p-2">
  <button class="px-3 py-2 bg-gray-200 rounded">Load</button>
</form>
<form method="POST" action="{{ route('attendance.staff.save') }}">@csrf
  <input type="hidden" name="date" value="{{ $date }}">
  <table class="w-full">
    <tr class="bg-gray-50 border-b"><th class="p-2 text-left">Staff</th><th>Present</th><th>Absent</th></tr>
    @foreach($staff as $m)
      @php $st = optional(\App\Models\StaffAttendance::where(['staff_id'=>$m->id,'date'=>$date])->first())->status; @endphp
      <tr class="border-b">
        <td class="p-2">{{ $m->name }} ({{ $m->role }})</td>
        <td class="text-center"><input type="radio" name="statuses[{{ $m->id }}]" value="present" {{ $st=='present'?'checked':'' }}></td>
        <td class="text-center"><input type="radio" name="statuses[{{ $m->id }}]" value="absent" {{ $st=='absent'?'checked':'' }}></td>
      </tr>
    @endforeach
  </table>
  <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Save</button>
</form>
@endsection
