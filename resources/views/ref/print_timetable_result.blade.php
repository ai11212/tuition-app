@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Time Table — Ref {{ $reference }}</h1>
<table class="w-full">
<tr class="bg-gray-50 border-b"><th class="p-2 text-left">Day</th><th>Start</th><th>End</th><th>Subject</th><th>Teacher</th><th>Room</th></tr>
@foreach($rows as $t)
<tr class="border-b"><td class="p-2">{{ $t->day_of_week }}</td><td>{{ $t->start_time }}</td><td>{{ $t->end_time }}</td><td>{{ $t->subject }}</td><td>{{ $t->teacher_name }}</td><td>{{ $t->room }}</td></tr>
@endforeach
</table>
<button onclick="window.print()" class="mt-4 bg-gray-200 px-3 py-2 rounded">Print</button>
@endsection
