<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('registrar.students.index') }}"
               class="text-gray-400 hover:text-gray-600">← Back</a>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Student</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $student->student_number }} — {{ $student->getFullName() }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl px-4 mx-auto sm:px-6 lg:px-8">

            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Student Information</h3>
                </div>

                <form action="{{ route('registrar.students.update', $student) }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Student Number + Course --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Student Number <span class="text-red-500">*</span></label>
                            <input type="text" name="student_number" value="{{ old('student_number', $student->student_number) }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('student_number') border-red-400 @else border-gray-300 @enderror">
                            @error('student_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Course <span class="text-red-500">*</span></label>
                            <select name="course_id"
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('course_id') border-red-400 @else border-gray-300 @enderror">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id', $student->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->code }} — {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('first_name') border-red-400 @else border-gray-300 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name) }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('last_name') border-red-400 @else border-gray-300 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Suffix + Birth Date + Gender --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $student->suffix) }}"
                                   placeholder="Jr., Sr., III..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Birth Date</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Gender</label>
                            <select name="gender"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $student->email) }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('email') border-red-400 @else border-gray-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Address</label>
                        <input type="text" name="address" value="{{ old('address', $student->address) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    {{-- Student Type --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Student Type <span class="text-red-500">*</span></label>
                        <select name="student_type"
                                class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('student_type') border-red-400 @else border-gray-300 @enderror">
                            <option value="Regular" {{ old('student_type', $student->student_type) == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Irregular" {{ old('student_type', $student->student_type) == 'Irregular' ? 'selected' : '' }}>Irregular</option>
                            <option value="Transferee" {{ old('student_type', $student->student_type) == 'Transferee' ? 'selected' : '' }}>Transferee</option>
                        </select>
                        @error('student_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Year Level + Status --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Year Level <span class="text-red-500">*</span></label>
                            <select name="year_level"
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('year_level') border-red-400 @else border-gray-300 @enderror">
                                <option value="">Select Year Level</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('year_level', $student->year_level) == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                                @endfor
                            </select>
                            @error('year_level')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select name="status"
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('status') border-red-400 @else border-gray-300 @enderror">
                                <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('registrar.students.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                            Update Student
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
