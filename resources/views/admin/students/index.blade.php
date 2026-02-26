<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Student Management</h2>
            <div class="flex flex-wrap gap-2">
                {{-- Download blank import template --}}
                <a href="{{ route('admin.excel.student-template') }}"
                   class="px-4 py-2 text-sm text-gray-700 bg-gray-100 border border-gray-300 rounded hover:bg-gray-200">
                    ↓ Download Template
                </a>

                {{-- Export all students --}}
                <a href="{{ route('admin.excel.export-students') }}"
                   class="px-4 py-2 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600">
                    ↓ Export to Excel
                </a>

                {{-- Import trigger --}}
                <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                        class="px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700">
                    ↑ Import Students
                </button>

                <a href="{{ route('admin.students.create') }}"
                   class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                    + Add Student
                </a>
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
                <p class="mb-1 font-semibold">Import Errors:</p>
                <ul class="text-sm list-disc list-inside">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Students Table --}}
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student No.</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Year</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($students as $student)
                    <tr>
                        <td class="px-6 py-4 font-mono text-sm">{{ $student->student_number }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->email }}</td>
                        <td class="px-6 py-4 text-sm">{{ $student->course->code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">Year {{ $student->year_level }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $student->status === 'inactive' ? 'bg-gray-100 text-gray-600' : '' }}
                                {{ $student->status === 'graduated' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $student->status === 'dropped' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td class="flex gap-2 px-6 py-4 text-sm">
                            <a href="{{ route('admin.students.edit', $student) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                  onsubmit="return confirm('Delete this student?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-400">No students found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $students->links() }}</div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
            <h3 class="mb-1 text-lg font-semibold text-gray-800">Import Students from Excel</h3>
            <p class="mb-4 text-sm text-gray-500">
                Upload a filled Excel or CSV file. Download the template first if you haven't already.
            </p>

            <form action="{{ route('admin.excel.import-students') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Select File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded">
                    <p class="mt-1 text-xs text-gray-400">Accepted: .xlsx, .xls, .csv — Max 2MB</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="document.getElementById('importModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700">
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
