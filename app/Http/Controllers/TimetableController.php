<?php
namespace App\Http\Controllers;
use App\Models\Timetable;
use App\Models\Student;
use Illuminate\Http\Request;

class TimetableController extends Controller {
    public function printForm(){ return view('ref.print_timetable'); }
    
    public function print(Request $r){
        $r->validate(['reference'=>'required']);
        $rows = Timetable::where('student_reference',$r->reference)->orderBy('day_of_week')->orderBy('start_time')->get();
        return view('ref.print_timetable_result', ['reference'=>$r->reference,'rows'=>$rows]);
    }

    public function printStudentTimetable(Request $r, $reference)
    {
        // Get all students with this reference (main student + siblings)
        $students = Student::where('reference', $reference)->get();

        if ($students->isEmpty()) {
            return redirect()->route('students.create')->with('error', 'No students found with reference: ' . $reference);
        }

        // Get timetable entries for this reference
        $timetableEntries = Timetable::where('student_reference', $reference)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Full family list for the sibling switcher (before any narrowing)
        $allSiblings = $students;
        $selectedStudentId = null;

        // ?student=<id> — print ONLY that sibling's timetable. Requires entries
        // carrying student_id (new data); old data can't be attributed per student.
        $hasStudentIdData = $timetableEntries->whereNotNull('student_id')->count() > 0;
        if ($r->filled('student') && $hasStudentIdData
            && ($sel = $students->firstWhere('id', (int) $r->input('student')))) {
            $selectedStudentId = $sel->id;
            $students = collect([$sel]);
            $timetableEntries = $timetableEntries->where('student_id', $sel->id)->values();
        }

        // Determine the period type from first entry or student
        $period = 'weekly'; // default
        $firstStudent = $students->first();
        if ($firstStudent && $firstStudent->period) {
            $period = $firstStudent->period;
        } elseif ($timetableEntries->isNotEmpty() && $timetableEntries->first()->period) {
            $period = $timetableEntries->first()->period;
        }

        // Check if we have siblings (multiple students with same reference)
        $hasSiblings = $students->count() > 1;
        
        // Check if timetable entries have student_id (new data vs old data)
        $hasStudentIds = $timetableEntries->where('student_id', '!=', null)->count() > 0;
        
        // Build timetable grids per student if we have siblings AND student_id data
        if ($hasSiblings && $hasStudentIds) {
            // Group timetables by student
            $studentTimetables = [];
            foreach ($students as $student) {
                $studentEntries = $timetableEntries->where('student_id', $student->id);
                $studentTimetables[] = [
                    'student' => $student,
                    'entries' => $studentEntries,
                    'grid' => $this->buildTimetableGrid($studentEntries, $period),
                    'hasEntries' => $studentEntries->count() > 0
                ];
            }
            
            return view('students.timetable_print', [
                'reference' => $reference,
                'students' => $students,
                'studentTimetables' => $studentTimetables, // Separate timetables per student
                'hasSiblings' => true,
                'period' => $period,
                'allSiblings' => $allSiblings,
                'selectedStudentId' => $selectedStudentId
            ]);
        } else {
            // Fall back to combined timetable (for old data or single student)
            $timetableGrid = $this->buildTimetableGrid($timetableEntries, $period);
            $hasEntries = $timetableEntries->count() > 0;

            return view('students.timetable_print', [
                'reference' => $reference,
                'students' => $students,
                'timetableEntries' => $timetableEntries,
                'timetableGrid' => $timetableGrid,
                'hasEntries' => $hasEntries,
                'hasSiblings' => false,
                'period' => $period,
                'allSiblings' => $allSiblings,
                'selectedStudentId' => $selectedStudentId
            ]);
        }
    }

