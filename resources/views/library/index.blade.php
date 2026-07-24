@extends('layouts.app')

@section('title', 'Book Library')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📚 Book Library</h1>
        <p class="text-sm text-gray-500">Catalog of available books. Adding a book here does not issue or bill it to any student — use Issue Books for that.</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Add Book Form --}}
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4" id="library-form-heading">➕ Add New Book</h2>
        <form method="POST" action="{{ route('library.store') }}" id="library-book-form" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            {{-- JS injects a _method=PUT here while editing --}}
            <span id="form-mode-fields" class="hidden"></span>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" maxlength="100" placeholder="e.g. Maths" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('subject') border-red-500 @enderror">
                @error('subject')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Book Name <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="190" placeholder="e.g. KS3 Maths Workbook" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Book Price <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" placeholder="0.00" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @enderror">
                @error('price')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-3 flex items-center gap-3">
                <button type="submit" id="library-submit-btn" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    💾 Save Book
                </button>
                <button type="button" id="library-cancel-btn" onclick="cancelLibraryEdit()"
                        class="hidden px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-md hover:bg-gray-300 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- Book List --}}
    @if($books->count())
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($books as $book)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $book->subject }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $book->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">£{{ number_format($book->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $book->created_at ? $book->created_at->format('d M Y') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button type="button"
                                            onclick="editLibraryBook({{ $book->id }}, '{{ addslashes($book->subject) }}', '{{ addslashes($book->title) }}', '{{ $book->price }}')"
                                            class="text-blue-600 hover:text-blue-900 font-medium mr-3">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" action="{{ route('library.destroy', $book->id) }}"
                                          onsubmit="return confirm('Remove this book from the library?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $books->links() }}
        </div>
    @else
        <div class="bg-white shadow-sm rounded-lg p-10 text-center text-gray-500">
            No books in the library yet. Add your first book above.
        </div>
    @endif
</div>

<script>
// Edit mode: populate the form with the book, switch to PUT update — same record, no new row
function editLibraryBook(id, subject, title, price) {
    const form = document.getElementById('library-book-form');
    form.action = '{{ url('book-library') }}/' + id;
    document.getElementById('form-mode-fields').innerHTML =
        '<input type="hidden" name="_method" value="PUT">';

    form.querySelector('[name="subject"]').value = subject;
    form.querySelector('[name="title"]').value = title;
    form.querySelector('[name="price"]').value = price;

    document.getElementById('library-form-heading').textContent = '✏️ Edit Book';
    document.getElementById('library-submit-btn').textContent = 'Update Book';
    document.getElementById('library-cancel-btn').classList.remove('hidden');

    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    form.querySelector('[name="subject"]').focus();
}

// Back to Add mode without saving
function cancelLibraryEdit() {
    const form = document.getElementById('library-book-form');
    form.action = '{{ route('library.store') }}';
    document.getElementById('form-mode-fields').innerHTML = '';

    form.querySelector('[name="subject"]').value = '';
    form.querySelector('[name="title"]').value = '';
    form.querySelector('[name="price"]').value = '';

    document.getElementById('library-form-heading').textContent = '➕ Add New Book';
    document.getElementById('library-submit-btn').textContent = '💾 Save Book';
    document.getElementById('library-cancel-btn').classList.add('hidden');
}
</script>
@endsection
