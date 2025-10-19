@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Student Search</h1>
<form action="{{ route('ref.show') }}" class="max-w-2xl">
  <div class="grid md:grid-cols-2 gap-4 mb-4">
    <div>
      <label class="block text-sm font-medium mb-1">Reference</label>
      <input name="reference" value="{{ request('reference') }}" class="border p-2 w-full" placeholder="e.g., A1001">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Date of Birth</label>
      <input type="date" name="dob" value="{{ request('dob') }}" class="border p-2 w-full">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">First Name</label>
      <input name="first_name" value="{{ request('first_name') }}" class="border p-2 w-full" placeholder="First name">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Last Name</label>
      <input name="last_name" value="{{ request('last_name') }}" class="border p-2 w-full" placeholder="Last name">
    </div>
  </div>
  <div class="flex gap-3">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
    <a href="{{ route('ref.form') }}" class="bg-gray-200 px-4 py-2 rounded">Clear</a>
  </div>
</form>
@endsection
