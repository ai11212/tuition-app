@extends('layouts.app')
@section('content')
@include('attendance._partials.tabs')

@include('partials.flash')
<h1 class="text-2xl font-bold text-gray-800 mb-4">New Attendance</h1>

{{-- Search / load card --}}
<div class="bg-white rounded-xl border shadow-sm p-5 mb-6">
  <form id="load-form" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-4 items-start" novalidate>
    <div class="lg:col-span-2">
      <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Attendance Date <span class="text-red-500">*</span></label>
      <input type="date" name="date" id="attendance-date" value="{{ $date }}"
             class="att-field w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      <p class="att-error hidden text-red-500 text-xs mt-1">Attendance date is required.</p>
    </div>
    <div class="lg:col-span-2">
      <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Reference (Student) <span class="text-red-500">*</span></label>
      <input name="reference" id="reference-input" placeholder="e.g. A1021" value="{{ $reference }}"
             class="att-field w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      <p class="att-error hidden text-red-500 text-xs mt-1">Student reference is required.</p>
    </div>
    <div class="lg:col-span-3">
      <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Time Slot <span class="text-red-500">*</span></label>
      <select name="time" id="time-select" class="att-field w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Select Time</option>
        @if($isWeekend)
          {{-- Weekend Times (Saturday & Sunday) --}}
          <option value="9:00 AM — 11:00 AM" {{ (request('time')=='9:00 AM — 11:00 AM') ? 'selected' : '' }}>9:00 AM — 11:00 AM</option>
          <option value="11:15 AM — 1:15 PM" {{ (request('time')=='11:15 AM — 1:15 PM') ? 'selected' : '' }}>11:15 AM — 1:15 PM</option>
          <option value="2:15 PM — 4:15 PM" {{ (request('time')=='2:15 PM — 4:15 PM') ? 'selected' : '' }}>2:15 PM — 4:15 PM</option>
          <option value="4:30 PM — 6:30 PM" {{ (request('time')=='4:30 PM — 6:30 PM') ? 'selected' : '' }}>4:30 PM — 6:30 PM</option>
        @elseif($isFriday)
          {{-- Friday Times --}}
          <option value="9:00 AM — 11:00 AM" {{ (request('time')=='9:00 AM — 11:00 AM') ? 'selected' : '' }}>9:00 AM — 11:00 AM</option>
          <option value="11:15 AM — 1:15 PM" {{ (request('time')=='11:15 AM — 1:15 PM') ? 'selected' : '' }}>11:15 AM — 1:15 PM</option>
          <option value="4:45 PM — 6:45 PM" {{ (request('time')=='4:45 PM — 6:45 PM') ? 'selected' : '' }}>4:45 PM — 6:45 PM</option>
          <option value="7:00 PM — 9:00 PM" {{ (request('time')=='7:00 PM — 9:00 PM') ? 'selected' : '' }}>7:00 PM — 9:00 PM</option>
        @else
          {{-- Weekday Times (Monday - Thursday) --}}
          <option value="12:00 PM — 2:00 PM" {{ (request('time')=='12:00 PM — 2:00 PM') ? 'selected' : '' }}>12:00 PM — 2:00 PM</option>
          <option value="2:15 PM — 4:15 PM" {{ (request('time')=='2:15 PM — 4:15 PM') ? 'selected' : '' }}>2:15 PM — 4:15 PM</option>
          <option value="4:45 PM — 6:45 PM" {{ (request('time')=='4:45 PM — 6:45 PM') ? 'selected' : '' }}>4:45 PM — 6:45 PM</option>
          <option value="7:00 PM — 9:00 PM" {{ (request('time')=='7:00 PM — 9:00 PM') ? 'selected' : '' }}>7:00 PM — 9:00 PM</option>
        @endif
      </select>
      <p class="att-error hidden text-red-500 text-xs mt-1">Please choose a time slot.</p>
    </div>
    <div class="lg:col-span-2">
      <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Subject / Theme <span class="text-red-500">*</span></label>
      <input name="subject" id="subject-input" placeholder="e.g. Mathematics" value="{{ $subject }}"
             class="att-field w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      <p class="att-error hidden text-red-500 text-xs mt-1">Subject / theme is required.</p>
    </div>
    <div class="lg:col-span-2">
      <label class="block h-4 leading-4 text-xs font-semibold text-gray-600 mb-1.5">Teacher <span class="text-red-500">*</span></label>
      <select name="teacher" id="teacher-select" class="att-field w-full h-10 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Select Teacher</option>
        @foreach($teachers as $t)
          <option value="{{ $t->name }}" {{ request('teacher') == $t->name ? 'selected' : '' }}>{{ $t->name }}{{ $t->reference ? ' (' . $t->reference . ')' : '' }}</option>
        @endforeach
      </select>
      <p class="att-error hidden text-red-500 text-xs mt-1">Please choose a teacher.</p>
    </div>
    <div class="lg:col-span-1">
      <label class="block h-4 leading-4 text-xs font-semibold mb-1.5 invisible" aria-hidden="true">Load</label>
      <button class="w-full h-10 inline-flex items-center justify-center bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">Load</button>
    </div>
  </form>
