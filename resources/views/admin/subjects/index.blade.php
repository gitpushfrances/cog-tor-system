<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Subject Management</h2>
            <a href="{{ route('admin.subjects.create') }}" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">+ Add Subject</a>
        </div>
    </x-slot>
    <div class="px-4 py-6 mx-auto max-w-7xl">
        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Faculty</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Units</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Year</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Semester</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($subjects as $subject)
                    <tr>
                        <td class="px-6 py-4 font-mono text-sm">{{ $subject->code }}</td>
                        <td class="px-6 py-4 text-sm">{{ $subject->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $subject->course->code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($subject->faculty)
                                {{ $subject->faculty->name }}
                            @else
                                <span class="text-xs italic text-gray-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $subject->units }}</td>
                        <td class="px-6 py-4 text-sm">Year {{ $subject->year_level }}</td>
                        <td class="px-6 py-4 text-sm">{{ $subject->semester }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $subject->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($subject->status) }}
                            </span>
                        </td>
                        <td class="flex gap-2 px-6 py-4 text-sm">
                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Delete this subject?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-6 py-4 text-center text-gray-400">No subjects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $subjects->links() }}</div>
        </div>
    </div>
</x-app-layout>
