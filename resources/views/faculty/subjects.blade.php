<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">My Assigned Subjects</h2>
    </x-slot>

    <div class="px-4 py-6 mx-auto max-w-7xl">
        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($subjects as $subject)
            @php
                // Get latest grade status for this subject
                $latestGrade = $subject->enrollments->first()?->grade;
                $status = $latestGrade?->status ?? 'not_started';
                $isRejected = $status === 'rejected';
                $submission = $latestGrade?->submission;
            @endphp
            <div class="bg-white shadow rounded-lg p-6 {{ $isRejected ? 'border-2 border-red-400' : '' }}">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $subject->code }}</h3>
                        <p class="text-sm text-gray-600">{{ $subject->name }}</p>
                    </div>
                    {{-- Submission status badge --}}
                    @if($status === 'saved')
                        <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">Saved</span>
                    @elseif($status === 'pending_dean_review')
                        <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">⏳ Pending</span>
                    @elseif($status === 'approved_by_dean')
                        <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full"><i class="fas fa-check-circle"></i> Approved</span>
                    @elseif($status === 'rejected')
                        <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full"><i class="fas fa-times-circle"></i> Rejected</span>
                    @elseif($status === 'finalized')
                        <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full"><i class="fas fa-lock"></i> Finalized</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded-full">Not Started</span>
                    @endif
                </div>

                {{-- Rejection remarks shown prominently on card --}}
                @if($isRejected && $submission?->dean_remarks)
                    <div class="px-3 py-2 mb-3 text-xs text-red-800 border border-red-200 rounded bg-red-50">
                        <span class="font-semibold">Head of Department's Remarks:</span> {{ $submission->dean_remarks }}
                    </div>
                @endif

                <div class="mb-4 text-sm text-gray-500">
                    <p>{{ $subject->course->code ?? '-' }} &bull; Year {{ $subject->year_level }} &bull; {{ $subject->semester }} Sem</p>
                    <p class="mt-1">{{ $subject->enrollments->count() }} enrolled students</p>
                    <p class="mt-1">{{ $subject->enrollments->filter(fn($e) => $e->grade)->count() }} grades encoded</p>
                </div>

                <a href="{{ route('faculty.subjects.grades', $subject) }}"
                   class="block text-center px-4 py-2 rounded text-sm font-medium
                       {{ $isRejected ? 'bg-orange-500 text-white hover:bg-orange-600' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                    {{ $isRejected ? 'Correct & Resubmit' : 'Manage Grades' }}
                </a>
            </div>
            @empty
            <div class="col-span-3 p-12 text-center text-gray-400 bg-white rounded-lg shadow">
                No subjects assigned to you yet. Contact your Head of Department.
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
