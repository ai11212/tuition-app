<?php
namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BookController extends Controller {
    public function index(){ $items = Book::latest()->paginate(20); return view('books.index', compact('items')); }
    
    public function create(Request $request)
    { 
        // Build query with search and filter
        $query = Book::query();
        
        // Search by reference, subject, or title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }
        
        // Filter by student reference
        if ($request->filled('student_ref') && Schema::hasColumn('books', 'student_reference')) {
            $studentRef = $request->student_ref;
            $query->where(function($q) use ($studentRef) {
                $q->where('student_reference', 'like', "%{$studentRef}%")
                  ->orWhereNull('student_reference');
            });
        }
        
        $books = $query->latest()->paginate(15)->withQueryString();
        
        return view('books.create', compact('books')); 
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
            $rules['student_reference'] = 'nullable|string';
        }
        
        $data = $r->validate($rules);
        
        // Auto-generate reference
        $data['reference'] = 'BK-' . strtoupper(\Illuminate\Support\Str::random(6));
        
        // Remove student_reference from data if column doesn't exist
        if (!$hasStudentReferenceColumn && isset($data['student_reference'])) {
            unset($data['student_reference']);
        }
        
        Book::create($data); 
        return redirect()->route('books.index')->with('ok','Book added');
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
