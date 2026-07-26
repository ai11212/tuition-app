@extends('layouts.app')

@section('content')
<style>
    /* Hide navigation header for print view */
    header { display: none !important; }
    aside { display: none !important; }
    main { padding-top: 0 !important; padding: 0 !important; }
    body { margin: 0; padding: 0; }
    
    /* Balanced spacing for screen view */
    .compact-page {
        max-width: 1300px;
        padding: 3rem 6rem;
        margin: 2rem auto;
        background: white;
    }
    
    .compact-header {
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
    }
    
    .compact-section {
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
    }
    
    .compact-day {
        margin-bottom: 2rem;
        padding-left: 0.5rem;
    }
    
    .compact-day h3 {
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    .compact-slot {
        margin-bottom: 0.75rem;
        line-height: 1.6;
        display: flex;
        align-items: baseline;
    }
    
    /* Monthly week sections */
    .week-section {
        margin-bottom: 2.5rem;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #f9fafb;
    }
    
    .week-section h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #3b82f6;
    }
    
    /* Button styling */
    .no-print button,
    .no-print a {
        white-space: nowrap;
        min-width: auto;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .compact-page {
            max-width: 100%;
            padding: 1.5rem 2rem;
            margin: 0.5rem;
        }
        
        .compact-header {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .compact-header h1 {
            font-size: 1.5rem !important;
        }
        
        .compact-header p {
            font-size: 0.875rem !important;
        }
        
        .compact-header .flex {
            flex-direction: column;
            gap: 1rem;
        }
        
        .compact-header .no-print {
            width: 100%;
            flex-direction: column;
        }
        
        .compact-header .no-print button,
        .compact-header .no-print a {
            width: 100%;
            justify-content: center;
        }
        
        .compact-section {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .compact-section .flex-wrap {
            gap: 0.5rem !important;
        }
        
        .compact-day {
            margin-bottom: 1.5rem;
            padding-left: 0.75rem;
        }
        
        .compact-day h3 {
            font-size: 1rem;
            padding-left: 0.25rem;
        }
        
        .compact-slot {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
            padding-left: 0.5rem;
        }
    }
    
    @media print {
        .no-print { display: none !important; }
        body { background: white; margin: 0; padding: 0; }
        .shadow-lg { box-shadow: none !important; }
        header { display: none !important; }
        aside { display: none !important; }
        main { padding: 0 !important; }
        
        /* Portrait orientation for all prints */
        @page { 
            size: portrait;
            margin: 15mm;
        }
        
        /* Each week on new page for monthly */
        .week-section {
            page-break-after: always;
            page-break-inside: avoid;
            border: none;
            background: white;
            padding: 0;
            margin-bottom: 0;
        }
        
        .week-section:last-child {
            page-break-after: auto;
        }
        
        /* Compact for print */
        .compact-page {
            max-width: 100%;
            padding: 0.5rem;
            margin: 0;
        }
        
        .compact-header {
            padding: 0.25rem 0;
            margin-bottom: 0.5rem;
        }
        
        .compact-section {
            padding: 0.25rem 0;
            margin-bottom: 0.5rem;
        }
        
        .compact-day {
            margin-bottom: 0.5rem;
        }
        
        .compact-day h3 {
            margin-bottom: 0.25rem;
            padding-bottom: 0.25rem;
            font-size: 0.9rem;
        }
        
        .compact-slot {
            margin-bottom: 0.15rem;
            line-height: 1.2;
            display: flex !important;
            flex-direction: row !important;
            align-items: baseline !important;
            gap: 0.5rem !important;
        }
        
        .compact-slot span {
            display: inline-block !important;
        }
        
        /* Force single page */
        @page {
            size: A4;
            margin: 1cm;
        }
    }
</style>

<div class="compact-page">

    {{-- Sibling switcher (screen only — never printed): pick whose timetable to print --}}
    @if(isset($allSiblings) && $allSiblings->count() > 1)
    <div class="no-print mb-4 flex flex-wrap items-center gap-2 p-3 bg-gray-50 border rounded-lg">
        <span class="text-sm text-gray-600 font-medium">Print for:</span>
        <a href="{{ route('student.timetable.print', $reference) }}"
           class="px-3 py-1.5 rounded-lg text-sm border {{ empty($selectedStudentId) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">
            All siblings
        </a>
        @foreach($allSiblings as $sib)
            <a href="{{ route('student.timetable.print', [$reference, 'student' => $sib->id]) }}"
               class="px-3 py-1.5 rounded-lg text-sm border {{ ($selectedStudentId ?? null) == $sib->id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' }}">
                {{ $sib->first_name }} {{ $sib->last_name }}
            </a>
        @endforeach
    </div>
    @endif

    @if(isset($hasSiblings) && $hasSiblings)
        {{-- Multiple Students: Show separate list for each --}}
        @foreach($studentTimetables as $index => $studentData)
            <div class="bg-white" style="page-break-after: {{ $index < count($studentTimetables) - 1 ? 'always' : 'auto' }};">
                
                {{-- Header --}}
                <div class="compact-header border-b-2 border-gray-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-1">Student Timetable</h1>
                            <p class="text-base text-gray-600">Reference: <span class="font-semibold text-blue-600">{{ $reference }}</span></p>
                        </div>
                        <div class="no-print flex gap-3">
                            @if($index === 0)
                                <a href="{{ route('students.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                                    ← Back to All Students
                                </a>
                                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print Timetable
                                </button>
                                <a href="{{ route('students.create') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Admission
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Student Info --}}
                <div class="compact-section border-b border-gray-200">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 inline-block">
                        <h3 class="font-semibold text-blue-900 text-lg">{{ $studentData['student']->first_name }} {{ $studentData['student']->last_name }}</h3>
                        @if($studentData['student']->guardian_name)
                            <p class="text-sm text-blue-600 mt-1">Guardian: {{ $studentData['student']->guardian_name }}</p>
                        @endif
                    </div>
                </div>

                {{-- Clean List View --}}
                <div class="compact-section">
                    @if($studentData['hasEntries'])
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">📅</span> {{ ucfirst($period ?? 'Weekly') }} Schedule
                        </h2>
                        
                        @php 
                            $timetableGrid = $studentData['grid'];
                            $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        @endphp

                        {{-- Check if Monthly Schedule --}}
                        @if(isset($timetableGrid['isMonthly']) && $timetableGrid['isMonthly'])
                            {{-- Monthly: Show stacked weeks (Approach 1) --}}
                            @foreach($timetableGrid['grid'] as $weekLabel => $weekData)
                                <div class="week-section">
                                    <h3>{{ $weekLabel }}</h3>
                                    
                                    @foreach($dayOrder as $day)
                                        @if(isset($weekData[$day]) && count($weekData[$day]) > 0)
                                            <div class="compact-day">
                                                <h3 class="font-bold text-gray-900 border-b-2 border-gray-400 text-base">{{ $day }}</h3>
                                                <div class="ml-4 mt-2">
                                                    @foreach($weekData[$day] as $timeSlot => $entry)
                                                        <div class="compact-slot">
                                                            <span class="text-gray-600 font-medium min-w-[130px] inline-block">{{ $timeSlot }}</span>
                                                            <span class="text-gray-900 font-semibold">{{ $entry['subject'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            {{-- Weekly: Show standard list by day --}}
                            @foreach($dayOrder as $day)
                                @if(isset($timetableGrid['grid'][$day]) && count($timetableGrid['grid'][$day]) > 0)
                                    <div class="compact-day">
                                        <h3 class="font-bold text-gray-900 border-b-2 border-gray-400 text-base">{{ $day }}</h3>
                                        <div class="ml-4 mt-2">
                                            @foreach($timetableGrid['grid'][$day] as $timeSlot => $entry)
                                                <div class="compact-slot">
                                                    <span class="text-gray-600 font-medium min-w-[130px] inline-block">{{ $timeSlot }}</span>
                                                    <span class="text-gray-900 font-semibold">{{ $entry['subject'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                    @else
                        <div class="text-center py-8 text-gray-500">
                            No timetable entries for this student.
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="border-t border-gray-200 py-1 text-center text-xs text-gray-500">
                    Generated on {{ now()->format('F j, Y \a\t g:i A') }}
                </div>
            </div>
        @endforeach
        
    @else
        {{-- Single Student or Combined View --}}
        <div class="bg-white">
            
            {{-- Header --}}
            <div class="compact-header border-b-2 border-gray-300">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">Student Timetable</h1>
                        <p class="text-base text-gray-600">Reference: <span class="font-semibold text-blue-600">{{ $reference }}</span></p>
                    </div>
                    <div class="no-print flex gap-3">
                        <a href="{{ route('students.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                            ← Back to All Students
                        </a>
                        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Timetable
                        </button>
                        <a href="{{ route('students.create') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Admission
                        </a>
                    </div>
                </div>
            </div>

            {{-- Student Info --}}
            <div class="compact-section border-b border-gray-200">
                <div class="flex flex-wrap gap-3">
                    @foreach($students as $student)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2">
                            <span class="font-semibold text-blue-900">{{ $student->first_name }} {{ $student->last_name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Clean List View --}}
            <div class="compact-section">
                @if($hasEntries)
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-2xl">📅</span> {{ ucfirst($period ?? 'Weekly') }} Schedule
                    </h2>
                    
                    @php 
                        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    @endphp

                    {{-- Check if Monthly Schedule --}}
                    @if(isset($timetableGrid['isMonthly']) && $timetableGrid['isMonthly'])
                        {{-- Monthly: Show stacked weeks (Approach 1) --}}
                        @foreach($timetableGrid['grid'] as $weekLabel => $weekData)
                            <div class="week-section">
                                <h3>{{ $weekLabel }}</h3>
                                
                                @foreach($dayOrder as $day)
                                    @if(isset($weekData[$day]) && count($weekData[$day]) > 0)
                                        <div class="compact-day">
                                            <h3 class="font-bold text-gray-800 border-b border-gray-400">{{ $day }}</h3>
                                            <div class="ml-3">
                                                @foreach($weekData[$day] as $timeSlot => $entry)
                                                    <div class="compact-slot flex gap-2">
                                                        <span class="text-gray-600 text-sm min-w-[100px]">{{ $timeSlot }}</span>
                                                        <span class="text-gray-900 font-medium text-sm">{{ $entry['subject'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        {{-- Weekly: Show standard list by day --}}
                        @foreach($dayOrder as $day)
                            @if(isset($timetableGrid['grid'][$day]) && count($timetableGrid['grid'][$day]) > 0)
                                <div class="compact-day">
                                    <h3 class="font-bold text-gray-800 border-b border-gray-400">{{ $day }}</h3>
                                    <div class="ml-3">
                                        @foreach($timetableGrid['grid'][$day] as $timeSlot => $entry)
                                            <div class="compact-slot flex gap-2">
                                                <span class="text-gray-600 text-sm min-w-[100px]">{{ $timeSlot }}</span>
                                                <span class="text-gray-900 font-medium text-sm">{{ $entry['subject'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif

                @else
                    <div class="text-center py-4 text-gray-500 text-sm">
                        No timetable entries found.
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-200 py-1 text-center text-xs text-gray-500">
                Generated on {{ now()->format('F j, Y \a\t g:i A') }}
            </div>
        </div>
    @endif
    
</div>
@endsection
