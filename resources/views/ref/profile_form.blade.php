@extends('layouts.app')
@section('content')
@include('partials.flash')

<div class="max-w-5xl mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Student Search</h1>
    <p class="text-sm text-gray-500">Search by any combination of the fields below.</p>
  </div>

  <div class="bg-white rounded-xl border shadow-sm p-5">
    <form action="{{ route('ref.show') }}">
      {{-- Row 1: Reference | Date of Birth | Year --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Reference</label>
          <input name="reference" value="{{ request('reference') }}"
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="e.g. A1001">
        </div>
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Date of Birth</label>
          <input type="date" name="dob" value="{{ request('dob') }}"
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Year</label>
          <input type="number" name="year" min="1" max="30" value="{{ request('year') }}"
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="e.g. 7">
        </div>
      </div>

      {{-- Row 2: First Name | Last Name --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">First Name</label>
          <input name="first_name" value="{{ request('first_name') }}"
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="First name">
        </div>
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Last Name</label>
          <input name="last_name" value="{{ request('last_name') }}"
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 placeholder="Last name">
        </div>
      </div>

      {{-- Row 3: Buttons --}}
      <div class="flex flex-wrap gap-3">
        <button type="submit"
                class="h-10 px-6 inline-flex items-center justify-center bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">Search</button>
        <a href="{{ route('ref.form') }}"
           class="h-10 px-6 inline-flex items-center justify-center border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">Clear</a>
      </div>
    </form>
  </div>
</div>
@endsection
