@extends('layouts.app')

@section('title', 'Teacher Salaries')

@section('content')
@include('partials.flash')

<div class="max-w-6xl mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">💰 Teacher Salaries</h1>
    <p class="text-sm text-gray-500">Salaries are calculated from unpaid Present attendance — one class session (date + time slot) = 2 hours × the teacher's hourly rate. Multiple subjects taught in the same slot count as one session.</p>
  </div>

  {{-- Filters / calculator --}}
  <div class="bg-white rounded-xl border shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('salaries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-start">
      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Teacher</label>
        <select name="staff_id" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Select Teacher</option>
          @foreach($teachers as $t)
            <option value="{{ $t->id }}" {{ (string) $staffId === (string) $t->id ? 'selected' : '' }}>{{ $t->name }}{{ $t->reference ? ' (' . $t->reference . ')' : '' }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Date From</label>
        <input type="date" name="from" value="{{ $from }}" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Date To</label>
        <input type="date" name="to" value="{{ $to }}" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Status</label>
        <select name="status" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
          <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
          <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
        </select>
      </div>
      <div>
        <label class="block h-4 leading-4 text-xs font-semibold mb-1.5 invisible" aria-hidden="true">Go</label>
        <button class="w-full h-10 inline-flex items-center justify-center bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">Calculate Salary</button>
      </div>
    </form>
  </div>

  @if($calc)
    {{-- Salary Summary --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
        <div class="min-w-0">
          <div class="h-4 text-xs font-semibold text-blue-700 text-center">👤 Teacher</div>
          <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ $calc['teacher']->name }}</div>
        </div>
        <div class="min-w-0">
          <div class="h-4 text-xs font-semibold text-blue-700 text-center">📅 Pay Period</div>
          <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">
            {{ $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : 'Start' }} – {{ $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : 'Today' }}
          </div>
        </div>
        <div class="min-w-0">
          <div class="h-4 text-xs font-semibold text-blue-700 text-center">💷 Hourly Rate</div>
          <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">
            {{ $calc['rate'] !== null ? '£' . number_format($calc['rate'], 2) : 'Not set' }}
          </div>
        </div>
        <div class="min-w-0">
          <div class="h-4 text-xs font-semibold text-blue-700 text-center">🗓 Sessions</div>
          <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ $calc['session_count'] }}</div>
        </div>
        <div class="min-w-0">
          <div class="h-4 text-xs font-semibold text-blue-700 text-center">⏰ Hours Worked</div>
          <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ $calc['hours'] }} hrs</div>
        </div>
        <div class="min-w-0">
          <div class="h-4 text-xs font-semibold text-blue-700 text-center">💰 Gross Salary</div>
          <div class="mt-1.5 h-5 leading-5 text-sm font-bold text-purple-700 truncate text-center">
            {{ $calc['gross'] !== null ? '£' . number_format($calc['gross'], 2) : '—' }}
          </div>
        </div>
      </div>
      @if($calc['rate'] === null)
        <div class="mt-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">
          ⚠️ {{ $calc['teacher']->name }} has no hourly rate — set it in the Add Teacher module before paying.
        </div>
      @endif
    </div>

    {{-- Attendance Breakdown --}}
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden mb-6">
      <div class="px-5 py-4 border-b flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-800">Attendance Breakdown (unpaid)</h2>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">{{ $calc['session_count'] }} session(s)</span>
      </div>
      @if($calc['session_count'])
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Time Slot</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Subject</th>
              <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Students</th>
              <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Hours</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            @foreach($calc['sessions'] as $s)
            <tr class="even:bg-gray-50">
              <td class="px-4 py-3">{{ \Carbon\Carbon::parse($s->date)->format('d/m/Y') }}</td>
              <td class="px-4 py-3">{{ $s->time ?: '—' }}</td>
              <td class="px-4 py-3">
                @if(($s->subject_count ?? 1) > 1)
                  <details>
                    <summary class="cursor-pointer">
                      {{ $s->subject }}
                      <span class="block text-xs text-gray-500">1 session · {{ $s->subject_count }} subjects</span>
                    </summary>
                    <ul class="mt-1 ml-4 list-disc text-xs text-gray-600">
                      @foreach($s->subjects as $subj)
                        <li>{{ $subj }}</li>
                      @endforeach
                    </ul>
                  </details>
                @else
                  {{ $s->subject ?: '—' }}
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                {{-- Clickable count: opens the Students popup for this session (view-only) --}}
                <button type="button" onclick="openSessionStudents({{ $loop->index }})"
                        class="text-blue-600 hover:text-blue-800 hover:underline font-medium cursor-pointer"
                        title="Click to see the students in this session">
                  @if(($s->subject_count ?? 1) > 1)
                    {{ $s->students }} students across {{ $s->subject_count }} subjects
                  @else
                    {{ $s->students }}
                  @endif
                </button>
              </td>
              <td class="px-4 py-3 text-center">2</td>
              <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Present</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="p-8 text-center text-gray-500">No unpaid attendance sessions in this period. 🎉</div>
      @endif
    </div>

    {{-- Students-in-session popup (view-only — no calculation involved) --}}
    @php
        $sessionStudentsPayload = $calc['sessions']->map(fn($s) => [
            'date'     => \Carbon\Carbon::parse($s->date)->format('d/m/Y'),
            'time'     => $s->time ?: '—',
            'subject'  => $s->subject ?: '—',
            'students' => $s->students_list ?? [],
        ])->values();
    @endphp
    <div id="session-students-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">
        <div class="px-5 py-4 border-b flex items-start justify-between">
          <div>
            <h3 class="text-lg font-bold text-gray-800">👥 Students in Session</h3>
            <div class="text-sm text-gray-600 mt-1">
              Teacher: <span class="font-medium">{{ $calc['teacher']->name }}</span><br>
              <span id="ssm-date"></span> · <span id="ssm-time"></span><br>
              Subject: <span id="ssm-subject"></span>
            </div>
          </div>
          <button type="button" onclick="closeSessionStudents()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="overflow-y-auto p-5">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Reference</th>
                <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Student Name</th>
              </tr>
            </thead>
            <tbody id="ssm-rows" class="divide-y divide-gray-200"></tbody>
          </table>
        </div>
        <div class="px-5 py-3 border-t text-right">
          <button type="button" onclick="closeSessionStudents()"
                  class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300">Close</button>
        </div>
      </div>
    </div>
    <script>
      const sessionStudents = @json($sessionStudentsPayload);
      function openSessionStudents(i) {
        const s = sessionStudents[i];
        if (!s) return;
        document.getElementById('ssm-date').textContent = s.date;
        document.getElementById('ssm-time').textContent = s.time;
        document.getElementById('ssm-subject').textContent = s.subject;
        document.getElementById('ssm-rows').innerHTML = s.students.map(st =>
          '<tr><td class="px-3 py-2 font-medium">' + escapeHtmlSS(st.reference) + '</td>' +
          '<td class="px-3 py-2">' + escapeHtmlSS(st.name) + '</td></tr>'
        ).join('');
        document.getElementById('session-students-modal').classList.remove('hidden');
      }
      function closeSessionStudents() {
        document.getElementById('session-students-modal').classList.add('hidden');
      }
      function escapeHtmlSS(v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
      }
      // Close on overlay click or Escape
      document.getElementById('session-students-modal').addEventListener('click', function(e){ if (e.target === this) closeSessionStudents(); });
      document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeSessionStudents(); });
    </script>

    {{-- Salary Calculation Summary --}}
    @if($calc['session_count'] && $calc['rate'] !== null)
    <div class="bg-white rounded-xl border shadow-sm p-5 mb-6">
      <h2 class="text-lg font-bold text-gray-800 mb-4">🧮 Salary Calculation Summary</h2>
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-sm mb-4">
        <div>
          <div class="text-xs font-semibold text-gray-500">Teacher Hourly Rate</div>
          <div class="text-lg font-semibold">£{{ number_format((float)$calc['rate'], 2) }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold text-gray-500">Unique Teaching Sessions</div>
          <div class="text-lg font-semibold">{{ $calc['session_count'] }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold text-gray-500">Hours per Session</div>
          <div class="text-lg font-semibold">2</div>
        </div>
        <div>
          <div class="text-xs font-semibold text-gray-500">Total Hours Worked</div>
          <div class="text-lg font-semibold">{{ $calc['hours'] }}</div>
        </div>
        <div>
          <div class="text-xs font-semibold text-gray-500">Gross Salary</div>
          <div class="text-lg font-semibold text-purple-700">£{{ number_format((float)$calc['gross'], 2) }}</div>
        </div>
      </div>
      <div class="px-4 py-3 rounded-lg bg-gray-50 border text-sm text-gray-700">
        {{ $calc['session_count'] }} Sessions × 2 Hours × £{{ number_format((float)$calc['rate'], 2) }}
        = <span class="font-semibold">£{{ number_format((float)$calc['gross'], 2) }}</span>
      </div>
    </div>
    @endif

    {{-- Pay Salary --}}
    @if($calc['session_count'] && $calc['rate'] !== null)
    <div class="bg-white rounded-xl border shadow-sm p-5 mb-6">
      <h2 class="text-lg font-bold text-gray-800 mb-4">Pay Salary</h2>
      <form method="POST" action="{{ route('salaries.store') }}"
            onsubmit="return confirm('Pay {{ addslashes($calc['teacher']->name) }} £{{ number_format($calc['gross'], 2) }} for {{ $calc['session_count'] }} session(s) ({{ $calc['hours'] }} hours)?\n\nThis will create salary {{ $calc['next_reference'] }} and a Teacher Salary expense.');"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @csrf
        <input type="hidden" name="staff_id" value="{{ $calc['teacher']->id }}">
        <input type="hidden" name="date_from" value="{{ $from }}">
        <input type="hidden" name="date_to" value="{{ $to }}">

        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Payment Date</label>
          <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Payment Method</label>
          <select name="payment_method" required class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="Cash">Cash</option>
            <option value="Bank">Bank Transfer</option>
            <option value="Card">Card</option>
          </select>
        </div>
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Reference</label>
          <input type="text" value="{{ $calc['next_reference'] }} (auto)" disabled
                 class="w-full h-10 px-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
        </div>
        <div>
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Teacher</label>
          <input type="text" value="{{ $calc['teacher']->name }}" disabled
                 class="w-full h-10 px-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
          <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Notes (optional)</label>
          <input type="text" name="notes" value="{{ old('notes') }}" maxlength="255"
                 class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
          <button type="submit" class="w-full h-10 inline-flex items-center justify-center bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition">
            💾 Pay Salary — £{{ number_format($calc['gross'], 2) }}
          </button>
        </div>
      </form>
    </div>
    @endif
  @endif

  {{-- Salary History --}}
  <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b flex items-center justify-between">
      <h2 class="text-lg font-bold text-gray-800">Salary History</h2>
      <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">{{ $history->total() }} total</span>
    </div>
    @if($history->count())
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Salary Ref</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Teacher</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Pay Period</th>
            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Hours</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Rate</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Amount</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Payment Date</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Method</th>
            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @foreach($history as $sal)
          <tr class="even:bg-gray-50 hover:bg-blue-50">
            <td class="px-3 py-3 font-mono font-semibold text-blue-700">{{ $sal->reference }}</td>
            <td class="px-3 py-3 font-medium text-gray-900">{{ $sal->teacher_name }}</td>
            <td class="px-3 py-3 text-gray-600 whitespace-nowrap">{{ $sal->date_from->format('d/m/y') }} – {{ $sal->date_to->format('d/m/y') }}</td>
            <td class="px-3 py-3 text-center">{{ $sal->hours }}</td>
            <td class="px-3 py-3">£{{ number_format($sal->hourly_rate, 2) }}</td>
            <td class="px-3 py-3 font-semibold">£{{ number_format($sal->gross, 2) }}</td>
            <td class="px-3 py-3 whitespace-nowrap">{{ $sal->payment_date->format('d/m/Y') }}</td>
            <td class="px-3 py-3">{{ $sal->payment_method }}</td>
            <td class="px-3 py-3"><span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ ucfirst($sal->status) }}</span></td>
            <td class="px-3 py-3 text-right whitespace-nowrap">
              <a href="{{ route('salaries.slip', $sal) }}" class="text-blue-600 hover:underline font-medium">View</a>
              <a href="{{ route('salaries.slip', $sal) }}" class="text-gray-600 hover:underline font-medium ml-2">Print</a>
              <a href="{{ route('salaries.edit', $sal) }}" class="text-indigo-600 hover:underline font-medium ml-2">Edit</a>
              <form method="POST" action="{{ route('salaries.destroy', $sal) }}" class="inline ml-2"
                    onsubmit="return confirm('Delete salary {{ $sal->reference }} ({{ addslashes($sal->teacher_name) }}, £{{ number_format($sal->gross, 2) }})?\n\nThe linked expense will be removed and the attendance sessions become unpaid again.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline font-medium">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-5 py-4 border-t">
      {{ $history->links() }}
    </div>
    @else
    <div class="p-10 text-center text-gray-500">No salary payments recorded yet.</div>
    @endif
  </div>
</div>
@endsection
