<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Course Management') }}
            </h2>
            <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                Add New Course
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Courses Table -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Years</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Subjects</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Students</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($courses as $course)
                                <tr>
                                    <td class="px-6 py-4 font-semibold">{{ $course->code }}</td>
                                    <td class="px-6 py-4">{{ $course->name }}</td>
                                    <td class="px-6 py-4">{{ $course->department->code }}</td>
                                    <td class="px-6 py-4">{{ $course->years }}</td>
                                    <td class="px-6 py-4">{{ $course->subjects_count }}</td>
                                    <td class="px-6 py-4">{{ $course->students_count }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $course->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($course->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.courses.edit', $course) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No courses found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $courses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
