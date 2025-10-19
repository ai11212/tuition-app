@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.flash')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Manage Books</h1>
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        {{-- Left Column: Add New Book Form --}}
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-plus-circle"></i> Add New Book
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('books.store') }}" id="add-book-form">
                        @csrf
                        
                        @if(Schema::hasColumn('books', 'student_reference'))
                        <!-- Student Reference Search -->
                        <div class="mb-3">
                            <label for="reference_search" class="form-label">Student Reference *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="reference_search" 
                                       placeholder="Enter reference to find siblings">
                                <button type="button" class="btn btn-outline-secondary" onclick="searchSiblings()">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Optional: Search to assign book to specific siblings
                            </div>
                        </div>

                        <!-- Sibling Selection (Hidden by default) -->
                        <div id="sibling_selection" class="mb-3" style="display: none;">
                            <label class="form-label">Select Students:</label>
                            <div id="sibling_list" class="border rounded p-3 bg-light">
                                <!-- Checkboxes will be inserted here by JavaScript -->
                            </div>
                            <small class="text-muted">Select one or more siblings to assign this book.</small>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                   placeholder="e.g., Mathematics" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Book Title *</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="e.g., Algebra Basics" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (£) *</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror" 
                                   placeholder="0.00" value="{{ old('price') }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Add Book
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Column: Existing Books List --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-book"></i> All Books ({{ $books->total() }})</span>
                    <span class="badge bg-secondary">{{ $books->count() }} shown</span>
                </div>
                <div class="card-body p-0">
                    {{-- Search Bar --}}
                    <div class="p-3 bg-light border-bottom">
                        <form method="GET" action="{{ route('books.create') }}" id="search-form">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search by reference, subject, or title..." 
                                           value="{{ request('search') }}">
                                </div>
                                @if(Schema::hasColumn('books', 'student_reference'))
                                <div class="col-md-4">
                                    <input type="text" name="student_ref" class="form-control" 
                                           placeholder="Filter by student ref..." 
                                           value="{{ request('student_ref') }}">
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            @if(request('search') || request('student_ref'))
                                <div class="mt-2">
                                    <a href="{{ route('books.create') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>

                    {{-- Books Table --}}
                    @if($books->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">Reference</th>
                                    <th style="width: 12%;">Subject</th>
                                    <th style="width: 20%;">Title</th>
                                    <th style="width: 10%;">Price</th>
                                    @if(Schema::hasColumn('books', 'student_reference'))
                                        <th style="width: 15%;">Student</th>
                                    @endif
                                    <th style="width: 200px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($books as $book)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $book->reference }}</strong>
                                    </td>
                                    <td>{{ $book->subject }}</td>
                                    <td>{{ Str::limit($book->title, 30) }}</td>
                                    <td><strong>£{{ number_format($book->price, 2) }}</strong></td>
                                    @if(Schema::hasColumn('books', 'student_reference'))
                                    <td>
                                        @if($book->student_reference)
                                            <div>
                                                <strong class="d-block">{{ $book->first_name }} {{ $book->last_name }}</strong>
                                                <small class="text-muted">{{ $book->student_reference }}</small>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">General</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editBook({{ $book->id }}, '{{ addslashes($book->reference) }}', '{{ addslashes($book->subject) }}', '{{ addslashes($book->title) }}', {{ $book->price }}, '{{ addslashes($book->student_reference ?? '') }}')"
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                    style="min-width: 65px;">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('books.destroy', $book) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this book?\n\nReference: {{ $book->reference }}\nTitle: {{ $book->title }}')" 
                                                  class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="Delete book"
                                                        style="min-width: 70px;">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No books found</p>
                        @if(request('search') || request('student_ref'))
                            <a href="{{ route('books.create') }}" class="btn btn-sm btn-secondary">Clear search</a>
                        @endif
                    </div>
                    @endif
                </div>
                
                {{-- Pagination --}}
                @if($books->hasPages())
                <div class="card-footer">
                    {{ $books->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="edit-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reference *</label>
                        <input type="text" name="reference" id="edit-reference" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" id="edit-subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Book Title *</label>
                        <input type="text" name="title" id="edit-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (£) *</label>
                        <input type="number" step="0.01" min="0" name="price" id="edit-price" class="form-control" required>
                    </div>
                    @if(Schema::hasColumn('books', 'student_reference'))
                    <div class="mb-3">
                        <label class="form-label">Student Reference *</label>
                        <input type="text" name="student_reference" id="edit-student-reference" class="form-control" required>
                        <div class="form-text">Leave empty for general books</div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Improve table layout and spacing */
    .table td {
        vertical-align: middle;
    }
    
    /* Ensure buttons don't wrap */
    .table td .d-flex {
        white-space: nowrap;
    }
    
    /* Better hover effect for table rows */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Consistent button sizing */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Badge styling improvements */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    /* Make action column buttons more compact */
    .table td form {
        margin: 0;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .table th:nth-child(3),
        .table td:nth-child(3) {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
</style>

<script>
function editBook(id, reference, subject, title, price, studentRef) {
    document.getElementById('edit-form').action = '/books/' + id;
    document.getElementById('edit-reference').value = reference;
    document.getElementById('edit-subject').value = subject;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-price').value = price;
    @if(Schema::hasColumn('books', 'student_reference'))
    document.getElementById('edit-student-reference').value = studentRef || '';
    @endif
}

// Auto-submit search on input (with debounce)
let searchTimeout;
document.querySelectorAll('#search-form input').forEach(input => {
    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('search-form').submit();
        }, 500);
    });
});

// Function to search for siblings by reference
function searchSiblings() {
    const reference = document.getElementById('reference_search').value.trim();
    
    if (!reference) {
        alert('Please enter a student reference');
        return;
    }
    
    // Show loading state
    document.getElementById('sibling_list').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</div>';
    document.getElementById('sibling_selection').style.display = 'block';
    
    // Fetch siblings via AJAX
    fetch(`/books/get-siblings?reference=${encodeURIComponent(reference)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.siblings.length > 0) {
                // Build checkbox list
                let html = '';
                data.siblings.forEach(sibling => {
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="students[]" 
                                   value="${sibling.reference}" id="sibling_${sibling.id}">
                            <label class="form-check-label" for="sibling_${sibling.id}">
                                ${sibling.first_name} ${sibling.last_name} (${sibling.reference})
                            </label>
                        </div>
                    `;
                });
                document.getElementById('sibling_list').innerHTML = html;
            } else {
                document.getElementById('sibling_list').innerHTML = '<div class="text-danger">No students found with this reference.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('sibling_list').innerHTML = '<div class="text-danger">Error loading siblings. Please try again.</div>';
        });
}
</script>

@endsection
