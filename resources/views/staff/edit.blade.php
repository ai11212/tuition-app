@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Edit Staff</h1>
<form method="POST" action="{{ route('staff.update',$staff) }}" class="grid grid-cols-2 gap-4">@csrf @method('PUT')
<input name="name" class="border p-2" value="{{ $staff->name }}">
<input name="role" class="border p-2" value="{{ $staff->role }}">
<input name="email" class="border p-2" value="{{ $staff->email }}">
<input name="phone" class="border p-2" value="{{ $staff->phone }}">
<button class="bg-blue-600 text-white rounded px-4 py-2 col-span-2">Update</button>
</form>
@endsection
