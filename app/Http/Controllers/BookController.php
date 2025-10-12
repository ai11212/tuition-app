<?php
namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BookController extends Controller {
    public function index(){ $items = Book::latest()->paginate(20); return view('books.index', compact('items')); }
    public function create(){ return view('books.create'); }
    public function store(Request $r){
        // Check if student_reference column exists
        $hasStudentReferenceColumn = Schema::hasColumn('books', 'student_reference');
        
        // Base validation rules
        $rules = [
            'reference'=>'required',
            'subject'=>'required',
            'title'=>'required',
            'price'=>'required|numeric'
        ];
        
        // Add student_reference validation only if column exists
        if ($hasStudentReferenceColumn) {
            $rules['student_reference'] = 'nullable|string';
        }
        
        $data = $r->validate($rules);
        
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
        
        // Base validation rules
        $rules = [
            'reference'=>'required',
            'subject'=>'required',
            'title'=>'required',
            'price'=>'required|numeric'
        ];
        
        // Add student_reference validation only if column exists
        if ($hasStudentReferenceColumn) {
            $rules['student_reference'] = 'nullable|string';
        }
        
        $data = $r->validate($rules);
        
        // Remove student_reference from data if column doesn't exist
        if (!$hasStudentReferenceColumn && isset($data['student_reference'])) {
            unset($data['student_reference']);
        }
        
        $book->update($data); 
        return back()->with('ok','Updated');
    }
    public function destroy(Book $book){ $book->delete(); return back()->with('ok','Deleted'); }
}
