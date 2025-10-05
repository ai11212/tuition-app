@extends('layouts.app')
@section('content')
@include('partials.flash')

@php
    // Safe defaults so the page never crashes if controller vars are missing
    $inTotal  = $inTotal  ?? ($paid ?? 0);
    $outTotal = $outTotal ?? 0;
    $net      = $net      ?? ($inTotal - $outTotal);

    // Breakdown defaults
    $breakdown = array_merge(['cash'=>0,'card'=>0,'bank'=>0], $breakdown ?? []);

    // Tables
    $daily   = $daily   ?? [];
    $weekly  = $weekly  ?? [];
    $monthly = $monthly ?? [];
@endphp

<h1 class="text-2xl font-semibold mb-3">Accounts Summary</h1>

<form class="flex flex-wrap items-end gap-2 mb-4">
  <div>
    <label class="text-sm text-gray-600">From</label>
    <input type="date" name="from" value="{{ $from ?? now()->startOfMonth()->toDateString() }}" class="border p-2 rounded">
  </div>
  <div>
    <label class="text-sm text-gray-600">To</label>
    <input type="date" name="to" value="{{ $to ?? now()->toDateString() }}" class="border p-2 rounded">
  </div>
  <button class="px-4 py-2 rounded bg-indigo-600 text-white">Apply</button>
</form>

<div class="grid md:grid-cols-3 gap-4 mb-6">
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-gray-600">Money In</div>
    <div class="text-3xl font-semibold mt-1">£{{ number_format((float)$inTotal,2) }}</div>
    <div class="text-xs text-gray-600 mt-2">
      Cash £{{ number_format((float)($breakdown['cash'] ?? 0),2) }} •
      Card £{{ number_format((float)($breakdown['card'] ?? 0),2) }} •
      Bank £{{ number_format((float)($breakdown['bank'] ?? 0),2) }}
    </div>
  </div>
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-gray-600">Money Out</div>
    <div class="text-3xl font-semibold mt-1 text-red-600">£{{ number_format((float)$outTotal,2) }}</div>
    <div class="text-xs text-gray-600 mt-2">Expenses & refunds</div>
  </div>
  <div class="p-4 rounded-xl border bg-white">
    <div class="text-sm text-gray-600">Net</div>
    <div class="text-3xl font-semibold mt-1 {{ ($net ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
      £{{ number_format((float)$net,2) }}
    </div>
    <div class="text-xs text-gray-600 mt-2">In − Out</div>
  </div>
</div>

{{-- Daily Cash Flow --}}
<div class="mb-6 bg-white border rounded-xl overflow-hidden">
  <div class="px-4 py-3 border-b font-semibold">Daily Cash Flow</div>
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr><th class="p-2 text-left">Date</th><th class="p-2 text-right">In (£)</th><th class="p-2 text-right">Out (£)</th><th class="p-2 text-right">Net (£)</th></tr>
    </thead>
    <tbody>
      @forelse($daily as $row)
        <tr class="border-t">
          <td class="p-2">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
          <td class="p-2 text-right text-emerald-700">£{{ number_format((float)($row['in']  ?? 0),2) }}</td>
          <td class="p-2 text-right text-red-700">£{{ number_format((float)($row['out'] ?? 0),2) }}</td>
          <td class="p-2 text-right">{{ ($row['net'] ?? 0) >= 0 ? '+' : '' }}£{{ number_format((float)($row['net'] ?? 0),2) }}</td>
        </tr>
      @empty
        <tr><td class="p-3 text-gray-500" colspan="4">No activity</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Weekly Summary --}}
<div class="mb-6 bg-white border rounded-xl overflow-hidden">
  <div class="px-4 py-3 border-b font-semibold">Weekly Summary</div>
  <table class="w-full text-sm">
    <thead class="bg-gray-50"><tr><th class="p-2 text-left">Year</th><th class="p-2">Week</th><th class="p-2 text-right">In (£)</th><th class="p-2 text-right">Out (£)</th><th class="p-2 text-right">Net (£)</th></tr></thead>
    <tbody>
      @forelse($weekly as $r)
      <tr class="border-t">
        <td class="p-2">{{ $r['year'] ?? '' }}</td>
        <td class="p-2 text-center">{{ $r['week'] ?? '' }}</td>
        <td class="p-2 text-right text-emerald-700">£{{ number_format((float)($r['in']  ?? 0),2) }}</td>
        <td class="p-2 text-right text-red-700">£{{ number_format((float)($r['out'] ?? 0),2) }}</td>
        <td class="p-2 text-right">{{ ($r['net'] ?? 0) >= 0 ? '+' : '' }}£{{ number_format((float)($r['net'] ?? 0),2) }}</td>
      </tr>
      @empty
      <tr><td class="p-3 text-gray-500" colspan="5">No activity</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Monthly Summary --}}
<div class="mb-6 bg-white border rounded-xl overflow-hidden">
  <div class="px-4 py-3 border-b font-semibold">Monthly Summary</div>
  <table class="w-full text-sm">
    <thead class="bg-gray-50"><tr><th class="p-2 text-left">Year</th><th class="p-2">Month</th><th class="p-2 text-right">In (£)</th><th class="p-2 text-right">Out (£)</th><th class="p-2 text-right">Net (£)</th></tr></thead>
    <tbody>
      @forelse($monthly as $r)
      <tr class="border-t">
        <td class="p-2">{{ $r['year'] ?? '' }}</td>
        <td class="p-2 text-center">{{ \Carbon\Carbon::create()->month((int)($r['month'] ?? 1))->format('M') }}</td>
        <td class="p-2 text-right text-emerald-700">£{{ number_format((float)($r['in']  ?? 0),2) }}</td>
        <td class="p-2 text-right text-red-700">£{{ number_format((float)($r['out'] ?? 0),2) }}</td>
        <td class="p-2 text-right">{{ ($r['net'] ?? 0) >= 0 ? '+' : '' }}£{{ number_format((float)($r['net'] ?? 0),2) }}</td>
      </tr>
      @empty
      <tr><td class="p-3 text-gray-500" colspan="5">No activity</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
