<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Registrar Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('registrar.students') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    Generate COG / TOR
                </a>
            </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Pending Finalization</div>
                        <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_finalization'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Finalized Grades</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['finalized_grades'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">COG Generated</div>
                        <div class="text-3xl font-bold">{{ $stats['cog_generated'] }}</div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">TOR Generated</div>
                        <div class="text-3xl font-bold">{{ $stats['tor_generated'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">{{ session('success') }}</div>
            @endif

            <!-- Pending Finalization -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Grades Pending Finalization</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Reviewed By</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($pending_submissions as $submission)
                                <tr>
                                    <td class="px-6 py-4">{{ $submission->grade->enrollment->student->getFullName() }}</td>
                                    <td class="px-6 py-4">{{ $submission->grade->enrollment->subject->code }}</td>
                                    <td class="px-6 py-4">{{ $submission->reviewedBy->name }}</td>
                                    <td class="px-6 py-4">{{ $submission->reviewed_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <form method="POST" action="{{ route('registrar.submissions.finalize', $submission) }}">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Finalize this grade?')"
                                                    class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white transition bg-green-600 rounded-lg hover:bg-green-700">
                                                Finalize
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No pending finalizations</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
