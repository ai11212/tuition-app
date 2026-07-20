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
        <div class="col-md-3">
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
                                <i class="bi bi-info-circle"></i> Required: search and select at least one student
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
                            <label class="form-label">Book (from Library)</label>
                            <select id="library_book_picker" class="form-select">
                                <option value="">— Select from Book Library —</option>
                                @foreach($libraryBooks as $lb)
                                    <option data-subject="{{ $lb->subject }}" data-title="{{ $lb->title }}" data-price="{{ $lb->price }}">
                                        {{ $lb->subject }} — {{ $lb->title }} (£{{ number_format($lb->price, 2) }})
                                    </option>
                                @endforeach
                                <option value="other">Other (enter manually)</option>
                            </select>
                            <div class="form-text">Pick a library book to auto-fill the fields below, or choose Other.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" list="subject_suggestions" autocomplete="off"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   placeholder="e.g., Mathematics" value="{{ old('subject') }}" required>
                            {{-- Distinct library subjects — typing filters the Book Library dropdown live --}}
                            <datalist id="subject_suggestions">
                                @foreach($libraryBooks->pluck('subject')->filter()->unique() as $subj)
                                    <option value="{{ $subj }}"></option>
                                @endforeach
                            </datalist>
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

                        <div class="mb-3">
                            <label class="form-label">Issue Date *</label>
                            <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                                   value="{{ old('issue_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Today or a past date.</div>
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
        <div class="col-md-9">
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
                                @if(Schema::hasColumn('books', 'student_reference'))
                                <div class="col-md-10">
                                    <input type="text" name="student_ref" class="form-control" 
                                           placeholder="Search by reference" 
                                           value="{{ request('student_ref') }}">
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            @if(request('student_ref'))
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
                                    <th style="width: 14%;">Reference</th>
                                    <th style="width: 11%;">Subject</th>
                                    <th style="width: 19%;">Title</th>
                                    <th style="width: 9%;">Price</th>
                                    <th style="width: 11%;">Date</th>
                                    @if(Schema::hasColumn('books', 'student_reference'))
                                        <th style="width: 14%;">Student</th>
                                    @endif
                                    <th style="width: 12%;" class="text-center">Actions</th>
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
                                    <td>{{ ($book->issue_date ?? $book->created_at)->format('d/m/Y') }}</td>
                                    @if(Schema::hasColumn('books', 'student_reference'))
                                    <td>
                                        @if($book->first_name)
                                            <div>
                                                <strong class="d-block">{{ trim($book->first_name . ' ' . $book->last_name) }}</strong>
                                                <small class="text-muted">{{ $book->student_ref }}</small>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">General</span>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editBook({{ $book->id }}, '{{ addslashes($book->reference) }}', '{{ addslashes($book->subject) }}', '{{ addslashes($book->title) }}', {{ $book->price }}, '{{ addslashes($book->student_reference ?? '') }}', '{{ ($book->issue_date ?? $book->created_at)->format('Y-m-d') }}')"
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
                    <div class="mb-3">
                        <label class="form-label">Issue Date *</label>
                        <input type="date" name="issue_date" id="edit-issue-date" class="form-control" max="{{ date('Y-m-d') }}" required>
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
function editBook(id, reference, subject, title, price, studentRef, issueDate) {
    document.getElementById('edit-form').action = '/books/' + id;
    document.getElementById('edit-reference').value = reference;
    document.getElementById('edit-subject').value = subject;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-price').value = price;
    document.getElementById('edit-issue-date').value = issueDate || '';
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
                    // Create unique identifier: studentId|reference|firstName_lastName
                    const uniqueId = `${sibling.id}|${sibling.reference}|${sibling.first_name}_${sibling.last_name}`;
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="students[]" 
                                   value="${uniqueId}" id="sibling_${sibling.id}">
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

// Book Library picker: auto-fill subject/title/price from the selected catalog entry
const libraryPicker = document.getElementById('library_book_picker');
if (libraryPicker) {
    libraryPicker.addEventListener('change', function() {
        const form = document.getElementById('add-book-form');
        const opt = this.options[this.selectedIndex];
        if (this.value === 'other') {
            form.querySelector('[name="subject"]').value = '';
            form.querySelector('[name="title"]').value = '';
            form.querySelector('[name="price"]').value = '';
            form.querySelector('[name="subject"]').focus();
        } else if (opt.dataset.subject !== undefined) {
            form.querySelector('[name="subject"]').value = opt.dataset.subject;
            form.querySelector('[name="title"]').value = opt.dataset.title;
            form.querySelector('[name="price"]').value = opt.dataset.price;
        }
    });
}

// Subject typed-filter: typing in Subject live-filters the Book Library dropdown
// (case-insensitive, partial match). Empty subject = all books, as before.
const subjectFilterInput = document.querySelector('#add-book-form [name="subject"]');
if (libraryPicker && subjectFilterInput) {
    // Snapshot the library options once (placeholder and "Other" always remain)
    const masterLibraryOptions = Array.from(libraryPicker.options)
        .filter(o => o.dataset.subject !== undefined && o.value !== 'other');
    const placeholderOption = libraryPicker.options[0];
    const otherOption = Array.from(libraryPicker.options).find(o => o.value === 'other');

    subjectFilterInput.addEventListener('input', function() {
        const term = this.value.trim().toLowerCase();
        const selectedBefore = libraryPicker.selectedIndex > 0 ? libraryPicker.options[libraryPicker.selectedIndex] : null;

        const frag = document.createDocumentFragment();
        frag.appendChild(placeholderOption);
        let keptSelection = false;
        masterLibraryOptions.forEach(o => {
            if (term === '' || o.dataset.subject.toLowerCase().includes(term)) {
                frag.appendChild(o);
                if (o === selectedBefore) keptSelection = true;
            }
        });
        frag.appendChild(otherOption);

        libraryPicker.innerHTML = '';
        libraryPicker.appendChild(frag);
        // Clear a selection that no longer belongs to the filtered subject
        if (selectedBefore && !keptSelection && selectedBefore !== otherOption) {
            libraryPicker.selectedIndex = 0;
        }
    });
}

@if(Schema::hasColumn('books', 'student_reference'))
// Issuing requires at least one selected student
document.getElementById('add-book-form').addEventListener('submit', function(e) {
    const checked = this.querySelectorAll('input[name="students[]"]:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Please search a student reference and select at least one student before issuing the book.');
    }
});
@endif
</script>

@endsection
