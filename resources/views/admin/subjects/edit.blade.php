<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Subject</h2>
    </x-slot>
    <div class="max-w-3xl px-4 py-6 mx-auto">
        <div class="p-6 bg-white rounded-lg shadow">
            <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Code</label>
                        <input type="text" name="code" value="{{ old('code', $subject->code) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Name</label>
                        <input type="text" name="name" value="{{ old('name', $subject->name) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course</label>
                        <select name="course_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id', $subject->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->department->code ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Units</label>
                        <input type="number" name="units" value="{{ old('units', $subject->units) }}" min="1" max="10" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('units')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year Level</label>
                        <select name="year_level" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('year_level', $subject->year_level) == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="semester" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="1st Semester" {{ old('semester', $subject->semester) == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd Semester" {{ old('semester', $subject->semester) == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ old('semester', $subject->semester) == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="active" {{ old('status', $subject->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $subject->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Update Subject</button>
                    <a href="{{ route('admin.subjects.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
