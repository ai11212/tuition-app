@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Invoice {{ $invoice->reference }}</h1>
<div class="mb-4">Student: <b>{{ $invoice->student->full_name }}</b> ({{ $invoice->student->reference }})</div>
<div class="mb-4">Period: {{ $invoice->period_from }} → {{ $invoice->period_to }}</div>
<div class="mb-4">Amount: <b>{{ number_format($invoice->amount,2) }}</b></div>
<h2 class="font-semibold mb-2">Transactions</h2>
<table class="w-full">
<tr class="border-b bg-gray-50"><th class="p-2 text-left">Date</th><th>Amount</th><th>Method</th></tr>
@foreach($invoice->transactions as $t)
<tr class="border-b"><td class="p-2">{{ $t->paid_on }}</td><td>{{ number_format($t->amount,2) }}</td><td>{{ $t->method }}</td></tr>
@endforeach
</table>
<button onclick="window.print()" class="mt-4 bg-gray-200 px-3 py-2 rounded">Print</button>
@endsection
