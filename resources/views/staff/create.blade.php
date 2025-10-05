@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Add Staff</h1>
<form method="POST" action="{{ route('staff.store') }}" class="grid grid-cols-2 gap-4">@csrf
<input name="name" class="border p-2" placeholder="Name">
<input name="role" class="border p-2" placeholder="Role (e.g., teacher)">
<input name="email" class="border p-2" placeholder="Email">
<input name="phone" class="border p-2" placeholder="Phone">
<button class="bg-blue-600 text-white rounded px-4 py-2 col-span-2">Save</button>
</form>
@endsection
