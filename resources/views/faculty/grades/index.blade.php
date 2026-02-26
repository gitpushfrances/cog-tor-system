<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.subjects') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Subjects</a>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $subject->code }} - {{ $subject->name }}</h2>
                <p class="text-sm text-gray-500">Year {{ $subject->year_level }} &bull; {{ $subject->semester }} Semester &bull; {{ $subject->units }} units</p>
            </div>
            <div class="flex flex-wrap gap-2">
                {{-- Download pre-filled grade template --}}
                <a href="{{ route('faculty.subjects.grades.template', $subject) }}"
                   class="px-4 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200">
                    ↓ Download Grade Template
                </a>

                {{-- Upload grades trigger --}}
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                        class="px-4 py-2 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600">
                    ↑ Upload Grades (Excel)
                </button>

                {{-- Submit to Dean --}}
                <form action="{{ route('faculty.subjects.grades.submit', $subject) }}" method="POST"
                      onsubmit="return confirm('Submit all pending grades to Dean for approval?')">
                    @csrf
                    <button class="px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700">
                        ✓ Submit to Dean
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 mx-auto max-w-7xl">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="px-4 py-3 mb-4 text-yellow-800 bg-yellow-100 rounded">{{ session('warning') }}</div>
        @endif
        @if(session('import_errors'))
            <div class="px-4 py-3 mb-4 text-red-800 border border-red-200 rounded bg-red-50">
                <p class="mb-1 font-semibold">Upload Errors (these rows were skipped):</p>
                <ul class="text-sm list-disc list-inside">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Grades Table --}}
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <form action="{{ route('faculty.subjects.grades.store', $subject) }}" method="POST">
                @csrf
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student No.</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Percentage (%)</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Remarks</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($enrollments as $i => $enrollment)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}
                                <input type="hidden" name="grades[{{ $i }}][enrollment_id]" value="{{ $enrollment->id }}">
                            </td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $enrollment->student->student_number }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($enrollment->grade && $enrollment->grade->status !== 'pending')
                                    {{ $enrollment->grade->percentage }}%
                                @else
                                    <input type="number" name="grades[{{ $i }}][percentage]"
                                           value="{{ $enrollment->grade->percentage ?? '' }}"
                                           min="0" max="100" step="0.01"
                                           class="w-24 text-sm border-gray-300 rounded">
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
                                           class="w-32 text-sm border-gray-300 rounded">
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
                                        <p class="mt-1 text-xs text-red-500">{{ $enrollment->grade->submission->dean_remarks }}</p>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Not encoded</span>
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
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-400">No students enrolled in this subject.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($enrollments->where('grade', null)->count() > 0 || $enrollments->contains(fn($e) => $e->grade && $e->grade->status === 'pending'))
                <div class="px-6 py-4 border-t bg-gray-50">
                    <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Save Grades
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Upload Grades Modal --}}
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
            <h3 class="mb-1 text-lg font-semibold text-gray-800">Upload Grades from Excel</h3>
            <p class="mb-2 text-sm text-gray-500">
                Upload a filled grade template. Only <strong>pending</strong> grades will be updated.
                Already approved or finalized grades will be skipped.
            </p>
            <p class="px-3 py-2 mb-4 text-xs text-yellow-700 border border-yellow-200 rounded bg-yellow-50">
                ⚠ Do not modify the Enrollment ID, Student Number, or Subject columns in the template.
            </p>

            <form action="{{ route('faculty.subjects.grades.upload', $subject) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Select File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded">
                    <p class="mt-1 text-xs text-gray-400">Accepted: .xlsx, .xls, .csv — Max 2MB</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600">
                        Upload & Save
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
