@extends('layouts.app')
@section('content')
@include('partials.flash')
<h1 class="text-xl font-semibold mb-4">Edit Book</h1>
<form method="POST" action="{{ route('books.update',$book) }}" class="grid grid-cols-2 gap-4">@csrf @method('PUT')
<input name="reference" class="border p-2" value="{{ $book->reference }}">
<input name="subject" class="border p-2" value="{{ $book->subject }}">
<input name="title" class="border p-2" value="{{ $book->title }}">
<input name="price" class="border p-2" value="{{ $book->price }}">
<button class="bg-blue-600 text-white rounded px-4 py-2 col-span-2">Update</button>
</form>
@endsection
