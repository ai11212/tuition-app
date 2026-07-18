@extends('layouts.app')

@section('title', 'Add Teacher')

@section('content')
<div class="max-w-6xl mx-auto">
    @include('partials.flash')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">👩‍🏫 Add Teacher</h1>
        <p class="text-sm text-gray-500">Reference is generated automatically (T001, T002, …). New teachers also appear in the Staff list.</p>
    </div>

    <div class="bg-white shadow-sm rounded-lg p-6">
        <form method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" maxlength="255" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" maxlength="50" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                <input type="text" name="nationality" value="{{ old('nationality') }}" maxlength="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nationality') border-red-500 @enderror">
                @error('nationality')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" name="address" value="{{ old('address') }}" maxlength="190"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">
                @error('address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">DBS Check <span class="text-red-500">*</span></label>
                <div class="flex gap-6 mt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="dbs" value="1" {{ old('dbs') === '1' ? 'checked' : '' }}> Yes
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="dbs" value="0" {{ old('dbs', '0') === '0' ? 'checked' : '' }}> No
                    </label>
                </div>
                @error('dbs')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="dbs-file-section" class="mt-3" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">DBS Certificate (PDF)</label>
                    <input type="file" name="dbs_file" accept="application/pdf"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold @error('dbs_file') border-red-500 @enderror">
                    @error('dbs_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">PDF only, max 5 MB.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Hour Rate (£)</label>
                <input type="number" name="hourly_rate" value="{{ old('hourly_rate') }}" step="0.01" min="0" placeholder="0.00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hourly_rate') border-red-500 @enderror">
                @error('hourly_rate')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference <span class="text-red-500">*</span></label>
                <div class="flex gap-6 mt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="reference_doc" value="1" {{ old('reference_doc') === '1' ? 'checked' : '' }}> Yes
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="reference_doc" value="0" {{ old('reference_doc', '0') === '0' ? 'checked' : '' }}> No
                    </label>
                </div>
                @error('reference_doc')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="reference-file-section" class="mt-3" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Document (PDF or image)</label>
                    <input type="file" name="reference_file" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer file:mr-3 file:px-3 file:py-2 file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold @error('reference_file') border-red-500 @enderror">
                    @error('reference_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Reference letter — PDF or image, max 5 MB.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
                <input type="date" name="joining_date" value="{{ old('joining_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('joining_date') border-red-500 @enderror">
                @error('joining_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Leaving Date</label>
                <input type="date" name="leaving_date" value="{{ old('leaving_date') }}"
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
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Inactive teachers stay on record but are hidden from attendance dropdowns.</p>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    💾 Save Teacher
                </button>
            </div>
        </form>
    </div>

    {{-- All Teachers --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">All Teachers</h2>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">{{ $teachers->count() }} total</span>
        </div>

        @if($teachers->count())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DBS</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference Doc</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($teachers as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                            <span class="font-mono font-semibold text-blue-700">{{ $t->reference ?? '—' }}</span>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                            <div class="font-semibold text-gray-900">{{ $t->name }}</div>
                            @if($t->nationality)
                                <div class="text-xs text-gray-500">{{ $t->nationality }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600">{{ $t->phone ?: '—' }}</td>
                        <td class="px-3 py-4 text-sm text-gray-600" style="max-width: 11rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $t->address ?: '—' }}</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                            @if($t->dbs)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">No</span>
                            @endif
                            @if($t->dbs && $t->dbs_file)
                                <a href="{{ asset('storage/' . $t->dbs_file) }}" target="_blank" class="block text-xs text-blue-600 hover:underline mt-1">View PDF</a>
                            @endif
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ $t->hourly_rate !== null ? '£' . number_format($t->hourly_rate, 2) . '/hr' : '—' }}
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                            @if(($t->status ?? 'active') === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">Inactive</span>
                            @endif
                            @if($t->joining_date)
                                <div class="text-xs text-gray-500 mt-1">Joined {{ $t->joining_date->format('d/m/Y') }}</div>
                            @endif
                            @if($t->leaving_date)
                                <div class="text-xs text-red-500">Left {{ $t->leaving_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                            @if($t->reference_doc)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Yes</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">No</span>
                            @endif
                            @if($t->reference_doc && $t->reference_file)
                                <a href="{{ asset('storage/' . $t->reference_file) }}" target="_blank" class="block text-xs text-blue-600 hover:underline mt-1">View</a>
                            @endif
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-right">
                            <a href="{{ route('teachers.edit', $t) }}"
                               class="inline-block px-3 py-1 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50 font-medium">Edit</a>
                            <form method="POST" action="{{ route('teachers.destroy', $t) }}" class="inline"
                                  onsubmit="return confirm('Remove teacher {{ addslashes($t->name) }}? Their attendance history will also be deleted.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-block px-3 py-1 rounded-md border border-red-200 text-red-600 hover:bg-red-50 font-medium ml-1">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-10 text-center text-gray-500">No teachers yet. Add your first teacher above.</div>
        @endif
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
