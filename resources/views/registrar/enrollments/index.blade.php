<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Enrollment Management</h2>
        <p class="mt-1 text-sm text-gray-500">Enroll students into subjects for the active semester</p>
    </x-slot>

    <div class="mx-auto space-y-6 max-w-7xl">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                {{ session('error') }}
            </div>
        @endif

        {{-- Active Semester Banner --}}
        @if($activeSemester)
            <div class="px-4 py-3 text-sm border rounded-lg bg-amber-50 border-amber-200 text-amber-800">
                <i class="mr-1 fa-solid fa-calendar-check"></i>
                Active Semester: <strong>{{ $activeSemester->semester_name }} — {{ $activeSemester->schoolYear->year_code }}</strong>
            </div>
        @else
            <div class="px-4 py-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                <i class="mr-1 fa-solid fa-triangle-exclamation"></i>
                No active semester set. Contact Admin to set an active semester before enrolling.
            </div>
        @endif

        {{-- Enroll Form --}}
        @if($activeSemester)
        <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Enroll a Student</h3>
            <form method="POST" action="{{ route('registrar.enrollments.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Student</label>
                    <select name="student_id" id="student-select" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">Select student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->getFullName() }} — {{ $student->student_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-48">
                    <label class="block mb-1 text-xs font-medium text-gray-600">Subject</label>
                    <select name="subject_id" id="subject-select" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">Select student first...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}"
                                data-code="{{ $subject->code }}"
                                data-name="{{ $subject->name }}">
                                {{ $subject->code }} — {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    style="background:#c9a84c;color:#fff;padding:8px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;white-space:nowrap;"
                    onmouseover="this.style.background='#a8872e'"
                    onmouseout="this.style.background='#c9a84c'">
                    <i class="mr-1 fa-solid fa-plus"></i> Enroll
                </button>
            </form>
        </div>
        @endif

        {{-- Enrollments Table --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">
                    Enrollments
                    <span class="ml-2 text-xs font-normal text-gray-400">{{ $enrollments->total() }} total</span>
                </h3>
            </div>

            @if($enrollments->isEmpty())
                <div class="px-6 py-12 text-sm text-center text-gray-400">
                    <i class="block mb-2 text-2xl fa-solid fa-clipboard-list"></i>
                    No enrollments found for the active semester.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs tracking-wider text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Student</th>
                            <th class="px-6 py-3 text-left">Subject</th>
                            <th class="px-6 py-3 text-left">Semester</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($enrollments as $enrollment)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-800">{{ $enrollment->student->getFullName() }}</div>
                                <div class="text-xs text-gray-400">{{ $enrollment->student->student_number }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-800">{{ $enrollment->subject->code }}</div>
                                <div class="text-xs text-gray-400">{{ $enrollment->subject->name }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $enrollment->semester->semester_name }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $enrollment->status === 'enrolled' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if(!$enrollment->grade()->exists())
                                <form id="delete-enrollment-{{ $enrollment->id }}"
                                    method="POST" action="{{ route('registrar.enrollments.destroy', $enrollment) }}">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                        onclick="confirmRemoveEnrollment({{ $enrollment->id }}, '{{ addslashes($enrollment->student->getFullName()) }}', '{{ addslashes($enrollment->subject->code) }}')"
                                        style="background:transparent;color:#ef4444;font-size:0.75rem;font-weight:500;border:none;cursor:pointer;padding:0;transition:color 0.15s;"
                                        onmouseover="this.style.color='#b91c1c'"
                                        onmouseout="this.style.color='#ef4444'">
                                        Remove
                                    </button>
                                </form>
                                @else
                                    <span class="text-xs text-gray-400">Has grade</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $enrollments->links() }}
                </div>
            @endif
        </div>

    </div>

@push('scripts')
<script>
    const enrolledMap = @json($enrolledMap ?? []);

    const studentSelect = document.getElementById('student-select');
    const subjectSelect = document.getElementById('subject-select');

    const allSubjectOptions = Array.from(subjectSelect.querySelectorAll('option[value]:not([value=""])'));

    studentSelect.addEventListener('change', function () {
        const studentId = this.value;
        const enrolledSubjects = enrolledMap[parseInt(studentId)] || enrolledMap[studentId] || [];

        subjectSelect.innerHTML = '<option value="">' + (studentId ? 'Select subject...' : 'Select student first...') + '</option>';

        if (!studentId) return;

        allSubjectOptions.forEach(opt => {
            const subjectId = parseInt(opt.value);
            const isEnrolled = enrolledSubjects.includes(subjectId);

            const newOpt = document.createElement('option');
            newOpt.value = opt.value;
            newOpt.disabled = isEnrolled;
            newOpt.style.color = isEnrolled ? '#9ca3af' : '';
            newOpt.textContent = opt.getAttribute('data-code') + ' — ' + opt.getAttribute('data-name')
                + (isEnrolled ? ' (Already Enrolled)' : '');
            subjectSelect.appendChild(newOpt);
        });
    });

    if (studentSelect.value) studentSelect.dispatchEvent(new Event('change'));

    function confirmRemoveEnrollment(id, studentName, subjectCode) {
        Swal.fire({
            title: 'Remove Enrollment?',
            html: `<span style="font-size:0.9rem;color:#374151;">Remove <strong>${studentName}</strong> from <strong>${subjectCode}</strong>? This cannot be undone.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Remove',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-enrollment-' + id).submit();
            }
        });
    }
</script>
@endpush
</x-app-layout>