</div>

{{-- Loaded-student summary (display only) --}}
@if(request('reference') && $students->count() > 0)
<div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
    <div class="min-w-0">
      <div class="h-4 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-700">
        <span class="text-sm leading-none w-4 text-center shrink-0">🎓</span><span class="truncate">Student Reference</span>
      </div>
      <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ $reference }}</div>
      <div class="mt-0.5 h-4 leading-4 text-xs text-blue-600 truncate text-center">{{ $students->first()->first_name }} {{ $students->first()->last_name }}</div>
    </div>
    <div class="min-w-0">
      <div class="h-4 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-700">
        <span class="text-sm leading-none w-4 text-center shrink-0">⏰</span><span class="truncate">Time Slot</span>
      </div>
      <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ request('time') ?: '—' }}</div>
    </div>
    <div class="min-w-0">
      <div class="h-4 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-700">
        <span class="text-sm leading-none w-4 text-center shrink-0">📖</span><span class="truncate">Subject / Theme</span>
      </div>
      <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ $subject ?: '—' }}</div>
    </div>
    <div class="min-w-0">
      <div class="h-4 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-700">
        <span class="text-sm leading-none w-4 text-center shrink-0">👤</span><span class="truncate">Teacher</span>
      </div>
      <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ request('teacher') ?: '—' }}</div>
    </div>
    <div class="min-w-0">
      <div class="h-4 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-700">
        <span class="text-sm leading-none w-4 text-center shrink-0">📅</span><span class="truncate">Date</span>
      </div>
      <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
    </div>
    <div class="min-w-0">
      <div class="h-4 flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-700">
        <span class="text-sm leading-none w-4 text-center shrink-0">👥</span><span class="truncate">Students</span>
      </div>
      <div class="mt-1.5 h-5 leading-5 text-sm font-semibold text-gray-900 truncate text-center">{{ $students->count() }}</div>
    </div>
  </div>
</div>
@endif

@if(!empty($feeDue))
<div class="mb-6 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
  ⚠️
  @if($feeDue['never_paid'])
    <strong>No payment recorded yet</strong> for {{ $reference }}
  @else
    <strong>Fee due since {{ $feeDue['days'] }} day{{ $feeDue['days'] == 1 ? '' : 's' }}</strong> for {{ $reference }}
  @endif
  (outstanding £{{ number_format($feeDue['amount'], 2) }}).
</div>
@endif

