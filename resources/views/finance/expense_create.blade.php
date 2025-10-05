@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Add Expense / Refund</h1>
<form method="POST" action="{{ route('expenses.store') }}" class="grid md:grid-cols-3 gap-4 max-w-3xl">@csrf
  <div>
    <label class="text-sm text-gray-700">Date</label>
    <input type="date" name="expense_on" class="border p-2 rounded" value="{{ now()->toDateString() }}">
  </div>
  <div>
    <label class="text-sm text-gray-700">Amount (£)</label>
    <input name="amount" class="border p-2 rounded" placeholder="0.00">
  </div>
  <div>
    <label class="text-sm text-gray-700">Method</label>
    <select name="method" class="border p-2 rounded"><option>Cash</option><option>Card</option><option>Bank</option></select>
  </div>
  <div>
    <label class="text-sm text-gray-700">Type</label>
    <select name="type" class="border p-2 rounded"><option value="expense">Expense</option><option value="refund">Refund</option></select>
  </div>
  <div>
    <label class="text-sm text-gray-700">Category</label>
    <input name="category" class="border p-2 rounded" placeholder="Stationery / Rent / ...">
  </div>
  <div class="md:col-span-3">
    <label class="text-sm text-gray-700">Notes</label>
    <input name="notes" class="border p-2 rounded w-full" placeholder="Optional details">
  </div>
  <div class="md:col-span-3">
    <button class="px-5 py-2 rounded bg-blue-600 text-white">Save</button>
  </div>
</form>
@endsection
