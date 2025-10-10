@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Student</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Reference:</strong> {{ $student->reference }}</p>
            <p><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
            <p><strong>Guardian:</strong> {{ $student->guardian_name }}</p>
            <p><strong>City:</strong> {{ $student->guardian_city }}</p>
            <p><strong>Enroll Date:</strong> {{ $student->enroll_date }}</p>
            <p><strong>Start Date:</strong> {{ $student->start_date }}</p>
            <p><strong>Deposit:</strong> {{ $student->deposit }}</p>
            <p><strong>Period:</strong> {{ $student->period }}</p>
        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('students.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>
@endsection@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Edit Student (only filled fields are updated)</h1>
<form method="POST" action="{{ route('students.update',$student) }}" class="grid md:grid-cols-2 gap-4">@csrf @method('PUT')
  <input name="reference" placeholder="Reference: {{ $student->reference }}" class="border p-2">
  <input name="first_name" placeholder="First name: {{ $student->first_name }}" class="border p-2">
  <input name="last_name" placeholder="Last name: {{ $student->last_name }}" class="border p-2">
  <input name="phone" placeholder="Phone: {{ $student->phone }}" class="border p-2">
  <input name="email" placeholder="Email: {{ $student->email }}" class="border p-2">
  <input name="address" placeholder="Address: {{ $student->address }}" class="border p-2 md:col-span-2">
  <input name="status" placeholder="Status: {{ $student->status }}" class="border p-2">
  <input name="dob" type="date" class="border p-2" value="">
  <input name="enroll_date" type="date" class="border p-2" value="">
  <input name="gender" placeholder="Gender: {{ $student->gender }}" class="border p-2">
  <input name="fee_amount" placeholder="Fee (£): {{ $student->fee_amount }}" class="border p-2">
  <input name="start_date" type="date" class="border p-2" value="">
  <input name="period" placeholder="Period: {{ $student->period }}" class="border p-2">
  <div class="flex items-center gap-2"><input type="checkbox" name="full_time"><span>Full-time (tick to set)</span></div>
  <input name="lesson1" placeholder="Lesson 1: {{ $student->lesson1 }}" class="border p-2 md:col-span-2">
  <input name="lesson2" placeholder="Lesson 2: {{ $student->lesson2 }}" class="border p-2 md:col-span-2">
  <input name="lesson3" placeholder="Lesson 3: {{ $student->lesson3 }}" class="border p-2 md:col-span-2">
  <input name="lesson4" placeholder="Lesson 4: {{ $student->lesson4 }}" class="border p-2 md:col-span-2">
  <input name="deposit" placeholder="Deposit (£): {{ $student->deposit }}" class="border p-2">
  <button class="bg-blue-600 text-white px-4 py-2 rounded md:col-span-2">Save</button>
</form>
@endsection
