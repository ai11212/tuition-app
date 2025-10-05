@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-2xl font-semibold mb-4">Change Password</h1>
<form method="POST" action="{{ route('account.password.save') }}" class="max-w-md space-y-4">@csrf
  <div>
    <label class="text-sm text-gray-700">Current password</label>
    <input type="password" name="current_password" class="border rounded w-full p-2" required>
  </div>
  <div>
    <label class="text-sm text-gray-700">New password</label>
    <input type="password" name="password" class="border rounded w-full p-2" required>
  </div>
  <div>
    <label class="text-sm text-gray-700">Confirm new password</label>
    <input type="password" name="password_confirmation" class="border rounded w-full p-2" required>
  </div>
  <button class="bg-blue-600 text-white px-4 py-2 rounded">Update password</button>
</form>
@endsection
