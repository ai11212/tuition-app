@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Print Time Table</h1>
<form action="{{ route('tt.show') }}" class="flex gap-3">
  <input name="reference" class="border p-2" placeholder="Ref (e.g., A1001)">
  <button class="bg-gray-200 px-3 py-2 rounded">Show</button>
</form>
@endsection
