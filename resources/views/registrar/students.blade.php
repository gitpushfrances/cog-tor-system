<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Students — Generate Documents</h2>
            <a href="{{ route('registrar.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">← Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student No.</th>
                                <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Course</th>
                                <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Year</th>
                                <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($students as $student)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $student->student_number }}</td>
                                <td class="px-6 py-4 text-sm font-medium">{{ $student->getFullName() }}</td>
                                <td class="px-6 py-4 text-sm">{{ $student->course->code ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">Year {{ $student->year_level }}</td>
                                <td class="px-6 py-4 space-x-2 text-sm">
                                    <a href="{{ route('registrar.students.cog', $student) }}"
                                       class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                                        COG
                                    </a>
                                    <a href="{{ route('registrar.students.tor', $student) }}"
                                       class="inline-flex items-center px-3 py-1 text-xs font-semibold text-white transition bg-purple-600 rounded-lg hover:bg-purple-700">
                                        TOR
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No active students found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $students->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
