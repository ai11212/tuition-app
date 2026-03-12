@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  {{-- Success/Error Messages --}}
  @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ session('error') }}
    </div>
  @endif

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Payments</h1>
    <div class="flex gap-2">
      <a href="{{ route('expenses') }}" class="px-3 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700 text-sm flex items-center gap-1">
        💸 Expenses
      </a>
      <a href="{{ route('payments.export', request()->only('ref','from','to')) }}" class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">Export CSV</a>
      <a href="{{ route('payments.print', request()->only('ref','from','to')) }}" target="_blank" class="px-3 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">Print</a>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('payments') }}" class="grid md:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="text-sm text-gray-600">Reference</label>
  <input name="ref" value="{{ old('ref', $ref ?? '') }}" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2" placeholder="Type to search...">
    </div>
    <div>
      <label class="text-sm text-gray-600">From</label>
  <input type="date" name="from" value="{{ old('from', $from ?? '') }}" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-gray-600">To</label>
  <input type="date" name="to" value="{{ old('to', $to ?? '') }}" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
    </div>
    <div class="flex items-end">
      <button class="w-full px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Search</button>
    </div>
  </form>

  {{-- Student Information Card (when exact reference match found) --}}
  @if(isset($studentDetails) && $studentDetails)
    <div class="p-4 rounded-xl border-2 border-blue-200 bg-blue-50 mb-6">
      <h2 class="font-semibold text-blue-900 mb-4">Student Information</h2>
      
      {{-- Top Row: Basic Info - Responsive Grid with Column Spanning --}}
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4 pb-4 border-b border-blue-200 text-sm">
        <div>
          <span class="text-blue-700 font-medium">Reference:</span><br>
          <span class="text-lg font-semibold">{{ $studentDetails['student']->reference }}</span>
        </div>
        <div class="md:col-span-2">
          <span class="text-blue-700 font-medium">Name:</span><br>
          @php
            $siblings = \App\Models\Student::where('reference', $studentDetails['student']->reference)->get();
          @endphp
          @foreach($siblings as $sibling)
            <div class="text-lg overflow-hidden text-ellipsis whitespace-nowrap" title="{{ $sibling->first_name }} {{ $sibling->last_name }}">
              {{ $sibling->first_name }} {{ $sibling->last_name }}
            </div>
          @endforeach
        </div>
        <div>
          <span class="text-blue-700 font-medium">Year:</span><br>
          @foreach($siblings as $sibling)
            <div class="text-lg">{{ $sibling->year ?? '-' }}</div>
          @endforeach
        </div>
        <div>
          <span class="text-blue-700 font-medium">Deposit:</span><br>
          <span class="text-lg font-semibold text-green-700">£{{ number_format($studentDetails['deposit'], 2) }}</span>
          <span class="ms-2 badge {{ $studentDetails['deposit_paid'] == 1 || $studentDetails['deposit_paid'] === '1' || $studentDetails['deposit_paid'] === true ? 'bg-success' : 'bg-secondary' }}">
            {{ $studentDetails['deposit_paid'] == 1 || $studentDetails['deposit_paid'] === '1' || $studentDetails['deposit_paid'] === true ? 'Yes' : 'No' }}
          </span>
        </div>
        <div>
          @if(isset($studentDetails['payment_plan']) && $studentDetails['payment_plan'] > 0)
          <span class="text-blue-700 font-medium">Student Plan:</span><br>
          <span class="text-lg font-semibold text-gray-700">£{{ number_format($studentDetails['payment_plan'], 2) }}</span>
          <span class="text-xs text-gray-500">(Original)</span>
          @endif
        </div>
        <div>
          <span class="text-blue-700 font-medium">Student New Plan:</span><br>
          <form method="POST" action="{{ route('student.updateNewPlan', $studentDetails['student']->id) }}" class="inline-flex items-center gap-2 mt-1">
            @csrf
            @method('PATCH')
            <span class="text-sm font-bold text-gray-600">£</span>
            <input type="number" name="new_plan" 
                   value="{{ old('new_plan', $studentDetails['student']->new_plan ?? '') }}" 
                   step="0.01" min="0" 
                   class="w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-200"
                   placeholder="0.00">
            <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
              Save
            </button>
          </form>
        </div>
      </div>

      {{-- Middle Row: Add Pending Amount (Single Column) --}}
      <div class="mb-4 pb-4 border-b border-blue-200">
        <div class="max-w-lg">
          <span class="text-blue-700 font-medium text-base">Amount:</span><br>
          <div class="inline-flex items-center gap-2 mt-2">
            <span class="text-xl font-bold text-orange-600">£</span>
            <input type="number" id="pending-amount-input" value="0" 
                   step="0.01" min="0" 
                   class="w-32 px-3 py-2 border-2 border-orange-300 rounded-lg text-xl font-bold text-orange-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200"
                   required>
            
            {{-- Add Button (existing) --}}
            <form method="POST" action="{{ route('student.addPendingAmount', $studentDetails['student']->id) }}" class="inline">
              @csrf
              @method('PATCH')
              <input type="hidden" name="amount_to_add" id="add-amount-hidden">
              <button type="submit" 
                      onclick="document.getElementById('add-amount-hidden').value = document.getElementById('pending-amount-input').value"
                      class="px-4 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 transition-colors">
                ➕ Add
              </button>
            </form>
            
            {{-- Set Button (new) --}}
            <form method="POST" action="{{ route('student.setPendingAmount', $studentDetails['student']->id) }}" class="inline">
              @csrf
              @method('PATCH')
              <input type="hidden" name="amount_to_set" id="set-amount-hidden">
              <button type="submit" 
                      onclick="document.getElementById('set-amount-hidden').value = document.getElementById('pending-amount-input').value"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                📌 Set
              </button>
            </form>
          </div>
          <div class="text-xs text-gray-600 mt-2 italic">
            💡 <strong>Add:</strong> Adds to pending | <strong>Set:</strong> Resets to this amount
          </div>
        </div>
      </div>

      {{-- Bottom Row: Payment Summary in 3 columns --}}
      <div class="grid md:grid-cols-3 gap-4 text-sm">
        <div>
          <span class="text-blue-700 font-medium">Total Payments Made:</span><br>
          <span class="text-lg font-semibold text-green-700">£{{ number_format($studentDetails['total_paid'], 2) }}</span>
          <div class="text-xs text-blue-600 mt-1">
            Expected: £{{ number_format($studentDetails['expected_total'], 2) }}
          </div>
        </div>
        <div>
          <span class="text-blue-700 font-medium">Payment Pending:</span><br>
          <span class="text-lg font-semibold {{ $studentDetails['payment_pending'] > 0 ? 'text-orange-600' : 'text-green-700' }}">
            £{{ number_format($studentDetails['payment_pending'], 2) }}
          </span>
          <div class="text-xs text-blue-600 mt-1">
            @if($studentDetails['payment_pending'] == 0)
              Fully paid ✓
            @else
              Outstanding amount
            @endif
          </div>
        </div>
        <div>
          <span class="text-blue-700 font-medium">Book Payments Pending:</span><br>
          <span class="text-lg font-semibold {{ $studentDetails['book_payments_pending'] > 0 ? 'text-orange-600' : 'text-green-700' }}">
            £{{ number_format($studentDetails['book_payments_pending'], 2) }}
          </span>
          <div class="text-xs text-blue-600 mt-1">
            Total books value: £{{ number_format($studentDetails['total_book_price'], 2) }}
          </div>
        </div>
      </div>
      
      {{-- Book Details --}}
      @if($studentDetails['show_books'])
      <div class="mt-4 pt-3 border-t border-blue-200">
        
        {{-- Assigned Books Section --}}
        @if(isset($studentDetails['assigned_books']) && $studentDetails['assigned_books']->count() > 0)
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <span class="text-blue-700 font-medium">📚 Books Assigned to {{ $studentDetails['student']->reference }}:</span>
              <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                Total: £{{ number_format($studentDetails['assigned_books']->sum('price'), 2) }}
              </span>
            </div>
            <div class="grid md:grid-cols-2 gap-2 text-sm">
              @foreach($studentDetails['assigned_books'] as $book)
                <div class="flex justify-between items-center bg-green-100 px-3 py-2 rounded border-l-4 border-green-500">
                  <div>
                    <span class="text-xs font-semibold text-green-600 block mb-1">{{ $book->first_name }} {{ $book->last_name }}</span>
                    <span class="font-medium text-green-800">{{ $book->subject }}</span><br>
                    <span class="text-green-900 font-medium">{{ $book->title }}</span>
                    <span class="text-xs text-green-700 block">Ref: {{ $book->reference }}</span>
                    <span class="text-xs text-green-700 block">Issued: {{ $book->created_at->format('d/m/Y') }}</span>
                  </div>
                  <span class="font-semibold text-green-900">£{{ number_format($book->price, 2) }}</span>
                </div>
              @endforeach
            </div>
          </div>
        @endif
        
        {{-- General Subject Books Section --}}
        @if(isset($studentDetails['subject_books']) && $studentDetails['subject_books']->count() > 0)
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <span class="text-blue-700 font-medium">📖 General Books for Student's Subjects:</span>
              <div class="text-xs">
                <span class="text-blue-600">Subjects: {{ $studentDetails['student_subjects']->join(', ') }}</span>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">
                  Total: £{{ number_format($studentDetails['subject_books']->sum('price'), 2) }}
                </span>
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-2 text-sm">
              @foreach($studentDetails['subject_books'] as $book)
                <div class="flex justify-between items-center bg-blue-100 px-3 py-2 rounded">
                  <div>
                    <span class="font-medium">{{ $book->subject }}</span><br>
                    <span class="text-blue-800">{{ $book->title }}</span>
                    <span class="text-xs text-blue-600 block">Ref: {{ $book->reference }}</span>
                  </div>
                  <span class="font-semibold text-blue-900">£{{ number_format($book->price, 2) }}</span>
                </div>
              @endforeach
            </div>
          </div>
        @endif
        
        {{-- No Books Message --}}
        @if($studentDetails['books']->count() == 0)
          <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2 text-sm text-yellow-800">
            @if($studentDetails['student_subjects']->count() > 0)
              No books available for student {{ $studentDetails['student']->reference }} or subjects: {{ $studentDetails['student_subjects']->join(', ') }}
            @else
              Student has no timetable entries. No books to display.
            @endif
          </div>
        @endif
      </div>
      @endif
      @if($studentDetails['student']->guardian_name)
        <div class="mt-4 pt-3 border-t border-blue-200 text-sm">
          <span class="text-blue-700 font-medium">Guardian:</span>
          {{ $studentDetails['student']->guardian_name }}
          @if($studentDetails['student']->guardian_phone)
            | Phone: {{ $studentDetails['student']->guardian_phone }}
          @endif
          @if($studentDetails['student']->guardian_email)
            | Email: {{ $studentDetails['student']->guardian_email }}
          @endif
        </div>
      @endif
    </div>
  @endif

  {{-- Recorder --}}
  <div class="p-4 rounded-xl border bg-white mb-6">
    <h2 class="font-semibold mb-3">Record a Payment</h2>
    @if(session('ok')) <div class="mb-3 text-sm text-emerald-700">{{ session('ok') }}</div> @endif
    @php $students = $students ?? collect(); @endphp
    <form method="POST" action="{{ route('payments.store') }}" class="grid md:grid-cols-6 gap-3">
      @csrf
      <div class="md:col-span-2">
        <label class="text-sm text-gray-600">Reference*</label>
        <input name="reference" value="{{ old('reference', $ref ?? '') }}" required class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2" placeholder="Student reference">
        @if($students->count())
          <div class="mt-2 text-xs text-gray-500">
            <span>Matching students:</span>
            <ul>
              @foreach($students as $s)
                <li>{{ $s->full_name ?? ($s->first_name . ' ' . $s->last_name) }} ({{ $s->reference }})</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Amount (£)*</label>
        <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}" required class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
      </div>
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Method</label>
        <select name="method" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
          <option>Cash</option>
          <option>Card</option>
          <option>Bank</option>
          <option>Transfer</option>
        </select>
      </div>
      @if(Schema::hasColumn('payment_transactions', 'purpose'))
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Purpose</label>
        <select name="purpose" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
          <option value="tuition" {{ old('purpose') == 'tuition' ? 'selected' : '' }}>Tuition Fee</option>
          <option value="books" {{ old('purpose') == 'books' ? 'selected' : '' }}>Book Payment</option>
          <option value="deposit" {{ old('purpose') == 'deposit' ? 'selected' : '' }}>Deposit</option>
          <option value="other" {{ old('purpose') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
      </div>
      @endif
      <div class="md:col-span-1">
        <label class="text-sm text-gray-600">Paid at</label>
        <input type="date" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm text-gray-600">Payment Period From</label>
        <input type="date" name="period_from" value="{{ old('period_from', date('Y-m-d')) }}" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
      </div>
      <div class="md:col-span-3">
        <label class="text-sm text-gray-600">Payment Period To</label>
        <input type="date" name="period_to" value="{{ old('period_to', date('Y-m-d', strtotime('+1 week'))) }}" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2">
      </div>
      <div class="md:col-span-6">
        <label class="text-sm text-gray-600">Notes</label>
        <textarea name="notes" rows="2" class="w-full mt-1 rounded-lg border border-gray-300 bg-white px-3 py-2" placeholder="Add payment notes...">{{ old('notes') }}</textarea>
      </div>
      <div class="md:col-span-6">
        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Add Payment</button>
      </div>
    </form>
  </div>

  {{-- Results --}}
  <div class="rounded-xl border bg-white overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Date / Time</th>
          <th class="px-3 py-2 text-left">Reference</th>
          <th class="px-3 py-2 text-left">Student</th>
          <th class="px-3 py-2 text-left">Method</th>
          <th class="px-3 py-2 text-right">Amount (£)</th>
          <th class="px-3 py-2 text-left">Payment Period</th>
          <th class="px-3 py-2 text-left">Invoice</th>
          <th class="px-3 py-2 text-left">Notes</th>
          <th class="px-3 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse(($payments ?? []) as $p)
          @php
            // Show date only (dd/mm/YYYY). prefer paid_at if present else paid_on
            if ($p->paid_at) {
              $when = \Carbon\Carbon::parse($p->paid_at)->format('d/m/Y');
            } elseif ($p->paid_on) {
              $when = \Carbon\Carbon::parse($p->paid_on)->format('d/m/Y');
            } else {
              $when = '';
            }
          @endphp
          <tr class="border-t">
            <td class="px-3 py-2 whitespace-nowrap">{{ $when }}</td>
            <td class="px-3 py-2">{{ $p->student_ref ?? '' }}</td>
            <td class="px-3 py-2">{{ $p->student_name ?? '' }}</td>
            <td class="px-3 py-2">{{ $p->method }}</td>
            <td class="px-3 py-2 text-right">£{{ number_format($p->amount,2) }}</td>
            <td class="px-3 py-2 whitespace-nowrap">
              @if($p->period_from && $p->period_to)
                {{ \Carbon\Carbon::parse($p->period_from)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($p->period_to)->format('d/m/y') }}
              @else
                -
              @endif
            </td>
            <td class="px-3 py-2">{{ $p->invoice_ref ?? '' }}</td>
            <td class="px-3 py-2">{{ $p->notes }}</td>
            <td class="px-3 py-2">
              <div class="flex gap-2">
                @if($p->invoice_id)
                  <a href="{{ route('invoice.print',$p->invoice_id) }}" 
                     class="px-2 py-1 rounded bg-gray-100 text-sm hover:bg-gray-200">Print</a>
                @endif
                <button onclick="openEditModal({{ json_encode([
                  'id' => $p->id,
                  'student_ref' => $p->student_ref,
                  'student_name' => $p->student_name,
                  'amount' => $p->amount,
                  'method' => $p->method,
                  'purpose' => $p->purpose ?? 'tuition',
                  'paid_at' => $p->paid_at ? date('Y-m-d', strtotime($p->paid_at)) : ($p->paid_on ? date('Y-m-d', strtotime($p->paid_on)) : date('Y-m-d')),
                  'period_from' => $p->invoice_id ? \App\Models\Invoice::find($p->invoice_id)->period_from : '',
                  'period_to' => $p->invoice_id ? \App\Models\Invoice::find($p->invoice_id)->period_to : '',
                  'notes' => $p->notes,
                  'invoice_ref' => $p->invoice_ref ?? ''
                ]) }})" 
                        class="px-2 py-1 rounded bg-blue-100 text-blue-700 text-sm hover:bg-blue-200">✏️ Edit</button>
                <form method="POST" action="{{ route('payments.destroy', $p->id) }}" 
                      id="delete-form-{{ $p->id }}" class="inline">
                  @csrf
                  @method('DELETE')
                  <button type="button" 
                          onclick="confirmDelete({{ $p->id }}, '{{ $p->invoice_ref ?? 'N/A' }}')"
                          class="px-2 py-1 rounded bg-red-100 text-red-700 text-sm hover:bg-red-200">🗑️ Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td class="px-3 py-6 text-center text-gray-500" colspan="9">No payments found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(isset($payments))
    <div class="mt-4">
      {{ $payments->links() }}
    </div>
  @endif
