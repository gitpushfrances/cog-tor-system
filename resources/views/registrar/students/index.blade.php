<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Student Management</h2>
                <p class="mt-1 text-sm text-gray-500">Manage all students</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('registrar.students.create') }}"
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



            {{-- Filters --}}
            <div class="p-4 mb-4 bg-white rounded-lg shadow">
                <form method="GET" action="{{ route('registrar.students.index') }}" class="flex flex-wrap gap-3">
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
                        <a href="{{ route('registrar.students.index') }}"
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
                        <tr class="transition cursor-pointer hover:bg-blue-50" onclick="openStudentDetails({{ $student->id }})">
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
                            <td class="px-6 py-4" onclick="event.stopPropagation()">
                                <div class="flex gap-2">
                                    <a href="{{ route('registrar.students.edit', $student) }}"
                                       class="px-3 py-1 text-xs font-medium text-blue-700 rounded bg-blue-50 hover:bg-blue-100">
                                        Edit
                                    </a>
                                    <form action="{{ route('registrar.students.destroy', $student) }}" method="POST"
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
                                No students found. <a href="{{ route('registrar.students.create') }}" class="text-blue-600 hover:underline">Add one</a> or import via Excel.
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

      {{-- Student Details Modal --}}
      <div id="student-details-modal" class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-black/50 backdrop-blur-sm" onclick="if(event.target === this) closeStudentDetails()">
          <div id="student-details-panel" class="w-full max-w-3xl overflow-y-auto transition-all duration-200 ease-out scale-95 bg-white opacity-0 rounded-xl shadow-2xl max-h-[85vh]">
              <div id="student-details-body">
                  <div class="flex items-center justify-center p-16">
                      <div class="w-8 h-8 border-4 border-gray-200 rounded-full border-t-blue-600 animate-spin"></div>
                  </div>
              </div>
          </div>
      </div>

      @push('scripts')
<script>
    function studentDetailsUrl(id) {
        return "{{ route('registrar.students.show', ['student' => '__ID__']) }}".replace('__ID__', id);
    }

    function openStudentDetails(id) {
        const modal = document.getElementById('student-details-modal');
        const panel = document.getElementById('student-details-panel');
        const body = document.getElementById('student-details-body');
        body.innerHTML = '<div class="flex items-center justify-center p-16"><div class="w-8 h-8 border-4 border-gray-200 rounded-full border-t-blue-600 animate-spin"></div></div>';
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        });

        fetch(studentDetailsUrl(id))
            .then(res => res.text())
            .then(html => { body.innerHTML = html; })
            .catch(() => { body.innerHTML = '<div class="p-10 text-center text-red-500">Failed to load student details.</div>'; });
    }

    function closeStudentDetails() {
        const modal = document.getElementById('student-details-modal');
        const panel = document.getElementById('student-details-panel');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 150);
    }

    function switchDetailTab(tab) {
        document.querySelectorAll('[data-tab-panel]').forEach(panel => {
            panel.style.display = panel.dataset.tabPanel === tab ? '' : 'none';
        });
        document.querySelectorAll('[data-tab-btn]').forEach(btn => {
            if (btn.dataset.tabBtn === tab) {
                btn.classList.add('border-blue-600', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-500');
            } else {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            }
        });
    }

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
