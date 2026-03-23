<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Review Grade Submission</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $subject->code }} — {{ $subject->name }} &nbsp;·&nbsp; Submitted by {{ $faculty->name }}
                </p>
            </div>
            <a href="{{ route('head_of_department.dashboard') }}"
               class="text-sm text-gray-500 hover:text-gray-800">← Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl px-4 mx-auto space-y-6 sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="px-4 py-3 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            {{-- Faculty remarks block (resubmission) --}}
            @if($submissions->first()?->faculty_remarks)
                <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 text-xs font-semibold bg-yellow-200 text-yellow-800 rounded-full">Resubmission</span>
                        <span class="text-xs text-gray-500">Faculty explanation:</span>
                    </div>
                    <p class="text-sm text-gray-800">{{ $submissions->first()->faculty_remarks }}</p>
                </div>
            @endif

            {{-- Full class grade table --}}
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">
                        Class Grades
                        <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">
                            {{ $submissions->count() }} students
                        </span>
                    </h3>
                    <span class="text-xs text-gray-400">Submitted {{ $submissions->first()->submitted_at->format('M d, Y h:i A') }}</span>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student No.</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($submissions as $sub)
                        <tr class="{{ $sub->grade->grade == 5.00 ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-3 font-mono text-sm text-gray-600">
                                {{ $sub->grade->enrollment->student->student_number }}
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                {{ $sub->grade->enrollment->student->getFullName() }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm font-bold {{ $sub->grade->grade == 5.00 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ number_format($sub->grade->grade, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500">
                                {{ $sub->grade->remarks ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Approve All --}}
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-3 font-semibold text-gray-800">Approve All Grades</h3>
                <p class="mb-4 text-sm text-gray-500">
                    This will approve all {{ $submissions->count() }} grades for {{ $subject->code }} and forward them to the Registrar. This cannot be undone.
                </p>
                <form method="POST" action="{{ route('head_of_department.submissions.approve', $submission) }}" id="approveForm">
                    @csrf
                    <button type="button" onclick="confirmApprove()"
                            class="w-full px-4 py-2 font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="fas fa-check"></i> Approve All {{ $submissions->count() }} Grades
                    </button>
                </form>
            </div>

            {{-- Reject --}}
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-3 font-semibold text-gray-800">Reject & Return to Faculty</h3>
                <form method="POST" action="{{ route('head_of_department.submissions.reject', $submission) }}" id="rejectForm">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Rejection Remarks <span class="text-red-500">*</span>
                        </label>
                        <textarea name="dean_remarks" id="dean_remarks" rows="4" required
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-red-500"
                                  placeholder="Explain why these grades are being returned to faculty...">{{ old('dean_remarks') }}</textarea>
                        @error('dean_remarks')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="button" onclick="confirmReject()"
                            class="w-full px-4 py-2 font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                        <i class="fas fa-times"></i> Reject &amp; Return to Faculty
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        function confirmApprove() {
            Swal.fire({
                title: 'Approve All Grades?',
                text: 'Approve all {{ $submissions->count() }} grades for {{ $subject->code }}? This cannot be undone.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Approve All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approveForm').submit();
                }
            });
        }

        function confirmReject() {
            const remarks = document.getElementById('dean_remarks').value.trim();
            if (!remarks) {
                Swal.fire({
                    title: 'Remarks Required',
                    text: 'Please enter rejection remarks before rejecting.',
                    icon: 'warning',
                    confirmButtonColor: '#d97706'
                });
                return;
            }
            Swal.fire({
                title: 'Reject Submission?',
                text: 'This will return all grades to faculty. They will be notified to resubmit.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('rejectForm').submit();
                }
            });
        }
    </script>

</x-app-layout>