    private function buildTimetableGrid($timetableEntries, $period = 'weekly')
    {
        $dayNames = [
            0 => 'Monday', 1 => 'Tuesday', 2 => 'Wednesday', 3 => 'Thursday',
            4 => 'Friday', 5 => 'Saturday', 6 => 'Sunday'
        ];

        // All days to show (always show full week)
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Time slots for each day (matches admission form)
        $daySpecificSlots = [
            'Monday'    => ['12–2pm', '2:15–4:15pm', '4:45–6:45pm', '7–9pm'],
            'Tuesday'   => ['12–2pm', '2:15–4:15pm', '4:45–6:45pm', '7–9pm'],
            'Wednesday' => ['12–2pm', '2:15–4:15pm', '4:45–6:45pm', '7–9pm'],
            'Thursday'  => ['12–2pm', '2:15–4:15pm', '4:45–6:45pm', '7–9pm'],
            'Friday'    => ['9–11am', '11:15–1:15pm', '4:45–6:45pm', '7–9pm'],
            'Saturday'  => ['9–11am', '11:15–1:15pm', '2:15–4:15pm', '4:30–6:30pm'],
            'Sunday'    => ['9–11am', '11:15–1:15pm', '2:15–4:15pm', '4:30–6:30pm'],
        ];

        $timeSlots = [
            '12:00:00' => ['label' => '12–2pm', 'end' => '14:00:00'],
            '14:15:00' => ['label' => '2:15–4:15pm', 'end' => '16:15:00'],
            '16:45:00' => ['label' => '4:45–6:45pm', 'end' => '18:45:00'],
            '19:00:00' => ['label' => '7–9pm', 'end' => '21:00:00'],
            '09:00:00' => ['label' => '9–11am', 'end' => '11:00:00'],
            '11:15:00' => ['label' => '11:15–1:15pm', 'end' => '13:15:00'],
            '16:30:00' => ['label' => '4:30–6:30pm', 'end' => '18:30:00']
        ];

        // Initialize grid with all days
        $grid = [];
        foreach ($allDays as $day) {
            $grid[$day] = [];
        }

        if ($period === 'monthly') {
            // For monthly view, organize by weeks
            $weeklyGrids = [];
            
            foreach ($timetableEntries as $entry) {
                $dayNum = (int) $entry->day_of_week;
                $dayName = $dayNames[$dayNum] ?? "Day {$dayNum}";
                $weekNum = $entry->week_number ?? 1;
                $startTime = $entry->start_time;
                
                // Find matching time slot
                $timeLabel = null;
                foreach ($timeSlots as $slotStart => $slotInfo) {
                    if ($startTime === $slotStart || 
                        (strtotime($entry->start_time) >= strtotime($slotStart) && 
                         strtotime($entry->end_time) <= strtotime($slotInfo['end']))) {
                        $timeLabel = $slotInfo['label'];
                        break;
                    }
                }
                
                if (!$timeLabel) {
                    $timeLabel = substr($entry->start_time, 0, 5) . '–' . substr($entry->end_time, 0, 5);
                }

                $weeklyGrids["Week {$weekNum}"][$dayName][$timeLabel] = [
                    'subject' => $entry->subject,
                    'teacher' => $entry->teacher_name,
                    'room' => $entry->room
                ];

                $filledDays[$dayName] = true;
                $filledTimeSlots[$timeLabel] = true;
            }
            
            return [
                'grid' => $weeklyGrids,
                'filledDays' => array_keys($filledDays),
                'filledTimeSlots' => array_keys($filledTimeSlots),
                'isMonthly' => true
            ];
        } else {
            // Weekly view - populate grid with entries
            foreach ($timetableEntries as $entry) {
                $dayNum = (int) $entry->day_of_week;
                $dayName = $dayNames[$dayNum] ?? "Day {$dayNum}";
                $startTime = $entry->start_time;
                
                // Find matching time slot
                $timeLabel = null;
                foreach ($timeSlots as $slotStart => $slotInfo) {
                    if ($startTime === $slotStart || 
                        (strtotime($entry->start_time) >= strtotime($slotStart) && 
                         strtotime($entry->end_time) <= strtotime($slotInfo['end']))) {
                        $timeLabel = $slotInfo['label'];
                        break;
                    }
                }
                
                if (!$timeLabel) {
                    $timeLabel = substr($entry->start_time, 0, 5) . '–' . substr($entry->end_time, 0, 5);
                }

                $grid[$dayName][$timeLabel] = [
                    'subject' => $entry->subject,
                    'teacher' => $entry->teacher_name,
                    'room' => $entry->room
                ];
            }

            return [
                'grid' => $grid,
                'allDays' => $allDays, // Return all days (not just filled)
                'daySpecificSlots' => $daySpecificSlots, // Return day-specific time slots
                'isMonthly' => false
            ];
        }
    }
}