</div>

{{-- Edit Payment Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Edit Payment</h2>
        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <form method="POST" action="" id="editForm">
        @csrf
        @method('PUT')

        {{-- Student Info (Read-only) --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
          <div class="grid md:grid-cols-2 gap-3 text-sm">
            <div>
              <span class="text-gray-600">Reference:</span>
              <span class="font-semibold ml-2" id="modal_student_ref"></span>
            </div>
            <div>
              <span class="text-gray-600">Student:</span>
              <span class="font-semibold ml-2" id="modal_student_name"></span>
            </div>
            <div>
              <span class="text-gray-600">Invoice:</span>
              <span class="font-semibold ml-2" id="modal_invoice_ref"></span>
            </div>
          </div>
        </div>

        {{-- Editable Fields --}}
        <div class="grid md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (£) <span class="text-red-500">*</span></label>
            <input type="number" name="amount" id="edit_amount" step="0.01" min="0.01" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Method <span class="text-red-500">*</span></label>
            <select name="method" id="edit_method" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="Cash">Cash</option>
              <option value="Card">Card</option>
              <option value="Bank">Bank Transfer</option>
              <option value="Transfer">Transfer</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
            <select name="purpose" id="edit_purpose"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="tuition">Tuition Fee</option>
              <option value="books">Book Payment</option>
              <option value="deposit">Deposit</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
            <input type="date" name="paid_at" id="edit_paid_at" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Period From</label>
            <input type="date" name="period_from" id="edit_period_from"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Period To</label>
            <input type="date" name="period_to" id="edit_period_to"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
          <textarea name="notes" id="edit_notes" rows="3" maxlength="500"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeModal()" 
                  class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" onclick="return confirm('Are you sure you want to update this payment?')"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Update Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditModal(payment) {
  // Set read-only info
  document.getElementById('modal_student_ref').textContent = payment.student_ref || '';
  document.getElementById('modal_student_name').textContent = payment.student_name || '';
  document.getElementById('modal_invoice_ref').textContent = payment.invoice_ref || '';

  // Set editable fields
  document.getElementById('edit_amount').value = payment.amount || '';
  document.getElementById('edit_method').value = payment.method || 'Cash';
  document.getElementById('edit_purpose').value = payment.purpose || 'tuition';
  document.getElementById('edit_paid_at').value = payment.paid_at || '';
  document.getElementById('edit_period_from').value = payment.period_from || '';
  document.getElementById('edit_period_to').value = payment.period_to || '';
  document.getElementById('edit_notes').value = payment.notes || '';

  // Set form action URL
  document.getElementById('editForm').action = '/payments/' + payment.id;

  // Show modal
  document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(paymentId, invoiceRef) {
  if (confirm(`⚠️ Are you sure you want to delete this payment?\n\nInvoice: ${invoiceRef}\n\nThis will also delete the invoice and cannot be undone!`)) {
    document.getElementById('delete-form-' + paymentId).submit();
  }
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal();
  }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeModal();
  }
});
</script>

@endsection
