@php
    $card  = 'background:#fff;border:1px solid #e2d9c8;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.05);';
    $btn   = 'display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:0.78rem;font-weight:600;text-decoration:none;cursor:pointer;white-space:nowrap;';
    $gold  = $btn . 'background:#c9a84c;color:#fff;border:none;';
    $plain = $btn . 'background:#fff;color:#6b5f4a;border:1px solid #e2d9c8;';
    $link  = 'font-size:0.78rem;font-weight:600;color:#4b6043;text-decoration:none;background:none;border:none;cursor:pointer;padding:0;';
    $del   = 'font-size:0.78rem;font-weight:600;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;';
    $pill  = fn ($active) => 'display:inline-block;padding:1px 8px;border-radius:999px;font-size:0.68rem;font-weight:700;' . ($active ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <a href="{{ route('admin.departments.index') }}" style="font-size:0.8rem;color:#8a7a60;text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> All Departments
                </a>
                <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;margin-top:4px;">
                    {{ $department->code }} — {{ $department->name }}
                    <span style="{{ $pill($department->status === 'active') }}vertical-align:middle;">{{ ucfirst($department->status) }}</span>
                </h2>
                @if($department->description)
                    <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">{{ $department->description }}</p>
                @endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('admin.departments.edit', $department) }}" style="{{ $plain }}"><i class="fa-solid fa-pen"></i> Edit Department</a>
                @if($department->status === 'active')
                    <form action="{{ route('admin.departments.deactivate', $department) }}" method="POST" id="deactivate-department">
                        @csrf
                        <button type="button" style="{{ $plain }}color:#dc2626;"
                                onclick="submitWithPassword(document.getElementById('deactivate-department'), {title: 'Deactivate Department?', text: 'This will deactivate this department.', confirmButtonText: 'Yes, Deactivate'})">
                            <i class="fa-solid fa-ban"></i> Deactivate
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.courses.create', ['department_id' => $department->id]) }}" style="{{ $gold }}"><i class="fa-solid fa-plus"></i> Add Course</a>
            </div>
        </div>
    </x-slot>

    <div style="max-width:80rem;margin:0 auto;display:flex;flex-direction:column;gap:14px;">

        @if(session('success'))
            <div style="padding:12px 16px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;border-radius:10px;font-size:0.875rem;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="padding:12px 16px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:0.875rem;">{{ session('error') }}</div>
        @endif

        @forelse($courses as $course)
            <details id="course-{{ $course->id }}" style="{{ $card }}" {{ $openCourse === $course->id || $courses->count() === 1 ? 'open' : '' }}>
                <summary style="list-style:none;cursor:pointer;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;color:#c9a84c;"></i>
                        <span style="font-weight:700;color:#1a1a2e;">{{ $course->code }}</span>
                        <span style="color:#4a4535;">{{ $course->name }}</span>
                        <span style="{{ $pill($course->status === 'active') }}">{{ ucfirst($course->status) }}</span>
                    </div>
                    <div style="font-size:0.78rem;color:#8a7a60;">
                        {{ $course->years }} yrs · {{ $course->majors->count() }} major(s) · {{ $course->subjects->count() }} subject(s) · {{ $course->students_count }} student(s)
                    </div>
                </summary>

                <div style="border-top:1px solid #f0ebe0;padding:16px 20px;display:flex;flex-direction:column;gap:18px;">
                    {{-- Course actions --}}
                    <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;">
                        <a href="{{ route('admin.courses.edit', $course) }}" style="{{ $link }}"><i class="fa-solid fa-pen"></i> Edit course</a>
                        <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" data-password-confirm="Delete course {{ $course->code }}?" data-password-button="Delete"
                              data-password-text="Only a course without subjects and students can be deleted.">
                            @csrf @method('DELETE')
                            <button type="submit" style="{{ $del }}"><i class="fa-solid fa-trash"></i> Delete course</button>
                        </form>
                    </div>

                    {{-- Majors --}}
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#c9a84c;">Majors</div>
                            <a href="{{ route('admin.majors.create', ['course_id' => $course->id]) }}" style="{{ $plain }}"><i class="fa-solid fa-plus"></i> Add Major</a>
                        </div>
                        @if($course->majors->isEmpty())
                            <div style="font-size:0.8rem;color:#b8a88a;">No majors.</div>
                        @else
                            <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                                @foreach($course->majors as $major)
                                    <tr style="border-top:1px solid #f5f0e8;">
                                        <td style="padding:7px 8px;font-family:monospace;width:120px;">{{ $major->code }}</td>
                                        <td style="padding:7px 8px;">{{ $major->name }}</td>
                                        <td style="padding:7px 8px;color:#8a7a60;width:110px;">{{ $major->students_count }} student(s)</td>
                                        <td style="padding:7px 8px;width:80px;"><span style="{{ $pill($major->status === 'active') }}">{{ ucfirst($major->status) }}</span></td>
                                        <td style="padding:7px 8px;width:140px;text-align:right;white-space:nowrap;">
                                            <a href="{{ route('admin.majors.edit', $major) }}" style="{{ $link }}">Edit</a>
                                            <form action="{{ route('admin.majors.destroy', $major) }}" method="POST" style="display:inline;margin-left:10px;"
                                                  data-password-confirm="Delete major {{ $major->code }}?" data-password-button="Delete">
                                                @csrf @method('DELETE')
                                                <button type="submit" style="{{ $del }}">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </div>

                    {{-- Subjects --}}
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#c9a84c;">Subjects</div>
                            <a href="{{ route('admin.subjects.create', ['course_id' => $course->id]) }}" style="{{ $plain }}"><i class="fa-solid fa-plus"></i> Add Subject</a>
                        </div>
                        @if($course->subjects->isEmpty())
                            <div style="font-size:0.8rem;color:#b8a88a;">No subjects.</div>
                        @else
                            <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                                @foreach($course->subjectGroups as $groupLabel => $subjects)
                                    <tr><td colspan="6" style="padding:10px 8px 4px;font-size:0.75rem;font-weight:700;color:#4a4535;">{{ $groupLabel }}</td></tr>
                                    @foreach($subjects as $subject)
                                        <tr style="border-top:1px solid #f5f0e8;">
                                            <td style="padding:7px 8px;font-family:monospace;width:120px;">{{ $subject->code }}</td>
                                            <td style="padding:7px 8px;">{{ $subject->name }}</td>
                                            <td style="padding:7px 8px;color:#8a7a60;width:70px;">{{ $subject->units }} u</td>
                                            <td style="padding:7px 8px;color:#8a7a60;width:110px;">{{ $subject->enrollments_count }} enrolled</td>
                                            <td style="padding:7px 8px;width:80px;"><span style="{{ $pill($subject->status === 'active') }}">{{ ucfirst($subject->status) }}</span></td>
                                            <td style="padding:7px 8px;width:140px;text-align:right;white-space:nowrap;">
                                                <a href="{{ route('admin.subjects.edit', $subject) }}" style="{{ $link }}">Edit</a>
                                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" style="display:inline;margin-left:10px;"
                                                      data-password-confirm="Delete subject {{ $subject->code }}?" data-password-button="Delete">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" style="{{ $del }}">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </table>
                        @endif
                    </div>
                </div>
            </details>
        @empty
            <div style="{{ $card }}padding:40px;text-align:center;color:#b8a88a;font-size:0.9rem;">
                <i class="fa-solid fa-book-open" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                No courses in this department yet.
                <a href="{{ route('admin.courses.create', ['department_id' => $department->id]) }}" style="color:#c9a84c;font-weight:600;">Add the first course</a>.
            </div>
        @endforelse
    </div>

    <style>
        details[open] > summary .fa-chevron-right { transform: rotate(90deg); }
        details > summary::-webkit-details-marker { display: none; }
    </style>
</x-app-layout>
