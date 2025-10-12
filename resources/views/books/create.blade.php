@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Add Book</h1>
<form method="POST" action="{{ route('books.store') }}" class="grid grid-cols-2 gap-4">@csrf
<input name="reference" class="border p-2" placeholder="Book Reference" required>
<input name="subject" class="border p-2" placeholder="Subject" required>
<input name="title" class="border p-2" placeholder="Book Title" required>
<input name="price" class="border p-2" placeholder="Price" step="0.01" type="number" required>
@if(Schema::hasColumn('books', 'student_reference'))
<input name="student_reference" class="border p-2" placeholder="Student Reference (Optional)">
<div class="text-sm text-gray-600">Leave student reference empty for general books available to all students with matching subjects</div>
@else
<div class="text-sm text-gray-600 col-span-2">This book will be available to all students with matching subjects</div>
@endif
<button class="bg-blue-600 text-white rounded px-4 py-2 col-span-2">Save</button>
</form>
@endsection
