@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Defaulter List</h1>
<div class="text-sm mb-3 text-gray-600">No payment in last 14 days (cutoff {{ $cut }}) or balance pending.</div>
<table class="w-full">
<tr class="border-b bg-gray-50"><th class="p-2 text-left">Student</th><th>Reference</th></tr>
@forelse($rows as $s)
<tr class="border-b"><td class="p-2">{{ $s->full_name }}</td><td>{{ $s->reference }}</td></tr>
@empty
<tr><td class="p-2" colspan="2">No defaulters 🎉</td></tr>
@endforelse
</table>
@endsection
