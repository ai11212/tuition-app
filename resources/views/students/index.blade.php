
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-semibold">Students</h1>
    <a href="{{ route('students.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">New Admission</a>
  </div>

  @if (session('status'))
    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('status') }}</div>
  @endif
  
  <!-- Search Form -->
  <div class="mb-6">
    <form method="GET" action="{{ route('students.index') }}" class="flex gap-3 items-end">
      <div class="flex-1 max-w-md">
        <label class="block text-sm text-gray-600 mb-1">Search by Reference</label>
        <input type="text" 
               name="reference" 
               value="{{ $reference ?? '' }}" 
               placeholder="Type reference number..." 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
      </div>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
      @if($reference ?? false)
        <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Clear</a>
      @endif
    </form>
    @if($reference ?? false)
      <small class="text-gray-600 mt-2 block">Showing results for: "{{ $reference }}"</small>
    @endif
  </div>

  <div class="bg-white rounded-lg border overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-3 text-left font-medium text-gray-900">Reference</th>
          <th class="px-3 py-3 text-left font-medium text-gray-900">Name</th>
          <th class="px-3 py-3 text-left font-medium text-gray-900">Guardian</th>
          <th class="px-3 py-3 text-left font-medium text-gray-900">Phone</th>
          <th class="px-3 py-3 text-left font-medium text-gray-900">Deposit</th>
          <th class="px-3 py-3 text-left font-medium text-gray-900">Payment</th>
          <th class="px-3 py-3 text-right font-medium text-gray-900">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($students as $student)
        <tr class="border-t hover:bg-gray-50">
          <td class="px-3 py-3 font-medium text-blue-600">{{ $student->reference }}</td>
          <td class="px-3 py-3">{{ $student->first_name }} {{ $student->last_name }}</td>
          <td class="px-3 py-3">{{ $student->guardian_name }}</td>
          <td class="px-3 py-3">{{ $student->guardian_phone }}</td>
          <td class="px-3 py-3">£{{ number_format($student->deposit ?? 0, 2) }}</td>
          <td class="px-3 py-3">£{{ number_format($student->payment ?? 0, 2) }}</td>
          <td class="px-3 py-3 text-right">
            <a class="text-blue-600 hover:text-blue-800 mr-3" href="{{ route('students.edit', $student) }}">Edit</a>
            <form class="inline" method="POST" action="{{ route('students.destroy', $student) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this student?')">
              @csrf 
              @method('DELETE') 
              <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="px-3 py-8 text-center text-gray-500">
            @if($reference ?? false)
              No students found matching "{{ $reference }}".
            @else
              No students found.
            @endif
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(isset($students))
    <div class="mt-4">
      {{ $students->links() }}
    </div>
  @endif
</div>
@endsection
