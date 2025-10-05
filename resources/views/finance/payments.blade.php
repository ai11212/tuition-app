@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Payments</h1>
    <div class="flex gap-2">
      <a href="{{ route('payments.export', request()->only('ref','from','to')) }}" class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">Export CSV</a>
      <a href="{{ route('payments.print', request()->only('ref','from','to')) }}" target="_blank" class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">Print</a>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('payments') }}" class="grid md:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="text-sm text-gray-600">Reference</label>
      <input name="ref" value="{{ old('ref', $ref ?? '') }}" class="w-full mt-1 rounded-lg border-gray-300" placeholder="Type to search...">
    </div>
    <div>
      <label class="text-sm text-gray-600">From</label>
      <input type="date" name="from" value="{{ old('from', $from ?? '') }}" class="w-full mt-1 rounded-lg border-gray-300">
    </div>
    <div>
      <label class="text-sm text-gray-600">To</label>
      <input type="date" name="to" value="{{ old('to', $to ?? '') }}" class="w-full mt-1 rounded-lg border-gray-300">
    </div>
    <div class="flex items-end">
      <button class="w-full px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Search</button>
    </div>
  </form>

  {{-- Recorder --}}
  <div class="p-4 rounded-xl border bg-white mb-6">
    <h2 class="font-semibold mb-3">Record a Payment</h2>
    @if(session('ok')) <div class="mb-3 text-sm text-emerald-700">{{ session('ok') }}</div> @endif
    <form method="POST" action="{{ route('payments.store') }}" class="grid md:grid-cols-4 gap-3">
      @csrf
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Reference*</label>
        <input name="reference" value="{{ old('reference') }}" required class="w-full mt-1 rounded-lg border-gray-300">
      </div>
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Amount (£)*</label>
        <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}" required class="w-full mt-1 rounded-lg border-gray-300">
      </div>
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Method</label>
        <select name="method" class="w-full mt-1 rounded-lg border-gray-300">
          <option>Cash</option>
          <option>Card</option>
          <option>Bank</option>
          <option>Transfer</option>
        </select>
      </div>
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Paid at</label>
        <input type="datetime-local" name="paid_at" value="{{ old('paid_at') }}" class="w-full mt-1 rounded-lg border-gray-300">
      </div>
      <div class="md:col-span-4">
        <label class="text-sm text-gray-600">Notes</label>
        <textarea name="notes" rows="2" class="w-full mt-1 rounded-lg border-gray-300">{{ old('notes') }}</textarea>
      </div>
      <div class="md:col-span-4">
        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Add Payment</button>
      </div>
    </form>
  </div>

  {{-- Results --}}
  <div class="rounded-xl border bg-white overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Date / Time</th>
          <th class="px-3 py-2 text-left">Reference</th>
          <th class="px-3 py-2 text-left">Student</th>
          <th class="px-3 py-2 text-left">Method</th>
          <th class="px-3 py-2 text-right">Amount (£)</th>
          <th class="px-3 py-2 text-left">Invoice</th>
          <th class="px-3 py-2 text-left">Notes</th>
        </tr>
      </thead>
      <tbody>
        @forelse(($payments ?? []) as $p)
          @php
            $ts = $p->paid_at ?: ($p->paid_on ? $p->paid_on.' 00:00:00' : null);
            $when = $ts ? \Carbon\Carbon::parse($ts)->format('d/m/Y H:i') : '';
          @endphp
          <tr class="border-t">
            <td class="px-3 py-2 whitespace-nowrap">{{ $when }}</td>
            <td class="px-3 py-2">{{ $p->student_ref ?? '' }}</td>
            <td class="px-3 py-2">{{ $p->student_name ?? '' }}</td>
            <td class="px-3 py-2">{{ $p->method }}</td>
            <td class="px-3 py-2 text-right">£{{ number_format($p->amount,2) }}</td>
            <td class="px-3 py-2">{{ $p->invoice_ref ?? '' }}</td>
            <td class="px-3 py-2">{{ $p->notes }}</td>
          </tr>
        @empty
          <tr><td class="px-3 py-6 text-center text-gray-500" colspan="7">No payments found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(isset($payments))
    <div class="mt-4">
      {{ $payments->links() }}
    </div>
  @endif
</div>
@endsection
