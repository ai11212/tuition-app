@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('content')
<div class="max-w-3xl mx-auto">
    @include('partials.flash')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">✏️ Edit Teacher</h1>
            <p class="text-sm text-gray-500">Reference and role are not editable.</p>
        </div>
        @if($teacher->reference)
            <span class="font-mono text-sm font-semibold px-3 py-1.5 rounded-full bg-blue-100 text-blue-800">{{ $teacher->reference }}</span>
        @endif
    </div>

    <div class="bg-white shadow-sm rounded-lg p-6">
        <form method="POST" action="{{ route('teachers.update', $teacher) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $teacher->name) }}" maxlength="255" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}" maxlength="50" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                <input type="text" name="nationality" value="{{ old('nationality', $teacher->nationality) }}" maxlength="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nationality') border-red-500 @enderror">
                @error('nationality')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" name="address" value="{{ old('address', $teacher->address) }}" maxlength="190"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">
                @error('address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">DBS Check <span class="text-red-500">*</span></label>
                <div class="flex gap-6 mt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="dbs" value="1" {{ old('dbs', $teacher->dbs ? '1' : '0') === '1' ? 'checked' : '' }}> Yes
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="dbs" value="0" {{ old('dbs', $teacher->dbs ? '1' : '0') === '0' ? 'checked' : '' }}> No
                    </label>
                </div>
                @error('dbs')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="dbs-file-section" class="mt-3" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">DBS Certificate (PDF)</label>
                    @if($teacher->dbs_file)
                        <p class="text-xs text-gray-600 mb-1">
                            Current: <a href="{{ asset('storage/' . $teacher->dbs_file) }}" target="_blank" class="text-blue-600 hover:underline font-medium">View PDF</a>
                            — upload a new file to replace it.
                        </p>
                    @endif
                    <input type="file" name="dbs_file" accept="application/pdf"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold @error('dbs_file') border-red-500 @enderror">
                    @error('dbs_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">PDF only, max 5 MB. Switching DBS to "No" removes the stored certificate.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Hour Rate (£)</label>
                <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $teacher->hourly_rate) }}" step="0.01" min="0" placeholder="0.00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hourly_rate') border-red-500 @enderror">
                @error('hourly_rate')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference <span class="text-red-500">*</span></label>
                <div class="flex gap-6 mt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="reference_doc" value="1" {{ old('reference_doc', $teacher->reference_doc ? '1' : '0') === '1' ? 'checked' : '' }}> Yes
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="reference_doc" value="0" {{ old('reference_doc', $teacher->reference_doc ? '1' : '0') === '0' ? 'checked' : '' }}> No
                    </label>
                </div>
                @error('reference_doc')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="reference-file-section" class="mt-3" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Document (PDF or image)</label>
                    @if($teacher->reference_file)
                        <p class="text-xs text-gray-600 mb-1">
                            Current: <a href="{{ asset('storage/' . $teacher->reference_file) }}" target="_blank" class="text-blue-600 hover:underline font-medium">View</a>
                            — upload a new file to replace it.
                        </p>
                    @endif
                    <input type="file" name="reference_file" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold @error('reference_file') border-red-500 @enderror">
                    @error('reference_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Reference letter — PDF or image, max 5 MB. Switching Reference to "No" removes the stored document.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
                <input type="date" name="joining_date" value="{{ old('joining_date', $teacher->joining_date ? $teacher->joining_date->format('Y-m-d') : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('joining_date') border-red-500 @enderror">
                @error('joining_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Leaving Date</label>
                <input type="date" name="leaving_date" value="{{ old('leaving_date', $teacher->leaving_date ? $teacher->leaving_date->format('Y-m-d') : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('leaving_date') border-red-500 @enderror">
                @error('leaving_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Leave blank while the teacher is currently employed.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                    <option value="active" {{ old('status', $teacher->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $teacher->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Inactive teachers stay on record (history kept) but are hidden from attendance dropdowns.</p>
            </div>

            <div class="md:col-span-2 flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    💾 Update Teacher
                </button>
                <a href="{{ route('teachers.create') }}" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-md hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFileSection(radioName, sectionId) {
    const yes = document.querySelector('input[name="' + radioName + '"][value="1"]');
    const section = document.getElementById(sectionId);
    if (section) section.style.display = (yes && yes.checked) ? '' : 'none';
}
function refreshFileSections() {
    toggleFileSection('dbs', 'dbs-file-section');
    toggleFileSection('reference_doc', 'reference-file-section');
}
document.querySelectorAll('input[name="dbs"], input[name="reference_doc"]').forEach(function (r) {
    r.addEventListener('change', refreshFileSections);
});
refreshFileSections();
</script>
@endsection
