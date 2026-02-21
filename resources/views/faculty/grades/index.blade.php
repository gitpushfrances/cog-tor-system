<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.subjects') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Subjects</a>
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $subject->code }} - {{ $subject->name }}</h2>
                <p class="text-sm text-gray-500">Year {{ $subject->year_level }} &bull; {{ $subject->semester }} Semester &bull; {{ $subject->units }} units</p>
            </div>
            <form action="{{ route('faculty.subjects.grades.submit', $subject) }}" method="POST"
                  onsubmit="return confirm('Submit all pending grades to Dean for approval?')">
                @csrf
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                    Submit to Dean
                </button>
            </form>
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
            <form action="{{ route('faculty.subjects.grades.store', $subject) }}" method="POST">
                @csrf
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentage (%)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($enrollments as $i => $enrollment)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                                <input type="hidden" name="grades[{{ $i }}][enrollment_id]" value="{{ $enrollment->id }}">
                            </td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $enrollment->student->student_id }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade && $enrollment->grade->status !== 'pending')
                                    {{ $enrollment->grade->percentage }}%
                                @else
                                    <input type="number" name="grades[{{ $i }}][percentage]"
                                           value="{{ $enrollment->grade->percentage ?? '' }}"
                                           min="0" max="100" step="0.01"
                                           class="w-24 border-gray-300 rounded text-sm"
                                           {{ $enrollment->grade && $enrollment->grade->status !== 'pending' ? 'disabled' : '' }}>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold">
                                {{ $enrollment->grade ? number_format($enrollment->grade->grade, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade && $enrollment->grade->status !== 'pending')
                                    {{ $enrollment->grade->remarks ?? '-' }}
                                @else
                                    <input type="text" name="grades[{{ $i }}][remarks]"
                                           value="{{ $enrollment->grade->remarks ?? '' }}"
                                           class="w-32 border-gray-300 rounded text-sm">
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade)
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $enrollment->grade->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $enrollment->grade->status === 'approved_by_dean' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $enrollment->grade->status === 'finalized' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $enrollment->grade->status)) }}
                                    </span>
                                    @if($enrollment->grade->submission && $enrollment->grade->submission->dean_remarks)
                                        <p class="text-xs text-red-500 mt-1">{{ $enrollment->grade->submission->dean_remarks }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">Not encoded</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade && $enrollment->grade->status === 'pending')
                                    <a href="{{ route('faculty.subjects.grades.edit', [$subject, $enrollment->grade]) }}"
                                       class="text-blue-600 hover:underline">Edit</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-4 text-center text-gray-400">No students enrolled in this subject.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($enrollments->where('grade', null)->count() > 0 || $enrollments->contains(fn($e) => $e->grade && $e->grade->status === 'pending'))
                <div class="px-6 py-4 bg-gray-50 border-t">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Save Grades
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</x-app-layout>
