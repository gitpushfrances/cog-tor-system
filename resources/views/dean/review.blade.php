<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Review Grade Submission</h2>
            <a href="{{ route('dean.dashboard') }}"
               class="text-sm text-gray-600 hover:text-gray-900">← Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto space-y-6 sm:px-6 lg:px-8">

            {{-- Submission Info --}}
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-lg font-semibold text-gray-700">Submission Details</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Student</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->enrollment->student->getFullName() }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Student ID</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->enrollment->student->student_id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Subject</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->enrollment->subject->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Subject Code</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->enrollment->subject->code }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Grade</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->grade }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Percentage</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->percentage }}%</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Submitted By</p>
                        <p class="font-medium text-gray-900">{{ $submission->submittedBy->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Submitted At</p>
                        <p class="font-medium text-gray-900">{{ $submission->submitted_at?->format('M d, Y h:i A') }}</p>
                    </div>
                    @if($submission->grade->remarks)
                    <div class="col-span-2">
                        <p class="text-gray-500">Faculty Remarks</p>
                        <p class="font-medium text-gray-900">{{ $submission->grade->remarks }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Approve --}}
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-lg font-semibold text-gray-700">Approve Submission</h3>
                <form method="POST" action="{{ route('dean.submissions.approve', $submission) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Approve this grade submission?')"
                            class="w-full px-4 py-2 font-semibold text-white transition bg-green-600 rounded-lg hover:bg-green-700">
                        ✓ Approve & Forward to Registrar
                    </button>
                </form>
            </div>

            {{-- Reject --}}
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-lg font-semibold text-gray-700">Return to Faculty</h3>
                <form method="POST" action="{{ route('dean.submissions.reject', $submission) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Remarks <span class="text-red-500">*</span></label>
                        <textarea name="dean_remarks" rows="4" required
                                  class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Explain why this grade is being returned...">{{ old('dean_remarks') }}</textarea>
                        @error('dean_remarks')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            onclick="return confirm('Return this grade to faculty?')"
                            class="w-full px-4 py-2 font-semibold text-white transition bg-red-600 rounded-lg hover:bg-red-700">
                        ✗ Reject & Return to Faculty
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
