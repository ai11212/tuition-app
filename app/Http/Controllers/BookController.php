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
        
        $items = $query->latest('books.created_at')->paginate(20); 
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
                $q->where('books.student_reference', 'like', "%{$studentRef}%")
                  ->orWhereNull('books.student_reference');
            });
        }
        
        $books = $query->latest('books.created_at')->paginate(15)->withQueryString();
        
        return view('books.create', compact('books')); 
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
        
        // Base validation rules
        $rules = [
            'subject'=>'required',
            'title'=>'required',
            'price'=>'required|numeric'
        ];
        
        // Add student_reference validation only if column exists
        if ($hasStudentReferenceColumn) {
            $rules['students'] = 'nullable|array'; // Accept array of student references
            $rules['students.*'] = 'string'; // Each element should be a string
        }
        
        $data = $r->validate($rules);
        
        // Check if multiple students were selected
        $students = $r->input('students', []);
        
        if (!empty($students) && $hasStudentReferenceColumn) {
            // Create a book record for each selected student
            $bookCount = 0;
            foreach ($students as $uniqueIdentifier) {
                // Parse unique identifier: studentId|reference|firstName_lastName
                $parts = explode('|', $uniqueIdentifier);
                $studentId = $parts[0] ?? null;
                
                if ($studentId) {
                    $bookData = [
                        'reference' => 'BK-' . strtoupper(\Illuminate\Support\Str::random(6)),
                        'subject' => $data['subject'],
                        'title' => $data['title'],
                        'price' => $data['price'],
                        'student_reference' => $studentId  // Store student ID
                    ];
                    
                    Book::create($bookData);
                    $bookCount++;
                }
            }
            
            return redirect()->route('books.index')->with('ok', "Book added for {$bookCount} student(s)");
        } else {
            // No students selected - create book without assignment (original behavior)
            $data['reference'] = 'BK-' . strtoupper(\Illuminate\Support\Str::random(6));
            
            // Remove students array from data if column doesn't exist
            if (!$hasStudentReferenceColumn && isset($data['students'])) {
                unset($data['students']);
            }
            
            Book::create($data); 
            return redirect()->route('books.index')->with('ok','Book added');
        }
    }
    public function edit(Book $book){ return view('books.edit', compact('book')); }
    public function update(Request $r, Book $book){
        // Check if student_reference column exists
        $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');
        
        // Base validation rules (reference is auto-generated, not editable)
        $rules = [
            'subject'=>'required',
            'title'=>'required',
            'price'=>'required|numeric'
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
