<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Subject</h2>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course</label>
                        <select name="course_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->code }} - {{ $course->department->code ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign Faculty <span class="text-gray-400">(optional)</span></label>
                        <select name="faculty_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Unassigned --</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                    {{ $faculty->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('faculty_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Units</label>
                        <input type="number" name="units" value="{{ old('units') }}" min="1" max="10" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('units')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year Level</label>
                        <select name="year_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('year_level') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                            @endfor
                        </select>
                        @error('year_level')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="semester" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1st" {{ old('semester') == '1st' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd" {{ old('semester') == '2nd' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ old('semester') == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                        @error('semester')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Subject</button>
                    <a href="{{ route('admin.subjects.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
