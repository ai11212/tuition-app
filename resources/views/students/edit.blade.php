@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Edit Student</h1>
        <a href="{{ route('students.index') }}" class="px-4 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600">Back to Students</a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl border bg-white p-6">
        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Student Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Student Information</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference', $student->reference) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2" required>
                        @error('reference') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2" required>
                        @error('first_name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2" required>
                        @error('last_name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $student->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', $student->dob) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('dob') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="city" value="{{ old('city', $student->city) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('city') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Guardian Information -->
                <div>
                    <h3 class="text-lg font-medium mb-4">Guardian Information</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Guardian Name</label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('guardian_name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Guardian Phone</label>
                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('guardian_phone') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Guardian Email</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email', $student->guardian_email) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('guardian_email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Guardian Address</label>
                        <textarea name="guardian_address" rows="2" 
                                  class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('guardian_address', $student->guardian_address) }}</textarea>
                        @error('guardian_address') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Guardian City</label>
                        <input type="text" name="guardian_city" value="{{ old('guardian_city', $student->guardian_city) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('guardian_city') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Post Code</label>
                        <input type="text" name="post_code" value="{{ old('post_code', $student->post_code) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('post_code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Admission Details -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium mb-4">Admission Details</h3>
                
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Enroll Date</label>
                        <input type="date" name="enroll_date" value="{{ old('enroll_date', $student->enroll_date) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('enroll_date') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $student->start_date) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('start_date') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Period</label>
                        <input type="text" name="period" value="{{ old('period', $student->period) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('period') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deposit (£)</label>
                        <input type="number" step="0.01" name="deposit" value="{{ old('deposit', $student->deposit) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('deposit') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment (£)</label>
                        <input type="number" step="0.01" name="payment" value="{{ old('payment', $student->payment) }}" 
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        @error('payment') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('students.index') }}" 
                   class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" 
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Update Student</button>
            </div>
        </form>
    </div>
</div>
@endsection
