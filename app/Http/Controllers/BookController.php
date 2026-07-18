<?php
namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BookController extends Controller {
    public function index(){ 
        // Join with students table to get student names
        $query = Book::query();
        
        if (Schema::hasColumn('books', 'student_reference')) {
            // Note: student_reference now stores student ID
            $query->leftJoin('students', 'books.student_reference', '=', 'students.id')
                  ->select('books.*', 'students.first_name', 'students.last_name', 'students.reference as student_ref');
        }
        
        $items = $query->orderByDesc('books.issue_date')->orderByDesc('books.created_at')->paginate(20);
        return view('books.index', compact('items')); 
    }
    
    public function create(Request $request)
    { 
        // Build query with search and filter - join with students to get names
        $query = Book::query();
        
        // Join with students table to get student names
        // Note: student_reference now stores student ID
        if (Schema::hasColumn('books', 'student_reference')) {
            $query->leftJoin('students', 'books.student_reference', '=', 'students.id')
                  ->select('books.*', 'students.first_name', 'students.last_name', 'students.reference as student_ref');
        }
        
        // Search by reference, subject, or title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('books.reference', 'like', "%{$search}%")
                  ->orWhere('books.subject', 'like', "%{$search}%")
                  ->orWhere('books.title', 'like', "%{$search}%");
            });
        }
        
        // Filter by student reference
        if ($request->filled('student_ref') && Schema::hasColumn('books', 'student_reference')) {
            $studentRef = $request->student_ref;
            $query->where(function($q) use ($studentRef) {
                $q->where('students.reference', 'like', "%{$studentRef}%")
                  ->orWhereNull('books.student_reference');
            });
        }
        
        $books = $query->orderByDesc('books.issue_date')->orderByDesc('books.created_at')->paginate(15)->withQueryString();

        // Book Library catalog for the picker (auto-fills subject/title/price)
        $libraryBooks = Schema::hasTable('library_books')
            ? \App\Models\LibraryBook::orderBy('subject')->orderBy('title')->get()
            : collect();

        return view('books.create', compact('books', 'libraryBooks'));
    }
    
    // AJAX endpoint to get siblings by reference
    public function getSiblings(Request $request)
    {
        $reference = $request->get('reference');
        
        if (!$reference) {
            return response()->json(['success' => false, 'message' => 'Reference required']);
        }
        
        // Find all students with this reference
        $siblings = Student::where('reference', $reference)
            ->select('id', 'reference', 'first_name', 'last_name')
            ->get();
        
        return response()->json([
            'success' => true,
            'siblings' => $siblings,
            'count' => $siblings->count()
        ]);
    }
    
    public function store(Request $r){
        // Check if student_reference column exists
        $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');

        // All fields required; a book must be assigned to at least one student
        $rules = [
            'subject'    => 'required|string|max:255',
            'title'      => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'issue_date' => 'required|date|before_or_equal:today',
            'students'   => 'required|array|min:1',
            'students.*' => 'string',
        ];

        $data = $r->validate($rules);

        // Create a book record for each selected student, skipping duplicates
        $duplicates = [];
        $bookCount = 0;
        foreach ($data['students'] as $uniqueIdentifier) {
            // Parse unique identifier: studentId|reference|firstName_lastName
            $parts = explode('|', $uniqueIdentifier);
            $studentId = $parts[0] ?? null;

            if (!$studentId) continue;

            $student = Student::find($studentId);
            $studentName = $student ? trim($student->first_name . ' ' . $student->last_name) : "ID {$studentId}";

            // No duplicate entries: same student + same subject + same title
            if ($hasStudentReferenceColumn) {
                $alreadyIssued = Book::where('student_reference', $studentId)
                    ->whereRaw('LOWER(subject) = ?', [strtolower($data['subject'])])
                    ->whereRaw('LOWER(title) = ?', [strtolower($data['title'])])
                    ->exists();
                if ($alreadyIssued) {
                    $duplicates[] = $studentName . ' (already has "' . $data['title'] . '")';
                    continue;
                }
            }

            $bookData = [
                'reference'  => 'BK-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'subject'    => $data['subject'],
                'title'      => $data['title'],
                'price'      => $data['price'],
                'issue_date' => $data['issue_date'],
            ];
            if ($hasStudentReferenceColumn) {
                $bookData['student_reference'] = $studentId; // Store student ID
            }

            Book::create($bookData);
            $bookCount++;
        }

        if (count($duplicates) > 0) {
            return redirect()->route('books.create')
                ->with('warning', "Book issued to {$bookCount} student(s). Skipped duplicates: " . implode(', ', $duplicates));
        }

        return redirect()->route('books.create')->with('ok', "Book issued to {$bookCount} student(s)");
    }
    public function edit(Book $book){ return view('books.edit', compact('book')); }
    public function update(Request $r, Book $book){
        // Check if student_reference column exists
        $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');
        
        // Base validation rules (reference is auto-generated, not editable)
        $rules = [
            'subject'    => 'required|string|max:255',
            'title'      => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'issue_date' => 'required|date|before_or_equal:today',
        ];
        
        // Add student_reference validation only if column exists
        if ($hasStudentReferenceColumn) {
            $rules['student_reference'] = 'nullable|string';
        }
        
        $data = $r->validate($rules);
        
        // Keep existing reference (don't change it)
        // Reference is auto-generated and should not be editable
        
        // Remove student_reference from data if column doesn't exist
        if (!$hasStudentReferenceColumn && isset($data['student_reference'])) {
            unset($data['student_reference']);
        }
        
        $book->update($data); 
        return back()->with('ok','Updated');
    }
    public function destroy(Book $book){ $book->delete(); return back()->with('ok','Deleted'); }
}
