<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Faculty Assignment</h2>
        <p class="mt-1 text-sm text-gray-500">Assign faculty members to subjects in your department</p>
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

        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">
                    Subjects
                    <span class="ml-2 text-xs font-normal text-gray-400">{{ $subjects->total() }} total</span>
                </h3>
                <span class="text-xs text-gray-400">{{ auth()->user()->department->name ?? '' }}</span>
            </div>

            @if($subjects->isEmpty())
                <div class="px-6 py-12 text-sm text-center text-gray-400">
                    <i class="block mb-2 text-2xl fa-solid fa-chalkboard"></i>
                    No subjects found in your department.
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs tracking-wider text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Subject</th>
                            <th class="px-6 py-3 text-left">Course</th>
                            <th class="px-6 py-3 text-left">Year / Units</th>
                            <th class="px-6 py-3 text-left">Assigned Faculty</th>
                            <th class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $currentCourse = null; @endphp
                        @foreach($subjects as $subject)
                        @if($currentCourse !== $subject->course_id)
                            @php $currentCourse = $subject->course_id; @endphp
                            <tr>
                                <td colspan="5" style="background:#f5f0e8;padding:8px 24px;font-size:0.72rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8a7a60;border-bottom:1px solid #e2d9c8;">
                                    {{ $subject->course->name }}
                                </td>
                            </tr>
                        @endif
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-800">{{ $subject->code }}</div>
                                <div class="text-xs text-gray-400">{{ $subject->name }}</div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $subject->course->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">Year {{ $subject->year_level }} · {{ $subject->units }} units</td>
                            <td class="px-6 py-3">
                                @if($subject->faculty)
                                    <span class="font-medium text-gray-800">{{ $subject->faculty->name }}</span>
                                @else
                                    <span class="text-xs italic text-gray-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <form method="POST" action="{{ route('head_of_department.assignments.update', $subject) }}"
                                    class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <select name="faculty_id"
                                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        <option value="">— Unassign —</option>
                                        @foreach($facultyList as $faculty)
                                            <option value="{{ $faculty->id }}"
                                                {{ $subject->faculty_id == $faculty->id ? 'selected' : '' }}>
                                                {{ $faculty->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        style="background:#c9a84c;color:#fff;padding:6px 14px;border-radius:8px;font-size:0.75rem;font-weight:600;border:none;cursor:pointer;transition:background 0.15s;"
                                        onmouseover="this.style.background='#a8872e'"
                                        onmouseout="this.style.background='#c9a84c'">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
