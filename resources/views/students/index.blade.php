@extends('layouts.app')
@section('content')
<div class="flex justify-between items-center mb-4">
  <h1 class="text-xl font-bold">Students</h1>
  <a href="{{ route('students.create') }}" class="bg-blue-600 text-white px-3 py-2 rounded">New Admission</a>
</div>
<table class="w-full">
<tr class="border-b"><th class="text-left p-2">Ref</th><th>Name</th><th>Phone</th><th></th></tr>
@foreach($students as $s)
<tr class="border-b">
  <td class="p-2">{{ $s->reference }}</td>
  <td>{{ $s->full_name }}</td>
  <td>{{ $s->phone }}</td>
  <td class="text-right">
    <a class="text-blue-600" href="{{ route('students.edit',$s) }}">Edit</a>
    <form class="inline" method="POST" action="{{ route('students.destroy',$s) }}">@csrf @method('DELETE') <button class="text-red-600">Delete</button></form>
  </td>
</tr>
@endforeach
</table>
{{ $students->links() }}
@endsection
