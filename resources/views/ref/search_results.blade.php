@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Search Results</h1>
<p class="mb-4 text-gray-600">Found {{ $students->count() }} students matching your search. Click on a student to view details.</p>

<table class="w-full border-collapse">
  <thead>
    <tr class="bg-gray-50 border-b">
      <th class="p-3 text-left">Reference</th>
      <th class="p-3 text-left">Name</th>
      <th class="p-3 text-left">Phone Number</th>
      <th class="p-3 text-left">Year</th>
      <th class="p-3 text-left">Date of Birth</th>
      <th class="p-3 text-left">Guardian</th>
      <th class="p-3 text-center">Action</th>
    </tr>
  </thead>
  <tbody>
    @foreach($students as $s)
    <tr class="border-b hover:bg-gray-50">
      <td class="p-3">{{ $s->reference }}</td>
      <td class="p-3">{{ $s->first_name }} {{ $s->last_name }}</td>
      <td class="p-3 whitespace-nowrap">
        @if($s->guardian_phone)
          <a href="tel:{{ $s->guardian_phone }}" class="text-blue-600 hover:underline">{{ $s->guardian_phone }}</a>
        @else
          -
        @endif
      </td>
      <td class="p-3">{{ $s->year ?? '-' }}</td>
      <td class="p-3">{{ $s->dob ? \Carbon\Carbon::parse($s->dob)->format('d/m/Y') : '-' }}</td>
      <td class="p-3">{{ $s->guardian_name ?? '-' }}</td>
      <td class="p-3 text-center">
        <a href="{{ route('ref.show', ['reference' => $s->reference, 'exact' => 1]) }}" 
           class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
          View Details
        </a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="mt-4">
  <a href="{{ route('ref.form') }}" class="text-blue-600 hover:underline">← Back to Search</a>
</div>
@endsection
