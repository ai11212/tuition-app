@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Reference Profile</h1>
<form action="{{ route('ref.show') }}" class="flex gap-3">
  <input name="reference" class="border p-2" placeholder="Ref (e.g., A1001)">
  <button class="bg-blue-600 text-white px-3 py-2 rounded">Show</button>
</form>
@endsection
