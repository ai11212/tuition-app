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
        
        // Fetch all students
        $allStudents = Student::query()
            ->when($reference, function($q) use ($reference) {
                return $q->where('reference', 'like', $reference . '%');
            })
            ->orderBy('reference', 'desc')
            ->orderBy('created_at', 'asc')  // First created = primary student
            ->get();
        
        // Group students by reference
        $groupedStudents = $allStudents->groupBy('reference')->map(function($group) {
            // First student in the group is the primary (created first)
            // Others are siblings
            return [
                'primary' => $group->first(),
                'siblings' => $group->slice(1),  // All except first
                'reference' => $group->first()->reference,
            ];
        })->values();
        
        // Paginate the grouped results
        $perPage = 20;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $currentItems = $groupedStudents->slice(($currentPage - 1) * $perPage, $perPage);
        
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $groupedStudents->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );
            
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
            'year'              => 'nullable|integer|min:1|max:16',
            'city'              => 'nullable|string|max:120',
            'enroll_date'       => 'nullable|date',
            'start_date'        => 'nullable|date',
            'deposit'           => 'nullable|numeric',
            'deposit_paid'      => 'nullable|in:0,1',
            'payment'           => 'nullable|numeric',
            'period'            => 'nullable|string|max:32',

            // siblings[] array (same minimal fields)
            'siblings'                      => 'array',
            'siblings.*.first_name'         => 'nullable|string|max:100',
            'siblings.*.last_name'          => 'nullable|string|max:100',
            'siblings.*.gender'             => 'nullable|in:male,female,other',
            'siblings.*.dob'                => 'nullable|date',
            'siblings.*.year'               => 'nullable|integer|min:1|max:16',
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
            // Auto-generate reference starting from A1001
            // Find the last reference matching pattern A#### and increment
            $lastReference = \App\Models\Student::where('reference', 'REGEXP', '^A[0-9]+$')
                ->orderByRaw('CAST(SUBSTRING(reference, 2) AS UNSIGNED) DESC')
                ->value('reference');
            
            if ($lastReference && preg_match('/^A(\d+)$/', $lastReference, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1001; // Start from 1001 if no numeric references found
            }
            $reference = 'A' . $nextNumber;
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
            'year'        => $admission['year'] ?? null,
            'guardian_city' => $admission['guardian_city'] ?? null,
            'city'        => $admission['guardian_city'] ?? null, // Map guardian_city to city as well
            'enroll_date' => $admission['enroll_date'] ?? null,
            'start_date'  => $admission['start_date'] ?? null,
            'deposit'     => $admission['deposit'] ?? null,
            'deposit_paid' => $admission['deposit_paid'] ?? 0,
            'payment'     => $admission['payment'] ?? null,
            'payment_plan' => $admission['payment'] ?? null, // Store original payment plan (read-only)
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
                'year'       => $sib['year'] ?? null,
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
                'guardian_relation' => $student->guardian_relation,
                'guardian_phone' => $student->guardian_phone,
                'guardian_email' => $student->guardian_email,
                'guardian_address' => $student->guardian_address,
                'guardian_city' => $student->guardian_city,
                'guardian_notes' => $student->guardian_notes,
                'city' => $student->city,
                'post_code' => $student->post_code,
                'notes' => $student->notes,
                'enroll_date' => $student->enroll_date,
                'start_date' => $student->start_date,
                'deposit' => $student->deposit,
                'deposit_paid' => $student->deposit_paid,
                'fee_amount' => $student->fee_amount,
                'period' => $student->period,
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
            'students.*.year' => 'nullable|integer|min:1|max:16',
            'students.*.gender' => 'nullable|string',
            'students.*.guardian_name' => 'nullable|string|max:255',
            'students.*.guardian_relation' => 'nullable|string|max:255',
            'students.*.guardian_phone' => 'nullable|string|max:50',
            'students.*.guardian_email' => 'nullable|email|max:255',
            'students.*.guardian_address' => 'nullable|string|max:255',
            'students.*.guardian_city' => 'nullable|string|max:255',
            'students.*.guardian_notes' => 'nullable|string|max:255',
            'students.*.city' => 'nullable|string|max:255',
            'students.*.post_code' => 'nullable|string|max:20',
            'students.*.notes' => 'nullable|string',
            'students.*.enroll_date' => 'nullable|date',
            'students.*.start_date' => 'nullable|date',
            'students.*.deposit' => 'nullable|numeric|min:0',
            'students.*.deposit_paid' => 'nullable|in:0,1',
            'students.*.fee_amount' => 'nullable|numeric|min:0',
            'students.*.period' => 'nullable|string|in:weekly,monthly',
        ]);

        // Get existing students from database to preserve IDs
        $existingStudents = Student::where('reference', $reference)->orderBy('id')->get();
        
        // Get existing timetable for building timetable structure
        $timetableEntries = Timetable::where('student_reference', $reference)
            ->orderBy('student_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Build timetable data structure
        $timetableData = [];
        $dayMap = [0 => 'Mon', 1 => 'Tue', 2 => 'Wed', 3 => 'Thu', 4 => 'Fri', 5 => 'Sat', 6 => 'Sun'];
        
        foreach ($timetableEntries as $entry) {
            // Find which student index this entry belongs to
            $studentIndex = null;
            foreach ($existingStudents as $idx => $student) {
                if ($entry->student_id == $student->id) {
                    $studentIndex = $idx;
                    break;
                }
            }
            
            if ($studentIndex !== null) {
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

        // Merge validated data with student IDs from database
        foreach ($validated['students'] as $index => &$studentData) {
            if (isset($existingStudents[$index])) {
                $studentData['id'] = $existingStudents[$index]->id;
            }
        }
        
        $admission = $validated;
        $admission['reference'] = $reference;
        $admission['is_edit'] = true;
        $admission['timetable'] = $timetableData;

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

        // Get timetable from request input (the form submission), NOT from session
        $timetableInput = $request->input('timetable', []);
        $period = $admission['period'] ?? 'weekly';

        // Build guardian data from first student (all siblings share guardian info)
        $guardianData = [
            'guardian_name' => $admission['students'][0]['guardian_name'] ?? null,
            'guardian_relation' => $admission['students'][0]['guardian_relation'] ?? null,
            'guardian_phone' => $admission['students'][0]['guardian_phone'] ?? null,
            'guardian_email' => $admission['students'][0]['guardian_email'] ?? null,
            'guardian_address' => $admission['students'][0]['guardian_address'] ?? null,
            'guardian_city' => $admission['students'][0]['guardian_city'] ?? null,
            'guardian_notes' => $admission['students'][0]['guardian_notes'] ?? null,
            'post_code' => $admission['students'][0]['post_code'] ?? null,
        ];

        // Update existing students OR create new ones
        $processedStudents = [];
        foreach ($admission['students'] as $idx => $studentData) {
            if (isset($studentData['id'])) {
                // Update existing student
                $student = Student::find($studentData['id']);
                if ($student) {
                    $student->update([
                        'first_name' => $studentData['first_name'] ?? null,
                        'last_name' => $studentData['last_name'] ?? null,
                        'dob' => $studentData['dob'] ?? null,
                        'year' => $studentData['year'] ?? null,
                        'gender' => $studentData['gender'] ?? null,
                        'guardian_name' => $studentData['guardian_name'] ?? null,
                        'guardian_relation' => $studentData['guardian_relation'] ?? null,
                        'guardian_phone' => $studentData['guardian_phone'] ?? null,
                        'guardian_email' => $studentData['guardian_email'] ?? null,
                        'guardian_address' => $studentData['guardian_address'] ?? null,
                        'guardian_city' => $studentData['guardian_city'] ?? null,
                        'guardian_notes' => $studentData['guardian_notes'] ?? null,
                        'city' => $studentData['city'] ?? null,
                        'post_code' => $studentData['post_code'] ?? null,
                        'notes' => $studentData['notes'] ?? null,
                        'enroll_date' => $studentData['enroll_date'] ?? null,
                        'start_date' => $studentData['start_date'] ?? null,
                        'deposit' => $studentData['deposit'] ?? 0,
                        'deposit_paid' => $studentData['deposit_paid'] ?? 0,
                        'fee_amount' => $studentData['fee_amount'] ?? 0,
                        'period' => $studentData['period'] ?? $period,
                    ]);
                    $processedStudents[] = $student;
                }
            } else {
                // Create new sibling (no id means it's a new addition)
                $newStudentData = [
                    'reference' => $reference,
                    'first_name' => $studentData['first_name'] ?? null,
                    'last_name' => $studentData['last_name'] ?? null,
                    'gender' => $studentData['gender'] ?? null,
                    'dob' => $studentData['dob'] ?? null,
                    'year' => $studentData['year'] ?? null,
                    'period' => $studentData['period'] ?? $period,
                ] + $guardianData;

                // Filter to only columns that exist
                $allowed = Schema::hasTable('students') ? Schema::getColumnListing('students') : [];
                $payload = array_intersect_key($newStudentData, array_flip($allowed));
                $student = Student::create($payload);
                $processedStudents[] = $student;
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

        // Use the processed students (includes both updated and newly created)
        foreach ($processedStudents as $idx => $stu) {
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
     * Delete a student by reference (deletes all siblings with same reference)
     */
    public function destroy($reference)
    {
        try {
            // Get all students with this reference (including siblings)
            $students = Student::where('reference', $reference)->get();
            
            if ($students->isEmpty()) {
                return redirect()->route('students.index')
                    ->with('error', 'Student not found with reference: ' . $reference);
            }

            $count = $students->count();
            
            // Delete related timetable entries
            Timetable::where('student_reference', $reference)->delete();
            
            // Delete related invoices and payment transactions
            foreach ($students as $student) {
                // Get invoices for this student
                $invoices = \App\Models\Invoice::where('student_id', $student->id)->get();
                
                foreach ($invoices as $invoice) {
                    // Delete payment transactions for this invoice
                    \App\Models\PaymentTransaction::where('invoice_id', $invoice->id)->delete();
                }
                
                // Delete invoices
                \App\Models\Invoice::where('student_id', $student->id)->delete();
                
                // Delete books assigned to this student (if books table has student_reference)
                if (Schema::hasTable('books') && Schema::hasColumn('books', 'student_reference')) {
                    \App\Models\Book::where('student_reference', $reference)->delete();
                }
            }
            
            // Finally, delete all students with this reference
            Student::where('reference', $reference)->delete();
            
            return redirect()->route('students.index')
                ->with('status', "Successfully deleted {$count} student(s) with reference: {$reference}");
                
        } catch (\Exception $e) {
            \Log::error('Student deletion failed: ' . $e->getMessage());
            return redirect()->route('students.index')
                ->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
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

    /**
     * Update student payment amount only (for fee adjustments)
     */
    public function updatePayment(Request $request, $id)
    {
        $data = $request->validate([
            'payment' => 'required|numeric|min:0|max:99999.99',
        ]);
        
        $student = Student::findOrFail($id);
        $oldPayment = $student->payment;
        
        $student->update(['payment' => $data['payment']]);
        
        return redirect()->back()->with('success', 
            'Payment amount updated from £' . number_format($oldPayment, 2) . 
            ' to £' . number_format($data['payment'], 2));
    }

    /**
     * Update student new plan (reference only field)
     */
    public function updateNewPlan(Request $request, $id)
    {
        $data = $request->validate([
            'new_plan' => 'nullable|numeric|min:0|max:99999.99',
        ]);
        
        $student = Student::findOrFail($id);
        $student->update(['new_plan' => $data['new_plan']]);
        
        return redirect()->back()->with('status', 'Student New Plan saved successfully.');
    }
}
