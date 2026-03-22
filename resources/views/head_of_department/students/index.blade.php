<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Student Management</h2>
                <p class="mt-1 text-sm text-gray-500">Manage students in your department</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('head_of_department.excel.student-template') }}"
                   class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                    <i class="fas fa-download"></i> Download Template
                </a>
                <form action="{{ route('head_of_department.excel.import-students') }}" method="POST" enctype="multipart/form-data" class="inline">
                    @csrf
                    <label class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                        <i class="fas fa-file-import"></i> Import Excel
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden"
                               onchange="this.form.submit()">
                    </label>
                </form>
                <a href="{{ route('head_of_department.excel.export-students') }}"
                   class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                    <i class="fas fa-file-export"></i> Export Excel
                </a>
                <a href="{{ route('head_of_department.students.create') }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                    + Add Student
                </a>
            </div>
        </div>
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
            @if(session('warning'))
                <div class="px-4 py-3 mb-4 text-yellow-800 bg-yellow-100 rounded">{{ session('warning') }}</div>
            @endif
            @if(session('import_errors'))
                <div class="px-4 py-3 mb-4 text-yellow-800 bg-yellow-100 rounded">
                    <div class="mb-1 font-semibold">Some rows were skipped:</div>
                    <ul class="text-sm list-disc list-inside">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Filters --}}
            <div class="p-4 mb-4 bg-white rounded-lg shadow">
                <form method="GET" action="{{ route('head_of_department.students.index') }}" class="flex flex-wrap gap-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search name, number, email..."
                           class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded min-w-48 focus:outline-none focus:ring-1 focus:ring-blue-500">

                    <select name="course_id" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }}
                            </option>
                        @endforeach
                    </select>

                    <select name="year_level" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">All Year Levels</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('year_level') == $i ? 'selected' : '' }}>Year {{ $i }}</option>
                        @endfor
                    </select>

                    <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    </select>

                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                        Filter
                    </button>

                    @if(request()->hasAny(['search','course_id','year_level','status']))
                        <a href="{{ route('head_of_department.students.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">
                        Students
                        <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">
                            {{ $students->total() }} total
                        </span>
                    </h3>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Student No.</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Year</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($students as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono text-sm text-gray-700">{{ $student->student_number }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $student->getFullName() }}</div>
                                <div class="text-xs text-gray-500">{{ $student->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $student->course->code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">Year {{ $student->year_level }}</td>
                            <td class="px-6 py-4">
                                @if($student->status === 'active')
                                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                @elseif($student->status === 'graduated')
                                    <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Graduated</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('head_of_department.students.edit', $student) }}"
                                       class="px-3 py-1 text-xs font-medium text-blue-700 rounded bg-blue-50 hover:bg-blue-100">
                                        Edit
                                    </a>
                                    <form action="{{ route('head_of_department.students.destroy', $student) }}" method="POST"
                                          class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="px-3 py-1 text-xs font-medium text-red-700 rounded delete-btn bg-red-50 hover:bg-red-100"
                                                data-name="{{ $student->getFullName() }}">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                No students found. <a href="{{ route('head_of_department.students.create') }}" class="text-blue-600 hover:underline">Add one</a> or import via Excel.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($students->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
    @push('scripts')
<script>
    document.querySelectorAll('.delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name = this.dataset.name;
            const form = this.closest('.delete-form');

            Swal.fire({
                title: 'Delete Student?',
                text: name + ' will be permanently removed. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
</x-app-layout>
