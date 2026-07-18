@extends('layouts.app')

@section('title', 'Edit Salary')

@section('content')
@include('partials.flash')

<div class="max-w-3xl mx-auto">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">✏️ Edit Salary</h1>
      <p class="text-sm text-gray-500">{{ $salary->teacher_name }} · {{ $salary->date_from->format('d/m/Y') }} – {{ $salary->date_to->format('d/m/Y') }} · {{ $salary->hours }} hrs × £{{ number_format($salary->hourly_rate, 2) }} = <strong>£{{ number_format($salary->gross, 2) }}</strong> (amount not editable)</p>
    </div>
    <span class="font-mono text-sm font-semibold px-3 py-1.5 rounded-full bg-blue-100 text-blue-800">{{ $salary->reference }}</span>
  </div>

  <div class="bg-white rounded-xl border shadow-sm p-5">
    <form method="POST" action="{{ route('salaries.update', $salary) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf
      @method('PUT')

      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Payment Date</label>
        <input type="date" name="payment_date" value="{{ old('payment_date', $salary->payment_date->format('Y-m-d')) }}" required
               class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('payment_date') border-red-500 @enderror">
        @error('payment_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Payment Method</label>
        <select name="payment_method" required class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Cash" {{ old('payment_method', $salary->payment_method) === 'Cash' ? 'selected' : '' }}>Cash</option>
          <option value="Bank" {{ old('payment_method', $salary->payment_method) === 'Bank' ? 'selected' : '' }}>Bank Transfer</option>
          <option value="Card" {{ old('payment_method', $salary->payment_method) === 'Card' ? 'selected' : '' }}>Card</option>
        </select>
        @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Reference Number</label>
        <input type="text" name="reference" value="{{ old('reference', $salary->reference) }}" maxlength="20" required
               class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reference') border-red-500 @enderror">
        @error('reference')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Notes</label>
        <input type="text" name="notes" value="{{ old('notes', $salary->notes) }}" maxlength="255"
               class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="md:col-span-2 flex gap-3">
        <button type="submit" class="h-10 px-6 inline-flex items-center justify-center bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">💾 Update Salary</button>
        <a href="{{ route('salaries.index') }}" class="h-10 px-6 inline-flex items-center justify-center border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
