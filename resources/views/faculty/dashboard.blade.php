<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-medium text-blue-700 uppercase tracking-widest mb-1">Eastern Samar State University - Guiuan Campus</p>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Faculty Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Welcome, {{ auth()->user()->name }}</p>
    </x-slot>

    <div class="py-6">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Assigned Subjects</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['assigned_subjects'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Grades Encoded</div>
                    <div class="text-3xl font-bold text-gray-800">{{ $stats['total_grades'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Pending Approval</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_grades'] }}</div>
                </div>
                <div class="p-5 bg-white rounded-lg shadow">
                    <div class="mb-1 text-xs text-gray-500 uppercase">Approved by Head of Department</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved_grades'] }}</div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mb-6">
                <h3 class="mb-3 text-xs font-semibold tracking-wider text-gray-400 uppercase">Quick Actions</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <a href="{{ route('faculty.subjects') }}" class="p-5 transition bg-white border-l-4 border-blue-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-book text-2xl"></i>
                            <div>
                                <div class="font-semibold text-gray-800">My Subjects</div>
                                <div class="text-xs text-gray-500">View assigned subjects and encode grades</div>
                            </div>
                        </div>
                    </a>

                    @foreach($subjects as $subject)
                    <a href="{{ route('faculty.subjects.grades', $subject) }}" class="p-5 transition bg-white border-l-4 border-green-500 rounded-lg shadow hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-pen text-2xl"></i>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $subject->code }}</div>
                                <div class="text-xs text-gray-500">{{ $subject->name }} — Encode Grades</div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Recent Grades --}}
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Recent Grades</h3>
                    <a href="{{ route('faculty.subjects') }}" class="text-sm text-blue-600 hover:underline">View all subjects →</a>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Subject</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recent_grades as $grade)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $grade->enrollment->student->last_name }}, {{ $grade->enrollment->student->first_name }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $grade->enrollment->subject->code }}</td>
                            <td class="px-6 py-4 text-sm font-bold">{{ number_format($grade->grade, 2) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $grade->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $grade->status === 'approved_by_dean' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $grade->status === 'finalized' ? 'bg-blue-100 text-blue-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $grade->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No grades encoded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
