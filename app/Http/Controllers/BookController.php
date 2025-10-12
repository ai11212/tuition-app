<?php
namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller {
    public function index(){ $items = Book::latest()->paginate(20); return view('books.index', compact('items')); }
    public function create(){ return view('books.create'); }
    public function store(Request $r){
        $data=$r->validate([
            'reference'=>'required',
            'subject'=>'required',
            'title'=>'required',
            'price'=>'required|numeric',
            'student_reference'=>'nullable|string'
        ]);
        Book::create($data); return redirect()->route('books.index')->with('ok','Book added');
    }
    public function edit(Book $book){ return view('books.edit', compact('book')); }
    public function update(Request $r, Book $book){
        $data=$r->validate([
            'reference'=>'required',
            'subject'=>'required',
            'title'=>'required',
            'price'=>'required|numeric',
            'student_reference'=>'nullable|string'
        ]);
        $book->update($data); return back()->with('ok','Updated');
    }
    public function destroy(Book $book){ $book->delete(); return back()->with('ok','Deleted'); }
}
