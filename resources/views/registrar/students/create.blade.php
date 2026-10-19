<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('registrar.students.index') }}"
               class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Add Student</h2>
                <p class="mt-1 text-sm text-gray-500">Register a new student</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl px-4 mx-auto sm:px-6 lg:px-8">

            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Student Information</h3>
                </div>

                <form action="{{ route('registrar.students.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    {{-- Student Number + Course --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Student Number <span class="text-red-500">*</span></label>
                            <input type="text" name="student_number" value="{{ old('student_number') }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('student_number') border-red-400 @else border-gray-300 @enderror"
                                   placeholder="e.g. 2024-00001">
                            @error('student_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Course <span class="text-red-500">*</span></label>
                            <select name="course_id" id="course_id" onchange="filterMajors()"
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('course_id') border-red-400 @else border-gray-300 @enderror">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->code }} — {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="major_wrapper" class="hidden">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Major</label>
                        <select name="major_id" id="major_id"
                                class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('major_id') border-red-400 @else border-gray-300 @enderror">
                            <option value="">Select Major</option>
                        </select>
                        @error('major_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Name Fields --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('first_name') border-red-400 @else border-gray-300 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}"
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
                            <input type="text" name="suffix" value="{{ old('suffix') }}"
                                   placeholder="Jr., Sr., III..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Birth Date <span class="text-red-500">*</span></label>
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" required min="1950-01-02" max="{{ now()->subDay()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('birth_date') border-red-400 @else border-gray-300 @enderror">
                            @error('birth_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Gender <span class="text-red-500">*</span></label>
                            <select name="gender" required
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('gender') border-red-400 @else border-gray-300 @enderror">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('email') border-red-400 @else border-gray-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   placeholder="09XXXXXXXXX"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    {{-- Place of Birth --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Place of Birth</label>
                        <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}" placeholder="e.g. Guiuan, Eastern Samar"
                               class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('place_of_birth') border-red-400 @else border-gray-300 @enderror">
                        @error('place_of_birth')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preliminary Education (printed on the TOR) --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Elementary School</label>
                            <input type="text" name="elementary_school" value="{{ old('elementary_school') }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('elementary_school') border-red-400 @else border-gray-300 @enderror">
                            @error('elementary_school')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Elementary Year of Graduation</label>
                            <input type="text" name="elementary_graduation_year" value="{{ old('elementary_graduation_year') }}" placeholder="e.g. 2012" inputmode="numeric" maxlength="4"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('elementary_graduation_year') border-red-400 @else border-gray-300 @enderror">
                            @error('elementary_graduation_year')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">High School</label>
                            <input type="text" name="high_school" value="{{ old('high_school') }}"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('high_school') border-red-400 @else border-gray-300 @enderror">
                            @error('high_school')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">High School Year of Graduation</label>
                            <input type="text" name="high_school_graduation_year" value="{{ old('high_school_graduation_year') }}" placeholder="e.g. 2018" inputmode="numeric" maxlength="4"
                                   class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('high_school_graduation_year') border-red-400 @else border-gray-300 @enderror">
                            @error('high_school_graduation_year')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ID Picture --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">ID Picture (2x2)</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png" class="block w-full text-sm text-gray-700">
                        <p class="mt-1 text-xs text-gray-500">Optional. JPG or PNG, up to 5 MB. Cropped to a square and printed on page 1 of the TOR.</p>
                        @error('photo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Student Type --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Student Type <span class="text-red-500">*</span></label>
                        <select name="student_type"
                                class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('student_type') border-red-400 @else border-gray-300 @enderror">
                            <option value="Regular" {{ old('student_type', 'Regular') == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Irregular" {{ old('student_type') == 'Irregular' ? 'selected' : '' }}>Irregular</option>
                            <option value="Transferee" {{ old('student_type') == 'Transferee' ? 'selected' : '' }}>Transferee</option>
                        </select>
                        @error('student_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Year Level + Section + Status --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Year Level <span class="text-red-500">*</span></label>
                            <select name="year_level"
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('year_level') border-red-400 @else border-gray-300 @enderror">
                                <option value="">Select Year Level</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('year_level') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                                @endfor
                            </select>
                            @error('year_level')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Section</label>
                            <input type="text" name="section" value="{{ old('section') }}" maxlength="20" placeholder="e.g. A"
                                   class="w-full px-3 py-2 text-sm border rounded uppercase focus:outline-none focus:ring-1 focus:ring-blue-500 @error('section') border-red-400 @else border-gray-300 @enderror">
                            @error('section')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select name="status"
                                    class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 @error('status') border-red-400 @else border-gray-300 @enderror">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="graduated" {{ old('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="dropped" {{ old('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
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
                            Save Student
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script>
        const allMajors = @json($majors->map(fn($m) => ['id' => $m->id, 'course_id' => $m->course_id, 'label' => $m->code . ' — ' . $m->name]));
        const oldMajorId = "{{ old('major_id') }}";

        function filterMajors() {
            const courseId = document.getElementById('course_id').value;
            const wrapper = document.getElementById('major_wrapper');
            const select = document.getElementById('major_id');
            const matches = allMajors.filter(m => String(m.course_id) === String(courseId));

            select.innerHTML = '<option value="">Select Major</option>';
            matches.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.label;
                if (String(m.id) === oldMajorId) opt.selected = true;
                select.appendChild(opt);
            });

            wrapper.classList.toggle('hidden', matches.length === 0);
        }

        document.addEventListener('DOMContentLoaded', filterMajors);
    </script>
</x-app-layout>
