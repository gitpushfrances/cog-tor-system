<div>
    <div class="sticky top-0 z-10 flex items-start justify-between px-6 py-4 bg-white border-b border-gray-100 rounded-t-xl">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">{{ $student->getFullName() }}</h3>
            <p class="text-sm text-gray-500">{{ $student->student_number }} &bull; {{ $student->course->code ?? 'N/A' }} &bull; Year {{ $student->year_level }}</p>
        </div>
        <button type="button" onclick="closeStudentDetails()" class="text-gray-400 transition hover:text-gray-600">
            <i class="text-xl fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="px-6 pt-4 pb-6">
    <div class="flex gap-1 mb-4 border-b border-gray-200">
        <button type="button" onclick="switchDetailTab('subjects')" data-tab-btn="subjects"
            class="px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600">
            Subjects
        </button>
        <button type="button" onclick="switchDetailTab('info')" data-tab-btn="info"
            class="px-4 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            Personal Info
        </button>
    </div>

    <div data-tab-panel="subjects">
        <h4 class="mb-2 text-xs font-semibold tracking-wide text-gray-500 uppercase">
            Currently Enrolled
            @if($activeSemester)
                <span class="font-normal text-gray-400 normal-case">— {{ $activeSemester->getFullName() }}</span>
            @endif
        </h4>

        @if($currentEnrollments->isEmpty())
            <p class="mb-4 text-sm text-gray-400">Not enrolled this semester.</p>
        @else
            <div class="mb-4 overflow-hidden border border-gray-200 rounded-lg">
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Code</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Units</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($currentEnrollments as $enrollment)
                        <tr>
                            <td class="px-4 py-2 font-mono">{{ $enrollment->subject->code }}</td>
                            <td class="px-4 py-2">
                                {{ $enrollment->subject->name }}
                                @if($enrollment->subject->course_id !== $student->course_id)
                                    <span class="px-1.5 py-0.5 ml-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded">Irregular</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $enrollment->subject->units }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $enrollment->status === 'enrolled' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h4 class="mb-2 text-xs font-semibold tracking-wide text-gray-500 uppercase">Previously Enrolled</h4>
        @if($pastEnrollments->isEmpty())
            <p class="text-sm text-gray-400">No past enrollment history.</p>
        @else
            @foreach($pastEnrollments as $semesterLabel => $rows)
            <div class="mb-3">
                <p class="mb-1 text-xs font-medium text-gray-500">{{ $semesterLabel }}</p>
                <div class="overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rows as $enrollment)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $enrollment->subject->code }}</td>
                                <td class="px-4 py-2">
                                    {{ $enrollment->subject->name }}
                                    @if($enrollment->subject->course_id !== $student->course_id)
                                        <span class="px-1.5 py-0.5 ml-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded">Irregular</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $enrollment->subject->units }}</td>
                                <td class="px-4 py-2 text-xs text-gray-500">{{ ucfirst($enrollment->status) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <div data-tab-panel="info" style="display:none;">
        <dl class="grid grid-cols-2 text-sm gap-x-4 gap-y-3">
            <div><dt class="text-xs text-gray-400 uppercase">Email</dt><dd class="text-gray-800">{{ $student->email }}</dd></div>
            <div><dt class="text-xs text-gray-400 uppercase">Phone</dt><dd class="text-gray-800">{{ $student->phone ?? '—' }}</dd></div>
            <div><dt class="text-xs text-gray-400 uppercase">Birth Date</dt><dd class="text-gray-800">{{ $student->birth_date?->format('M d, Y') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-gray-400 uppercase">Gender</dt><dd class="text-gray-800">{{ $student->gender ?? '—' }}</dd></div>
            <div class="col-span-2"><dt class="text-xs text-gray-400 uppercase">Address</dt><dd class="text-gray-800">{{ $student->address ?? '—' }}</dd></div>
            <div><dt class="text-xs text-gray-400 uppercase">Student Type</dt><dd class="text-gray-800">{{ $student->student_type }}</dd></div>
            <div><dt class="text-xs text-gray-400 uppercase">Status</dt><dd class="text-gray-800 capitalize">{{ $student->status }}</dd></div>
        </dl>

        <div class="pt-4 mt-4 border-t border-gray-100">
            <a href="{{ route('registrar.students.edit', $student) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                Edit Student
            </a>
        </div>
    </div>
    </div>
</div>