@if(request('reference') && $students->count() > 0)
  <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
  <form method="POST" action="{{ route('attendance.save') }}" id="save-form">@csrf
    <input type="hidden" name="date" id="save-date" value="{{ $date }}">
    <input type="hidden" name="subject" id="save-subject" value="{{ $subject }}">
    <input type="hidden" name="time" id="save-time" value="{{ request('time') }}">
    <input type="hidden" name="teacher" id="save-teacher" value="{{ request('teacher') }}">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[640px] text-sm">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="px-4 py-3 text-left w-10 text-xs font-semibold uppercase tracking-wider text-gray-600">#</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Student</th>
            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">✅ Present</th>
            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">❌ Absent</th>
            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">🔄 Clear</th>
          </tr>
        </thead>
        <tbody>
        @foreach($students as $i => $s)
          @php $sel = optional(\App\Models\StudentAttendance::where(['student_id'=>$s->id,'date'=>$date,'subject'=>$subject,'time'=>request('time')])->first())->status; @endphp
          <tr class="att-row border-b even:bg-gray-50 hover:bg-blue-50">
            <td class="px-4 py-3.5 align-middle text-gray-500">{{ $i + 1 }}</td>
            <td class="px-4 py-3.5 align-middle">
              <span class="font-medium text-gray-900">{{ $s->first_name }} {{ $s->last_name }}</span>
              <span class="text-gray-500 text-xs ml-1">({{ $s->reference }})</span>
            </td>
            <td class="px-4 py-3.5 text-center align-middle att-cell"><input type="radio" name="statuses[{{ $s->id }}]" value="present" class="h-4 w-4 align-middle accent-green-600" {{ $sel=='present'?'checked':'' }}></td>
            <td class="px-4 py-3.5 text-center align-middle att-cell"><input type="radio" name="statuses[{{ $s->id }}]" value="absent" class="h-4 w-4 align-middle accent-red-600" {{ $sel=='absent'?'checked':'' }}></td>
            <td class="px-4 py-3.5 text-center align-middle att-cell"><input type="radio" name="statuses[{{ $s->id }}]" value="" class="h-4 w-4 align-middle accent-gray-500" {{ empty($sel)?'checked':'' }}></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-t bg-gray-50">
      <div class="flex flex-wrap items-center gap-6 text-sm">
        <span class="font-semibold text-gray-700">Attendance Summary</span>
        <span class="inline-flex items-center gap-1.5 text-green-700">✅ <span>Present</span> <strong id="count-present">0</strong></span>
        <span class="inline-flex items-center gap-1.5 text-red-700">❌ <span>Absent</span> <strong id="count-absent">0</strong></span>
        <span class="inline-flex items-center gap-1.5 text-gray-600">⊖ <span>Unmarked</span> <strong id="count-unmarked">0</strong></span>
      </div>
      <button class="h-10 px-5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">💾 Save Attendance</button>
    </div>
  </form>
  </div>
@elseif(request('reference'))
  <div class="bg-white rounded-xl border shadow-sm p-10 text-center text-gray-500">No student found for this reference.</div>
@else
  <div class="bg-white rounded-xl border shadow-sm p-10 text-center text-gray-500">Enter a reference number and time slot to log attendance.</div>
@endif

<script>
// Dynamic time slot update based on selected date
document.getElementById('attendance-date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const dayOfWeek = selectedDate.getDay(); // 0=Sunday, 1=Monday, ..., 5=Friday, 6=Saturday
    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
    const isFriday = (dayOfWeek === 5);

    const timeSelect = document.getElementById('time-select');
    const currentValue = timeSelect.value;

    // Define time slot options
    const weekendTimes = [
        { value: '9:00 AM — 11:00 AM', label: '9:00 AM — 11:00 AM' },
        { value: '11:15 AM — 1:15 PM', label: '11:15 AM — 1:15 PM' },
        { value: '2:15 PM — 4:15 PM', label: '2:15 PM — 4:15 PM' },
        { value: '4:30 PM — 6:30 PM', label: '4:30 PM — 6:30 PM' }
    ];

    const fridayTimes = [
        { value: '9:00 AM — 11:00 AM', label: '9:00 AM — 11:00 AM' },
        { value: '11:15 AM — 1:15 PM', label: '11:15 AM — 1:15 PM' },
        { value: '4:45 PM — 6:45 PM', label: '4:45 PM — 6:45 PM' },
        { value: '7:00 PM — 9:00 PM', label: '7:00 PM — 9:00 PM' }
    ];

    const weekdayTimes = [
        { value: '12:00 PM — 2:00 PM', label: '12:00 PM — 2:00 PM' },
        { value: '2:15 PM — 4:15 PM', label: '2:15 PM — 4:15 PM' },
        { value: '4:45 PM — 6:45 PM', label: '4:45 PM — 6:45 PM' },
        { value: '7:00 PM — 9:00 PM', label: '7:00 PM — 9:00 PM' }
    ];

    // Build new options HTML
    let optionsHtml = '<option value="">Select Time</option>';
    let timesToUse;

    if (isWeekend) {
        timesToUse = weekendTimes;
    } else if (isFriday) {
        timesToUse = fridayTimes;
    } else {
        timesToUse = weekdayTimes;
    }

    timesToUse.forEach(time => {
        optionsHtml += `<option value="${time.value}">${time.label}</option>`;
    });

    // Update the select element
    timeSelect.innerHTML = optionsHtml;

    // Try to restore previous selection if it exists in new options
    if (currentValue) {
        const optionExists = Array.from(timeSelect.options).some(opt => opt.value === currentValue);
        if (optionExists) {
            timeSelect.value = currentValue;
        }
    }

    // Show visual feedback
    timeSelect.classList.add('border-blue-500');
    setTimeout(() => {
        timeSelect.classList.remove('border-blue-500');
    }, 300);
});

