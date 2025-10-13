@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg">
        {{-- Header Section --}}
        <div class="border-b border-gray-200 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Student Timetable</h1>
                    <p class="text-lg text-gray-600">Reference: <span class="font-semibold text-blue-600">{{ $reference }}</span></p>
                </div>
                <div class="flex gap-3 no-print">
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                        🖨️ Print Timetable
                    </button>
                    <a href="{{ route('students.create') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">
                        ➕ New Admission
                    </a>
                </div>
            </div>
        </div>

        {{-- Student Information --}}
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">👥 Students Enrolled</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students as $index => $student)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-900">Student {{ $index + 1 }}</h3>
                        <p class="text-blue-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                        @if($student->guardian_name)
                            <p class="text-sm text-blue-600 mt-1">Guardian: {{ $student->guardian_name }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Timetable Section --}}
        <div class="p-6">
            @if($hasEntries)
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    📅 {{ ucfirst($period ?? 'weekly') }} Schedule
                </h2>
                
                @if(isset($timetableGrid['isMonthly']) && $timetableGrid['isMonthly'])
                    {{-- Monthly View: Show each week separately --}}
                    @if(count($timetableGrid['filledDays']) > 0 && count($timetableGrid['filledTimeSlots']) > 0)
                        @foreach($timetableGrid['grid'] as $weekName => $weekGrid)
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-700 mb-3">{{ $weekName }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse border border-gray-300 bg-white rounded-lg">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-gray-700">Time Slot</th>
                                                @foreach($timetableGrid['filledDays'] as $day)
                                                    <th class="border border-gray-300 px-4 py-3 text-center font-semibold text-gray-700">{{ $day }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($timetableGrid['filledTimeSlots'] as $timeSlot)
                                                @php
                                                    // Check if this time slot has any actual classes in this week
                                                    $hasClasses = false;
                                                    foreach($timetableGrid['filledDays'] as $day) {
                                                        if(isset($weekGrid[$day][$timeSlot])) {
                                                            $hasClasses = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($hasClasses)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="border border-gray-300 px-4 py-4 font-medium text-gray-700 bg-gray-50">
                                                            {{ $timeSlot }}
                                                        </td>
                                                        @foreach($timetableGrid['filledDays'] as $day)
                                                            <td class="border border-gray-300 px-4 py-4 text-center">
                                                                @if(isset($weekGrid[$day][$timeSlot]))
                                                                    @php $entry = $weekGrid[$day][$timeSlot]; @endphp
                                                                    <div class="bg-blue-100 border border-blue-300 rounded-lg p-3">
                                                                        <div class="font-semibold text-blue-900">{{ $entry['subject'] }}</div>
                                                                        @if($entry['teacher'])
                                                                            <div class="text-sm text-blue-700 mt-1">👨‍🏫 {{ $entry['teacher'] }}</div>
                                                                        @endif
                                                                        @if($entry['room'])
                                                                            <div class="text-sm text-blue-600 mt-1">🏠 {{ $entry['room'] }}</div>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <div class="text-gray-400 text-sm">—</div>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @else
                    {{-- Weekly View: Show full 7-day grid with day-specific time slots --}}
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300 bg-white rounded-lg">
                            <thead>
                                <tr class="bg-gray-100">
                                    @foreach($timetableGrid['allDays'] as $day)
                                        <th class="border border-gray-300 px-4 py-3 text-center font-semibold text-gray-700">{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($slotIndex = 0; $slotIndex < 4; $slotIndex++)
                                    <tr class="hover:bg-gray-50">
                                        @foreach($timetableGrid['allDays'] as $day)
                                            @php
                                                // Get the time slot label for this day and slot index
                                                $timeSlotLabel = $timetableGrid['daySpecificSlots'][$day][$slotIndex] ?? '—';
                                            @endphp
                                            <td class="border border-gray-300 px-4 py-4 text-center">
                                                <div class="font-bold text-sm text-gray-600 mb-2">{{ $timeSlotLabel }}</div>
                                                @if(isset($timetableGrid['grid'][$day][$timeSlotLabel]))
                                                    @php $entry = $timetableGrid['grid'][$day][$timeSlotLabel]; @endphp
                                                    <div class="bg-blue-100 border border-blue-300 rounded-lg p-3">
                                                        <div class="font-semibold text-blue-900">{{ $entry['subject'] }}</div>
                                                        @if($entry['teacher'])
                                                            <div class="text-sm text-blue-700 mt-1">👨‍🏫 {{ $entry['teacher'] }}</div>
                                                        @endif
                                                        @if($entry['room'])
                                                            <div class="text-sm text-blue-600 mt-1">🏠 {{ $entry['room'] }}</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="text-gray-400 text-sm">—</div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                {{-- No Timetable Entries --}}
                <div class="text-center py-12">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8">
                        <div class="text-6xl mb-4">📅</div>
                        <h2 class="text-2xl font-semibold text-yellow-800 mb-2">No Timetable Created</h2>
                        <p class="text-yellow-700 mb-6">This student admission was saved without any timetable entries.</p>
                        <div class="flex gap-3 justify-center">
                            <a href="{{ route('students.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                                Create New Admission
                            </a>
                            <a href="{{ route('tt.form') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">
                                Print Existing Timetable
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <div class="flex justify-between items-center text-sm text-gray-600">
                <div>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</div>
                <div>Tuition Management System</div>
            </div>
        </div>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
    /* Force landscape orientation for wide timetable */
    @page {
        size: A4 landscape;
        margin: 0.5cm;
    }
    
    /* Hide screen-only elements */
    .no-print {
        display: none !important;
    }
    
    /* Compact everything */
    * {
        margin: 0;
        padding: 0;
    }
    
    body {
        font-size: 10pt;
        line-height: 1.2;
        padding: 0;
        margin: 0;
    }
    
    /* Remove container constraints */
    .max-w-6xl {
        max-width: none;
        margin: 0;
        padding: 0;
    }
    
    /* Remove decorative styling */
    .shadow-lg, .rounded-lg, .rounded {
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    
    /* Compact header */
    .border-b.border-gray-200.p-6 {
        padding: 0.25rem !important;
        border-bottom: 1px solid #000 !important;
    }
    
    h1 {
        font-size: 14pt;
        margin-bottom: 0.1rem;
    }
    
    p, h2, h3 {
        font-size: 9pt;
        margin: 0.1rem 0;
    }
    
    /* Compact student info section */
    .p-6.border-b.border-gray-200 {
        padding: 0.25rem !important;
        border-bottom: 1px solid #000 !important;
    }
    
    .grid.md\:grid-cols-2.lg\:grid-cols-3 {
        display: flex;
        flex-wrap: wrap;
        gap: 0.2rem;
    }
    
    .bg-blue-50 {
        background-color: #f0f0f0 !important;
        padding: 0.2rem !important;
        border: 1px solid #000 !important;
    }
    
    /* Compact timetable section */
    .p-6 {
        padding: 0.25rem !important;
    }
    
    /* Table styling - prevent page breaks */
    .overflow-x-auto {
        overflow: visible !important;
    }
    
    table {
        width: 100% !important;
        border-collapse: collapse;
        page-break-inside: avoid;
        page-break-after: avoid;
        font-size: 9pt;
        margin: 0;
    }
    
    thead {
        display: table-header-group;
    }
    
    tbody {
        display: table-row-group;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: avoid;
    }
    
    th, td {
        border: 1px solid #000 !important;
        padding: 2px 4px !important;
        text-align: center;
        vertical-align: top;
    }
    
    th {
        background-color: #e0e0e0 !important;
        font-weight: bold;
        font-size: 9pt;
    }
    
    /* Compact time slot labels and subject cells */
    .font-bold.text-sm {
        font-size: 8pt !important;
        margin-bottom: 2px !important;
        font-weight: bold;
    }
    
    .bg-blue-100 {
        background-color: #f0f0f0 !important;
        padding: 2px !important;
        border: none !important;
        border-radius: 0 !important;
    }
    
    .font-semibold.text-blue-900 {
        font-size: 9pt !important;
        color: #000 !important;
        font-weight: bold;
    }
    
    .text-sm.text-blue-700,
    .text-sm.text-blue-600 {
        font-size: 7pt !important;
        color: #000 !important;
        margin-top: 1px !important;
    }
    
    /* Hide emojis and icons in print */
    .text-6xl,
    button:before,
    a:before {
        display: none;
    }
    
    /* Compact footer */
    .border-t.border-gray-200.p-6.bg-gray-50 {
        padding: 0.2rem !important;
        border-top: 1px solid #000 !important;
        background-color: transparent !important;
        font-size: 7pt;
    }
    
    /* Remove hover effects */
    .hover\:bg-gray-50:hover {
        background-color: transparent !important;
    }
}
</style>
@endsection