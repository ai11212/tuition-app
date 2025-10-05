@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Add Book</h1>
<form method="POST" action="{{ route('books.store') }}" class="grid grid-cols-2 gap-4">@csrf
<input name="reference" class="border p-2" placeholder="Reference">
<input name="subject" class="border p-2" placeholder="Subject">
<input name="title" class="border p-2" placeholder="Book">
<input name="price" class="border p-2" placeholder="Price">
<button class="bg-blue-600 text-white rounded px-4 py-2 col-span-2">Save</button>
</form>
@endsection
