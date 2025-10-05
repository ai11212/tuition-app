@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Attendance Data (Class status)</h1>
<form class="mb-4">
  <input type="date" name="date" value="{{ $date }}" class="border p-2">
  <button class="bg-gray-200 px-3 py-2 rounded">Show</button>
</form>
<table class="w-full">
<tr class="bg-gray-50 border-b"><th class="p-2 text-left">Student</th><th>Status</th></tr>
@foreach($students as $s)
@php $st=$rows[$s->id]->status ?? '—'; @endphp
<tr class="border-b">
  <td class="p-2">{{ $s->full_name }}</td>
  <td class="{{ $st=='present'?'text-green-700':($st=='absent'?'text-red-700':'text-gray-500') }}">{{ $st=='—'?'No record':ucfirst($st) }}</td>
</tr>
@endforeach
</table>
@endsection
