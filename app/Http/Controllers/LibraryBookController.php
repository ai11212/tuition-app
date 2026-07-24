<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LibraryBook;

class LibraryBookController extends Controller
{
    /** Book Library: catalog list + add form */
    public function index()
    {
        $books = LibraryBook::orderBy('subject')->orderBy('title')->paginate(25);
        return view('library.index', compact('books'));
    }

    /** Add a book to the library catalog */
    public function store(Request $r)
    {
        $data = $r->validate([
            'subject' => 'required|string|max:100',
            'title'   => 'required|string|max:190',
            'price'   => 'required|numeric|min:0',
        ]);

        LibraryBook::create($data);

        return redirect()->route('library.index')->with('success', 'Book added to library!');
    }

    /** Edit a catalog book in place — same record/ID, no new row */
    public function update(Request $r, $id)
    {
        $data = $r->validate([
            'subject' => 'required|string|max:100',
            'title'   => 'required|string|max:190',
            'price'   => 'required|numeric|min:0',
        ]);

        LibraryBook::findOrFail($id)->update($data);

        return redirect()->route('library.index')->with('success', 'Book updated successfully.');
    }

    /** Remove a book from the library catalog */
    public function destroy($id)
    {
        try {
            LibraryBook::findOrFail($id)->delete();
            return redirect()->route('library.index')->with('success', 'Book removed from library.');
        } catch (\Exception $e) {
            return redirect()->route('library.index')->with('error', 'Failed to remove book.');
        }
    }
}
