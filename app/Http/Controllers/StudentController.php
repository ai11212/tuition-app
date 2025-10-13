<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Facades\Schema;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $reference = $request->input('reference');
        
        $students = Student::query()
            ->when($reference, function($q) use ($reference) {
                return $q->where('reference', 'like', $reference . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
            
        return view('students.index', compact('students', 'reference'));
    }

    public function create()
    {
        // start step 1
        return view('students.create');
    }

    public function next(Request $request)
    {
        // Step-1: validate minimal guardian + main student (no save)
        $data = $request->validate([
            // guardian FIRST (post_code belongs to guardian and saved on student row)
            'guardian_name'     => 'required|string|max:120',
            'guardian_relation' => 'nullable|string|max:120',
            'guardian_phone'    => 'nullable|string|max:120',
            'guardian_email'    => 'nullable|email|max:50',
            'guardian_address'  => 'nullable|string|max:190',
            'guardian_city'     => 'nullable|string|max:120',
            'guardian_notes'    => 'nullable|string|max:255',
            'post_code'         => 'nullable|string|max:120',

            // reference optional on step1 (we will auto-generate if empty in store)
            'reference'         => 'nullable|string|max:255',

            // primary student (no phone/email/address here as requested)
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'gender'            => 'nullable|in:male,female,other',
            'dob'               => 'nullable|date',
            'city'              => 'nullable|string|max:120',
            'enroll_date'       => 'nullable|date',
            'start_date'        => 'nullable|date',
            'deposit'           => 'nullable|numeric',
            'payment'           => 'nullable|numeric',
            'period'            => 'nullable|string|max:32',

            // siblings[] array (same minimal fields)
            'siblings'                      => 'array',
            'siblings.*.first_name'         => 'nullable|string|max:100',
            'siblings.*.last_name'          => 'nullable|string|max:100',
            'siblings.*.gender'             => 'nullable|in:male,female,other',
            'siblings.*.dob'                => 'nullable|date',
        ]);

        // trim/cap guardian post_code (multibyte safe)
        if (isset($data['post_code'])) {
            $v = trim((string) $data['post_code']);
            $data['post_code'] = function_exists('mb_substr') ? mb_substr($v, 0, 120) : substr($v, 0, 120);
            if ($data['post_code'] === '') $data['post_code'] = null;
        }

        // Drop empty sibling rows
        $data['siblings'] = collect($data['siblings'] ?? [])
            ->map(fn($row) => array_filter($row ?? [], fn($v) => $v !== null && $v !== ''))
            ->filter(fn($row) => Arr::get($row, 'first_name') || Arr::get($row, 'last_name'))
            ->values()
            ->all();

        // Stash in session and go to confirm
        session(['admission' => $data]);

        return redirect()->route('students.confirm');
    }

    public function confirm(Request $request)
    {
        $admission = $request->session()->get('admission');
        if (!$admission) {
            return redirect()->route('students.create')->with('status', 'Please fill Step 1 first.');
        }
        return view('students.confirm', ['admission' => $admission]);
    }

    public function store(Request $request)
    {
        // Pull from session; nothing is saved on step 1
        $admission = $request->session()->get('admission');
        if (!$admission) {
            return redirect()->route('students.create')->with('status', 'Session expired. Please start again.');
        }

        // Ensure reference exists (DB says NOT NULL in your logs)
        $reference = $admission['reference'] ?? null;
        if (!$reference) {
            // simple auto-ref; if your project had a custom generator, replace here
            $reference = 'A' . date('ymdHis');
        }

        // Common guardian data copied to each student row (post_code is guardian’s)
        $guardian = [
            'guardian_name'     => $admission['guardian_name'] ?? null,
            'guardian_relation' => $admission['guardian_relation'] ?? null,
            'guardian_phone'    => $admission['guardian_phone'] ?? null,
            'guardian_email'    => $admission['guardian_email'] ?? null,
            'guardian_address'  => $admission['guardian_address'] ?? null,
            'post_code'         => $admission['post_code'] ?? null,
        ];

    // Create primary student
        $main = [
            'reference'   => $reference,
            'first_name'  => $admission['first_name'] ?? null,
            'last_name'   => $admission['last_name'] ?? null,
            'gender'      => $admission['gender'] ?? null,
            'dob'         => $admission['dob'] ?? null,
            'guardian_city' => $admission['guardian_city'] ?? null,
            'city'        => $admission['guardian_city'] ?? null, // Map guardian_city to city as well
            'enroll_date' => $admission['enroll_date'] ?? null,
            'start_date'  => $admission['start_date'] ?? null,
            'deposit'     => $admission['deposit'] ?? null,
            'payment'     => $admission['payment'] ?? null,
            'period'      => $admission['period'] ?? null,
        ] + $guardian;

        $created = [];
        // Filter $main to only columns that exist in the students table on this environment
        $allowed = Schema::hasTable('students') ? Schema::getColumnListing('students') : [];
        $payload = array_intersect_key($main, array_flip($allowed));
        $created[] = Student::create($payload);

    // Timetable payload from form (timetable[studentIndex][dayIndex][slotIndex] = subject)
    $timetableInput = $request->input('timetable', []);

        // Create siblings
        foreach (($admission['siblings'] ?? []) as $sib) {
            $row = [
                'reference'  => $reference,
                'first_name' => $sib['first_name'] ?? null,
                'last_name'  => $sib['last_name'] ?? null,
                'gender'     => $sib['gender'] ?? null,
                'dob'        => $sib['dob'] ?? null,
            ] + $guardian;

            // skip fully empty names
            if (!($row['first_name'] || $row['last_name'])) continue;

            $rowPayload = array_intersect_key($row, array_flip($allowed));
            $created[] = Student::create($rowPayload);
        }

        // Persist timetables: for each created student (primary + siblings)
        // Map created students 0..n-1 to timetableInput indexes
        
        // Day string to number mapping (matches admission form)
        $dayToNumber = [
            'Mon' => 0, 'Tue' => 1, 'Wed' => 2, 'Thu' => 3,
            'Fri' => 4, 'Sat' => 5, 'Sun' => 6
        ];
        
        // Time slots for each day (matches admission form exactly)
        $dayTimeSlots = [
            'Mon' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Tue' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Wed' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Thu' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Fri' => [
                0 => ['start'=>'09:00','end'=>'11:00'],
                1 => ['start'=>'11:15','end'=>'13:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Sat' => [
                0 => ['start'=>'09:00','end'=>'11:00'],
                1 => ['start'=>'11:15','end'=>'13:15'],
                2 => ['start'=>'14:15','end'=>'16:15'],
                3 => ['start'=>'16:30','end'=>'18:30'],
            ],
            'Sun' => [
                0 => ['start'=>'09:00','end'=>'11:00'],
                1 => ['start'=>'11:15','end'=>'13:15'],
                2 => ['start'=>'14:15','end'=>'16:15'],
                3 => ['start'=>'16:30','end'=>'18:30'],
            ],
        ];
        
        foreach ($created as $idx => $stu) {
            $studentRef = $stu->reference;
            $studentTimetable = $timetableInput[$idx] ?? [];
            $period = $admission['period'] ?? 'weekly'; // Get period from admission form
            
            // studentTimetable: dayString=>[slotIndex=>subject]
            foreach ($studentTimetable as $dayString => $slots) {
                // Convert day string to number
                $dayNumber = $dayToNumber[$dayString] ?? null;
                if ($dayNumber === null) continue;
                
                foreach ($slots as $slotIndex => $subject) {
                    if (!$subject) continue;
                    
                    // Get time slot for this specific day and slot index
                    $timeSlot = $dayTimeSlots[$dayString][$slotIndex] ?? null;
                    if (!$timeSlot) continue;
                    
                    // For monthly period, create 4 weeks of entries
                    if ($period === 'monthly') {
                        for ($week = 1; $week <= 4; $week++) {
                            Timetable::create([
                                'student_id' => $stu->id, // Link to specific student
                                'student_reference' => $studentRef,
                                'day_of_week' => $dayNumber,
                                'start_time' => $timeSlot['start'],
                                'end_time' => $timeSlot['end'],
                                'subject' => $subject,
                                'teacher_name' => null,
                                'room' => null,
                                'period' => $period,
                                'week_number' => $week,
                            ]);
                        }
                    } else {
                        // Weekly period - single entry
                        Timetable::create([
                            'student_id' => $stu->id, // Link to specific student
                            'student_reference' => $studentRef,
                            'day_of_week' => $dayNumber,
                            'start_time' => $timeSlot['start'],
                            'end_time' => $timeSlot['end'],
                            'subject' => $subject,
                            'teacher_name' => null,
                            'room' => null,
                            'period' => $period,
                            'week_number' => null,
                        ]);
                    }
                }
            }
        }

        // Clear session & finish
        $request->session()->forget('admission');

        // Clear session & redirect to print page
        $request->session()->forget('admission');

        return redirect()->route('student.timetable.print', ['reference' => $reference])
            ->with('status', 'Admission saved: '.count($created).' record(s) created. Ref '.$reference);
    }

    /**
     * Edit existing student(s) by reference - loads all siblings
     */
    public function edit($reference)
    {
        // Get all students with this reference (including siblings)
        $students = Student::where('reference', $reference)->get();
        
        if ($students->isEmpty()) {
            return redirect()->route('students.index')->with('error', 'No students found with reference: ' . $reference);
        }

        // Get timetable entries for this reference
        $timetableEntries = Timetable::where('student_reference', $reference)
            ->orderBy('student_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Build admission session data from existing students
        $admissionData = [
            'reference' => $reference,
            'period' => $students->first()->period ?? 'weekly',
            'count' => $students->count(),
            'students' => []
        ];

        // Populate student data
        foreach ($students as $index => $student) {
            $admissionData['students'][$index] = [
                'id' => $student->id,  // Store ID for update
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'dob' => $student->dob,
                'gender' => $student->gender,
                'guardian_name' => $student->guardian_name,
                'guardian_phone' => $student->guardian_phone,
                'guardian_email' => $student->guardian_email,
                'city' => $student->city,
                'post_code' => $student->post_code,
                'notes' => $student->notes,
                'enroll_date' => $student->enroll_date,
                'start_date' => $student->start_date,
                'deposit' => $student->deposit,
                'fee_amount' => $student->fee_amount,
            ];
        }

        // Build timetable data structure
        $timetableData = [];
        foreach ($timetableEntries as $entry) {
            // Find which student index this entry belongs to
            $studentIndex = null;
            foreach ($students as $idx => $student) {
                if ($entry->student_id == $student->id) {
                    $studentIndex = $idx;
                    break;
                }
            }
            
            if ($studentIndex !== null) {
                // Convert day number to string
                $dayMap = [0 => 'Mon', 1 => 'Tue', 2 => 'Wed', 3 => 'Thu', 4 => 'Fri', 5 => 'Sat', 6 => 'Sun'];
                $dayString = $dayMap[$entry->day_of_week] ?? null;
                
                if ($dayString) {
                    // Determine slot index from start time
                    $slotIndex = $this->getSlotIndexFromTime($entry->start_time, $dayString);
                    
                    if ($slotIndex !== null) {
                        $timetableData[$studentIndex][$dayString][$slotIndex] = $entry->subject;
                    }
                }
            }
        }

        $admissionData['timetable'] = $timetableData;
        $admissionData['is_edit'] = true;  // Flag to indicate edit mode

        // Store in session
        session(['admission' => $admissionData]);

        return view('students.edit', [
            'students' => $students,
            'reference' => $reference,
            'admission' => $admissionData
        ]);
    }

    /**
     * Handle next step during edit (similar to next() but for edit mode)
     */
    public function nextEdit(Request $request, $reference)
    {
        // Validate the form data (same validation as next())
        $validated = $request->validate([
            'period' => 'nullable|string|in:weekly,monthly',
            'count' => 'required|integer|min:1|max:10',
            'students' => 'required|array',
            'students.*.first_name' => 'required|string|max:255',
            'students.*.last_name' => 'required|string|max:255',
            'students.*.dob' => 'nullable|date',
            'students.*.gender' => 'nullable|string',
            'students.*.guardian_name' => 'nullable|string|max:255',
            'students.*.guardian_phone' => 'nullable|string|max:50',
            'students.*.guardian_email' => 'nullable|email|max:255',
            'students.*.city' => 'nullable|string|max:255',
            'students.*.post_code' => 'nullable|string|max:20',
            'students.*.notes' => 'nullable|string',
            'students.*.enroll_date' => 'nullable|date',
            'students.*.start_date' => 'nullable|date',
            'students.*.deposit' => 'nullable|numeric|min:0',
            'students.*.fee_amount' => 'nullable|numeric|min:0',
        ]);

        // Get existing admission data from session
        $admission = $request->session()->get('admission', []);
        
        // Keep the student IDs for update
        $existingStudentIds = [];
        foreach ($admission['students'] ?? [] as $index => $student) {
            if (isset($student['id'])) {
                $existingStudentIds[$index] = $student['id'];
            }
        }

        // Merge new data with existing
        $admission = array_merge($admission, $validated);
        $admission['reference'] = $reference;  // Preserve reference
        $admission['is_edit'] = true;  // Preserve edit flag
        
        // Restore student IDs
        foreach ($existingStudentIds as $index => $id) {
            $admission['students'][$index]['id'] = $id;
        }

        $request->session()->put('admission', $admission);

        return redirect()->route('students.confirm');
    }

    /**
     * Update existing student(s) and timetable
     */
    public function update(Request $request, $reference)
    {
        $admission = $request->session()->get('admission');
        if (!$admission || empty($admission['students'])) {
            return redirect()->route('students.index')->with('error', 'No admission data found');
        }

        // Validate that reference matches
        if ($admission['reference'] !== $reference) {
            return redirect()->route('students.index')->with('error', 'Reference mismatch');
        }

        $timetableInput = $admission['timetable'] ?? [];
        $period = $admission['period'] ?? 'weekly';

        // Update existing students
        foreach ($admission['students'] as $idx => $studentData) {
            if (isset($studentData['id'])) {
                // Update existing student
                $student = Student::find($studentData['id']);
                if ($student) {
                    $student->update([
                        'first_name' => $studentData['first_name'] ?? null,
                        'last_name' => $studentData['last_name'] ?? null,
                        'dob' => $studentData['dob'] ?? null,
                        'gender' => $studentData['gender'] ?? null,
                        'guardian_name' => $studentData['guardian_name'] ?? null,
                        'guardian_phone' => $studentData['guardian_phone'] ?? null,
                        'guardian_email' => $studentData['guardian_email'] ?? null,
                        'city' => $studentData['city'] ?? null,
                        'post_code' => $studentData['post_code'] ?? null,
                        'notes' => $studentData['notes'] ?? null,
                        'enroll_date' => $studentData['enroll_date'] ?? null,
                        'start_date' => $studentData['start_date'] ?? null,
                        'deposit' => $studentData['deposit'] ?? 0,
                        'fee_amount' => $studentData['fee_amount'] ?? 0,
                        'period' => $period,
                    ]);
                }
            }
        }

        // Delete all existing timetable entries for this reference
        Timetable::where('student_reference', $reference)->delete();

        // Re-create timetable entries (same logic as store())
        $dayToNumber = [
            'Mon' => 0, 'Tue' => 1, 'Wed' => 2, 'Thu' => 3,
            'Fri' => 4, 'Sat' => 5, 'Sun' => 6
        ];

        $dayTimeSlots = [
            'Mon' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Tue' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Wed' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Thu' => [
                0 => ['start'=>'12:00','end'=>'14:00'],
                1 => ['start'=>'14:15','end'=>'16:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Fri' => [
                0 => ['start'=>'09:00','end'=>'11:00'],
                1 => ['start'=>'11:15','end'=>'13:15'],
                2 => ['start'=>'16:45','end'=>'18:45'],
                3 => ['start'=>'19:00','end'=>'21:00'],
            ],
            'Sat' => [
                0 => ['start'=>'09:00','end'=>'11:00'],
                1 => ['start'=>'11:15','end'=>'13:15'],
                2 => ['start'=>'14:15','end'=>'16:15'],
                3 => ['start'=>'16:30','end'=>'18:30'],
            ],
            'Sun' => [
                0 => ['start'=>'09:00','end'=>'11:00'],
                1 => ['start'=>'11:15','end'=>'13:15'],
                2 => ['start'=>'14:15','end'=>'16:15'],
                3 => ['start'=>'16:30','end'=>'18:30'],
            ],
        ];

        // Get updated students from database
        $updatedStudents = Student::where('reference', $reference)->orderBy('id')->get();

        foreach ($updatedStudents as $idx => $stu) {
            $studentTimetable = $timetableInput[$idx] ?? [];

            foreach ($studentTimetable as $dayString => $slots) {
                $dayNumber = $dayToNumber[$dayString] ?? null;
                if ($dayNumber === null) continue;

                foreach ($slots as $slotIndex => $subject) {
                    if (!$subject) continue;

                    $timeSlot = $dayTimeSlots[$dayString][$slotIndex] ?? null;
                    if (!$timeSlot) continue;

                    if ($period === 'monthly') {
                        for ($week = 1; $week <= 4; $week++) {
                            Timetable::create([
                                'student_id' => $stu->id,
                                'student_reference' => $reference,
                                'day_of_week' => $dayNumber,
                                'start_time' => $timeSlot['start'],
                                'end_time' => $timeSlot['end'],
                                'subject' => $subject,
                                'teacher_name' => null,
                                'room' => null,
                                'period' => $period,
                                'week_number' => $week,
                            ]);
                        }
                    } else {
                        Timetable::create([
                            'student_id' => $stu->id,
                            'student_reference' => $reference,
                            'day_of_week' => $dayNumber,
                            'start_time' => $timeSlot['start'],
                            'end_time' => $timeSlot['end'],
                            'subject' => $subject,
                            'teacher_name' => null,
                            'room' => null,
                            'period' => $period,
                            'week_number' => null,
                        ]);
                    }
                }
            }
        }

        // Clear session
        $request->session()->forget('admission');

        return redirect()->route('students.index')
            ->with('status', 'Student(s) updated successfully. Reference: ' . $reference);
    }

    /**
     * Helper: Get slot index from time and day
     */
    private function getSlotIndexFromTime($startTime, $dayString)
    {
        $timeMap = [
            'Mon' => ['12:00:00' => 0, '14:15:00' => 1, '16:45:00' => 2, '19:00:00' => 3],
            'Tue' => ['12:00:00' => 0, '14:15:00' => 1, '16:45:00' => 2, '19:00:00' => 3],
            'Wed' => ['12:00:00' => 0, '14:15:00' => 1, '16:45:00' => 2, '19:00:00' => 3],
            'Thu' => ['12:00:00' => 0, '14:15:00' => 1, '16:45:00' => 2, '19:00:00' => 3],
            'Fri' => ['09:00:00' => 0, '11:15:00' => 1, '16:45:00' => 2, '19:00:00' => 3],
            'Sat' => ['09:00:00' => 0, '11:15:00' => 1, '14:15:00' => 2, '16:30:00' => 3],
            'Sun' => ['09:00:00' => 0, '11:15:00' => 1, '14:15:00' => 2, '16:30:00' => 3],
        ];

        return $timeMap[$dayString][$startTime] ?? null;
    }
}
