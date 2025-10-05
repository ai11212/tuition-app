@extends('layouts.app')
@section('content')
@include('partials.flash')
<div class="flex justify-between mb-4"><h1 class="text-xl font-semibold">Books</h1>
<a class="bg-blue-600 text-white px-3 py-2 rounded" href="{{ route('books.create') }}">Add Book</a></div>
<table class="w-full">
<tr class="border-b bg-gray-50"><th class="p-2 text-left">Reference</th><th>Subject</th><th>Title</th><th>Price</th><th></th></tr>
@foreach($items as $b)
<tr class="border-b">
<td class="p-2">{{ $b->reference }}</td><td>{{ $b->subject }}</td><td>{{ $b->title }}</td><td>{{ number_format($b->price,2) }}</td>
<td class="text-right p-2">
  <a class="text-blue-600" href="{{ route('books.edit',$b) }}">Edit</a>
  <form class="inline" method="POST" action="{{ route('books.destroy',$b) }}">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
</td>
</tr>
@endforeach
</table>
{{ $items->links() }}
@endsection
