<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Student Management</h2>
            <a href="{{ route('admin.students.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Student</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($students as $student)
                    <tr>
                        <td class="px-6 py-4 font-mono text-sm">{{ $student->student_id }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->email }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->course->code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">Year {{ $student->year_level }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $student->status === 'inactive' ? 'bg-gray-100 text-gray-600' : '' }}
                                {{ $student->status === 'graduated' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm flex gap-2">
                            <a href="{{ route('admin.students.edit', $student) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Delete this student?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-400">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $students->links() }}</div>
        </div>
    </div>
</x-app-layout>
