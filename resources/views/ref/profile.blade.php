@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold mb-4">Student Profile</h1>

@if($student)
  {{-- Student Information --}}
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4 text-blue-700">Student Information</h2>
    <div class="grid md:grid-cols-3 gap-4">
      <div>
        <span class="text-blue-700 font-medium">Reference:</span><br>
        <span class="text-lg">{{ $student->reference }}</span>
      </div>
      <div>
        <span class="text-blue-700 font-medium">Name:</span><br>
        <span class="text-lg">{{ $student->first_name }} {{ $student->last_name }}</span>
      </div>
      <div>
        <span class="text-blue-700 font-medium">Date of Birth:</span><br>
        <span class="text-lg">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d/m/Y') : '-' }}</span>
      </div>
      <div>
        <span class="text-blue-700 font-medium">Deposit:</span><br>
        <span class="text-lg font-semibold text-green-700">£{{ number_format($student->deposit ?? 0, 2) }}</span>
        <span class="ms-2 badge {{ $student->deposit_paid ? 'bg-success' : 'bg-secondary' }}">
          {{ $student->deposit_paid ? 'Paid' : 'Not Paid' }}
        </span>
      </div>
      <div>
        <span class="text-blue-700 font-medium">Payment:</span><br>
        <span class="text-lg">£{{ number_format($student->payment ?? 0, 2) }}</span>
      </div>
      @if($student->guardian_name)
      <div class="md:col-span-3">
        <span class="text-blue-700 font-medium">Guardian:</span><br>
        <span class="text-lg">{{ $student->guardian_name }} 
          @if($student->guardian_phone)
            ({{ $student->guardian_phone }})
          @endif
        </span>
      </div>
      @endif
    </div>
  </div>

  {{-- Payment Status --}}
  @if(isset($paymentDetails))
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4 text-blue-700">Payment Status</h2>
    <div class="grid md:grid-cols-5 gap-4">
      <div class="bg-blue-50 p-4 rounded">
        <div class="text-sm text-blue-700 font-medium mb-1">Total Payments Made</div>
        <div class="text-2xl font-bold text-blue-900">£{{ number_format($paymentDetails['total_paid'], 2) }}</div>
        <div class="text-xs text-blue-600 mt-1">Expected: £{{ number_format($paymentDetails['expected_total'], 2) }}</div>
      </div>
      <div class="bg-{{ $paymentDetails['payment_pending'] > 0 ? 'orange' : 'green' }}-50 p-4 rounded">
        <div class="text-sm text-{{ $paymentDetails['payment_pending'] > 0 ? 'orange' : 'green' }}-700 font-medium mb-1">Payment Pending</div>
        <div class="text-2xl font-bold text-{{ $paymentDetails['payment_pending'] > 0 ? 'orange' : 'green' }}-900">£{{ number_format($paymentDetails['payment_pending'], 2) }}</div>
        <div class="text-xs text-{{ $paymentDetails['payment_pending'] > 0 ? 'orange' : 'green' }}-600 mt-1">{{ $paymentDetails['payment_pending'] > 0 ? 'Outstanding amount' : 'Fully paid' }}</div>
      </div>
      <div class="bg-green-50 p-4 rounded">
        <div class="text-sm text-green-700 font-medium mb-1">Book Payments Pending</div>
        <div class="text-2xl font-bold text-green-900">£{{ number_format($paymentDetails['book_payments_pending'], 2) }}</div>
        <div class="text-xs text-green-600 mt-1">Total books value: £{{ number_format($paymentDetails['total_book_price'], 2) }}</div>
      </div>
      <div class="bg-indigo-50 p-4 rounded">
        <div class="text-sm text-indigo-700 font-medium mb-1">Credit Balance</div>
        <div class="text-2xl font-bold text-indigo-900">£{{ number_format($paymentDetails['credit_balance'] ?? 0, 2) }}</div>
        <div class="text-xs text-indigo-600 mt-1">Available credit</div>
      </div>
      <div class="bg-purple-50 p-4 rounded flex items-center justify-center">
        {{-- 'ref' is the param the Take Payment page reads — student loads + searches automatically --}}
        <a href="{{ route('payments', ['ref' => $student->reference]) }}"
           class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
          Record Payment →
        </a>
      </div>
    </div>
  </div>
  @endif

  {{-- Timetable --}}
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4 text-blue-700">Time Table</h2>
    @foreach($siblingsWithTimetables as $siblingData)
      <div class="mb-6 @if(!$loop->last) border-b border-gray-300 pb-6 @endif">
        <h3 class="text-md font-semibold mb-3 text-gray-700">{{ $siblingData['student']->first_name }} {{ $siblingData['student']->last_name }}</h3>
        @if($siblingData['timetable']->count() > 0)
        <table class="w-full">
          <tr class="bg-gray-50 border-b">
            <th class="p-2 text-left">Day</th>
            <th>Start</th>
            <th>End</th>
            <th>Subject</th>
            <th>Teacher</th>
            <th>Room</th>
          </tr>
          @foreach($siblingData['timetable'] as $t)
          <tr class="border-b">
            <td class="p-2">{{ ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'][(int) $t->day_of_week] ?? $t->day_of_week }}</td>
            <td>{{ $t->start_time ? \Carbon\Carbon::parse($t->start_time)->format('g:i A') : '-' }}</td>
            <td>{{ $t->end_time ? \Carbon\Carbon::parse($t->end_time)->format('g:i A') : '-' }}</td>
            <td>{{ $t->subject }}</td>
            <td>{{ $t->teacher_name }}</td>
            <td>{{ $t->room }}</td>
          </tr>
          @endforeach
        </table>
        @else
        <p class="text-gray-500">No timetable entries found.</p>
        @endif
      </div>
    @endforeach
  </div>

  {{-- Quick Actions --}}
  <div class="flex gap-3 mb-6">
    <a href="{{ route('students.edit', $student) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Edit Student
    </a>
    <a href="{{ route('attendance.sheet', ['reference' => $student->reference]) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
      Mark Attendance
    </a>
    <a href="{{ route('ref.form') }}" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
      ← New Search
    </a>
  </div>
@else
  <div class="mb-4 text-gray-600">No student found.</div>
@endif
@endsection
