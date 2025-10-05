@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Take Payment</h1>

<form class="flex gap-3 mb-4">
  <input name="reference" class="border p-2" placeholder="Reference" value="{{ $ref }}">
  <button class="bg-gray-200 px-3 py-2 rounded">Find</button>
</form>

@if($students->count())
<div class="mb-3 text-sm text-gray-700">Select student and fill payment details.</div>
<form method="POST" action="{{ route('payments.store') }}" class="grid md:grid-cols-5 gap-3">@csrf
  <select name="student_id" class="border p-2 col-span-2">
    @foreach($students as $s)
    <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->reference }})</option>
    @endforeach
  </select>
  <input type="date" name="period_from" class="border p-2" value="{{ now()->startOfMonth()->toDateString() }}">
  <input type="date" name="period_to" class="border p-2" value="{{ now()->toDateString() }}">
  <input name="amount" class="border p-2" placeholder="Amount">
  <select name="method" class="border p-2">
    <option>Cash</option><option>Card</option><option>Transfer</option>
  </select>
  <input name="notes" class="border p-2 md:col-span-5" placeholder="Notes (optional)">
  <button class="bg-blue-600 text-white px-4 py-2 rounded md:col-span-5">Finish</button>
</form>
@endif
@endsection
