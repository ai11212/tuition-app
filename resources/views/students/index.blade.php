
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">All Students</h2>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    
    <!-- Search Form -->
    <div class="mb-4">
        <form method="GET" action="{{ route('students.index') }}" class="d-flex gap-2">
            <input type="text" 
                   name="reference" 
                   value="{{ $reference }}" 
                   placeholder="Search by reference..." 
                   class="form-control" 
                   style="max-width: 300px;">
            <button type="submit" class="btn btn-primary">Search</button>
            @if($reference)
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </form>
        @if($reference)
            <small class="text-muted">Showing results for: "{{ $reference }}"</small>
        @endif
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Reference</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Guardian</th>
                <th>City</th>
                <th>Enroll Date</th>
                <th>Start Date</th>
                <th>Deposit</th>
                <th>Payment</th>
                <th>Period</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
            <tr>
                <td>{{ $student->reference }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->gender }}</td>
                <td>{{ $student->dob }}</td>
                <td>{{ $student->guardian_name }}</td>
                <td>{{ $student->guardian_city }}</td>
                <td>{{ $student->enroll_date }}</td>
                <td>{{ $student->start_date }}</td>
                <td>{{ $student->deposit }}</td>
                <td>{{ $student->payment }}</td>
                <td>{{ $student->period }}</td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center">No students found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection@extends('layouts.app')
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
