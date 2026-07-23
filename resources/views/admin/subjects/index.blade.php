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

        @if($courses->isEmpty())
            <div class="px-6 py-4 text-center text-gray-400 bg-white rounded-lg shadow">No courses found.</div>
        @else
        <div x-data="{ activeCourse: {{ $courses->first()->id }} }" class="bg-white rounded-lg shadow">
            <div class="flex gap-1 px-4 pt-4 overflow-x-auto border-b border-gray-200">
                @foreach($courses as $course)
                <button
                    type="button"
                    x-on:click="activeCourse = {{ $course->id }}"
                    :class="activeCourse === {{ $course->id }} ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium text-left border-b-2 whitespace-nowrap"
                >
                    {{ $course->code }}
                    <span class="block text-xs font-normal text-gray-400">{{ $course->department->name ?? '—' }}</span>
                </button>
                @endforeach
            </div>

            @foreach($courses as $course)
            <div x-show="activeCourse === {{ $course->id }}" class="p-6 space-y-6">
                @forelse($course->groupedSubjects as $yearLevel => $semesters)
                    @foreach($semesters as $semesterName => $subjectsList)
                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-gray-700">Year {{ $yearLevel }} — {{ $semesterName }}</h3>
                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-2 text-xs font-medium text-left text-gray-500 uppercase">Code</th>
                                        <th class="px-6 py-2 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-2 text-xs font-medium text-left text-gray-500 uppercase">Units</th>
                                        <th class="px-6 py-2 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-2 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($subjectsList as $subject)
                                    <tr>
                                        <td class="px-6 py-3 font-mono text-sm">{{ $subject->code }}</td>
                                        <td class="px-6 py-3 text-sm">{{ $subject->name }}</td>
                                        <td class="px-6 py-3 text-sm">{{ $subject->units }}</td>
                                        <td class="px-6 py-3 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $subject->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ ucfirst($subject->status) }}
                                            </span>
                                        </td>
                                        <td class="flex gap-2 px-6 py-3 text-sm">
                                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-blue-600 hover:underline">Edit</a>
                                            <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Delete this subject?')">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                @empty
                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-gray-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                    <p class="text-sm text-gray-400">No subjects assigned to this program yet.</p>
                    <a href="{{ route('admin.subjects.create', ['course_id' => $course->id]) }}"
                       class="px-3 py-1.5 mt-1 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        + Add Subject
                    </a>
                </div>
                @endforelse
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