// Trigger on page load if date is pre-selected
window.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('attendance-date');
    if (dateInput.value) {
        // Trigger change event to set correct times on initial load
        dateInput.dispatchEvent(new Event('change'));
    }
});
</script>

<script>
// --- Required-field validation (client-side; server behavior unchanged) ---
function validateAttendanceForm() {
    const ids = ['attendance-date', 'reference-input', 'time-select', 'subject-input', 'teacher-select'];
    let firstInvalid = null;
    ids.forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        const wrapper = el.closest('div');
        const err = wrapper ? wrapper.querySelector('.att-error') : null;
        const empty = !el.value || !el.value.trim();
        el.classList.toggle('border-red-500', empty);
        el.classList.toggle('border-gray-300', !empty);
        if (err) err.classList.toggle('hidden', !empty);
        if (empty && !firstInvalid) firstInvalid = el;
    });
    if (firstInvalid) firstInvalid.focus();
    return !firstInvalid;
}

// Clear a field's error state as soon as it is filled
document.querySelectorAll('.att-field').forEach(function (el) {
    ['input', 'change'].forEach(function (evt) {
        el.addEventListener(evt, function () {
            if (el.value && el.value.trim()) {
                el.classList.remove('border-red-500');
                el.classList.add('border-gray-300');
                const wrapper = el.closest('div');
                const err = wrapper ? wrapper.querySelector('.att-error') : null;
                if (err) err.classList.add('hidden');
            }
        });
    });
});

// Block Load until all required fields are filled
document.getElementById('load-form').addEventListener('submit', function (e) {
    if (!validateAttendanceForm()) e.preventDefault();
});

// On Save: validate first, then copy the CURRENT top-form field values into the
// save form's hidden inputs — otherwise anything changed after the last "Load"
// click is lost
(function () {
    const saveForm = document.getElementById('save-form');
    if (!saveForm) return;
    saveForm.addEventListener('submit', function (e) {
        if (!validateAttendanceForm()) { e.preventDefault(); return; }
        document.getElementById('save-date').value    = document.getElementById('attendance-date').value;
        document.getElementById('save-time').value    = document.getElementById('time-select').value;
        document.getElementById('save-subject').value = document.getElementById('subject-input').value;
        document.getElementById('save-teacher').value = document.getElementById('teacher-select').value;
    });
})();

// --- Selected-status highlighting + live summary counts (display only) ---
function refreshAttendanceUI() {
    let present = 0, absent = 0, unmarked = 0;
    document.querySelectorAll('.att-row').forEach(function (row) {
        row.querySelectorAll('.att-cell').forEach(function (c) { c.classList.remove('bg-green-100', 'bg-red-100'); });
        const checked = row.querySelector('input[type="radio"]:checked');
        if (checked && checked.value === 'present') {
            checked.closest('.att-cell').classList.add('bg-green-100');
            present++;
        } else if (checked && checked.value === 'absent') {
            checked.closest('.att-cell').classList.add('bg-red-100');
            absent++;
        } else {
            unmarked++;
        }
    });
    const set = function (id, v) { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('count-present', present);
    set('count-absent', absent);
    set('count-unmarked', unmarked);
}
document.querySelectorAll('.att-row input[type="radio"]').forEach(function (r) {
    r.addEventListener('change', refreshAttendanceUI);
});
refreshAttendanceUI();
</script>

@endsection
