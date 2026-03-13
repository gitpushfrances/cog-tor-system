<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Registrar Dashboard</h2>
        <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Grade finalization and document generation</p>
    </x-slot>

    <div class="mx-auto space-y-5 max-w-7xl">

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:8px;font-size:0.875rem;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:0.875rem;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
            <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;margin-bottom:6px;">Pending Finalization</div>
                <div style="font-size:2rem;font-weight:700;color:#d97706;">{{ $stats['pending_finalization'] }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;margin-bottom:6px;">Finalized Grades</div>
                <div style="font-size:2rem;font-weight:700;color:#059669;">{{ $stats['finalized_grades'] }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;margin-bottom:6px;">COG Generated</div>
                <div style="font-size:2rem;font-weight:700;color:#2563eb;">{{ $stats['cog_generated'] }}</div>
            </div>
            <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;margin-bottom:6px;">TOR Generated</div>
                <div style="font-size:2rem;font-weight:700;color:#7c3aed;">{{ $stats['tor_generated'] }}</div>
            </div>
        </div>

        {{-- Tabs --}}
        @php $activeTab = request('tab', 'finalization'); @endphp
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.06);">

            <div style="display:flex;border-bottom:1px solid #e2d9c8;background:#faf8f4;">
                <a href="{{ route('registrar.dashboard', ['tab' => 'finalization']) }}"
                   style="display:flex;align-items:center;gap:6px;padding:14px 20px;font-size:0.8rem;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $activeTab === 'finalization' ? '#c9a84c' : 'transparent' }};color:{{ $activeTab === 'finalization' ? '#c9a84c' : '#6b5f4a' }};">
                    <i class="fa-solid fa-list-check"></i> Finalization Queue
                    @if($stats['pending_finalization'] > 0)
                        <span style="background:{{ $activeTab === 'finalization' ? '#c9a84c' : '#e2d9c8' }};color:{{ $activeTab === 'finalization' ? '#fff' : '#8a7a60' }};padding:1px 7px;border-radius:20px;font-size:0.7rem;font-weight:700;">
                            {{ $stats['pending_finalization'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('registrar.dashboard', ['tab' => 'documents']) }}"
                   style="display:flex;align-items:center;gap:6px;padding:14px 20px;font-size:0.8rem;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $activeTab === 'documents' ? '#c9a84c' : 'transparent' }};color:{{ $activeTab === 'documents' ? '#c9a84c' : '#6b5f4a' }};">
                    <i class="fa-solid fa-file-pdf"></i> Generate COG / TOR
                </a>
            </div>

            {{-- TAB 1: Finalization Queue --}}
            @if($activeTab === 'finalization')
            <div style="padding:24px;">
                @if($pending_submissions->isEmpty())
                    <div style="text-align:center;padding:48px;color:#b8a88a;font-size:0.875rem;">
                        <i class="fa-solid fa-circle-check" style="font-size:2rem;display:block;margin-bottom:8px;color:#059669;"></i>
                        No pending finalizations. All grades are finalized.
                    </div>
                @else
                <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                    <thead>
                        <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                            <th style="padding:10px 16px;text-align:left;font-weight:700;">Subject</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:700;">Approved By</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:700;">Date Approved</th>
                            <th style="padding:10px 16px;text-align:center;font-weight:700;">Students</th>
                            <th style="padding:10px 16px;text-align:left;font-weight:700;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending_submissions as $subjectId => $submissions)
                        @php
                            $first   = $submissions->first();
                            $subject = $first->grade->enrollment->subject;
                            $hod     = $first->reviewedBy;
                        @endphp
                        <tr style="border-top:1px solid #f0ebe0;" onmouseover="this.style.background='#faf8f4'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px;">
                                <div style="font-weight:600;color:#1a1a2e;">{{ $subject->name }}</div>
                                <div style="font-size:0.75rem;color:#8a7a60;font-family:monospace;">{{ $subject->code }}</div>
                            </td>
                            <td style="padding:14px 16px;color:#4a4535;">{{ $hod->name ?? '—' }}</td>
                            <td style="padding:14px 16px;color:#8a7a60;font-size:0.8rem;">{{ $first->reviewed_at->format('M d, Y') }}</td>
                            <td style="padding:14px 16px;text-align:center;">
                                <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:700;">
                                    {{ $submissions->count() }}
                                </span>
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <button type="button"
                                        onclick="openPreview('{{ $subjectId }}')"
                                        style="background:#f5f0e8;color:#4a4535;padding:6px 14px;border-radius:8px;font-size:0.78rem;font-weight:600;border:1px solid #e2d9c8;cursor:pointer;"
                                        onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                                        <i class="mr-1 fa-solid fa-eye"></i> Preview
                                    </button>
                                    <form method="POST" action="{{ route('registrar.submissions.finalize-subject', $subjectId) }}"
                                          id="finalizeForm-{{ $subjectId }}">
                                        @csrf
                                        <button type="button"
                                            onclick="confirmFinalize('{{ $subjectId }}', '{{ addslashes($subject->name) }}', {{ $submissions->count() }})"
                                            style="background:#059669;color:#fff;padding:6px 16px;border-radius:8px;font-size:0.78rem;font-weight:600;border:none;cursor:pointer;"
                                            onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                                            <i class="mr-1 fa-solid fa-check"></i> Finalize All
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @endif

            {{-- Preview Modal --}}
            <div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;align-items:center;justify-content:center;">
                <div style="background:#fff;border-radius:12px;width:700px;max-width:95vw;max-height:85vh;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);display:flex;flex-direction:column;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #e2d9c8;background:#faf8f4;">
                        <div>
                            <div id="modalSubjectName" style="font-weight:700;color:#1a1a2e;font-size:1rem;"></div>
                            <div id="modalSubjectCode" style="font-size:0.75rem;color:#8a7a60;font-family:monospace;margin-top:2px;"></div>
                        </div>
                        <button onclick="closePreview()" style="background:none;border:none;font-size:1.2rem;color:#8a7a60;cursor:pointer;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div style="overflow-y:auto;flex:1;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                            <thead>
                                <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;position:sticky;top:0;">
                                    <th style="padding:10px 20px;text-align:left;font-weight:700;">#</th>
                                    <th style="padding:10px 20px;text-align:left;font-weight:700;">Student</th>
                                    <th style="padding:10px 20px;text-align:left;font-weight:700;">Student No.</th>
                                    <th style="padding:10px 20px;text-align:center;font-weight:700;">Grade</th>
                                </tr>
                            </thead>
                            <tbody id="modalTableBody"></tbody>
                        </table>
                    </div>
                    <div style="padding:14px 24px;border-top:1px solid #e2d9c8;background:#faf8f4;display:flex;justify-content:flex-end;">
                        <button onclick="closePreview()" style="background:#f5f0e8;color:#4a4535;padding:8px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:1px solid #e2d9c8;cursor:pointer;">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            {{-- Hidden subject data for modal --}}
            @foreach($pending_submissions as $subjectId => $submissions)
            @php
                $first = $submissions->first();
                $subject = $first->grade->enrollment->subject;
                $rows = $submissions->map(function($s) {
                    return [
                        'name'   => $s->grade->enrollment->student->getfullname(),
                        'number' => $s->grade->enrollment->student->student_number,
                        'grade'  => number_format($s->grade->grade, 2),
                    ];
                });
            @endphp
            <div id="subject-data-{{ $subjectId }}" style="display:none;"
                 data-name="{{ $subject->name }}"
                 data-code="{{ $subject->code }}"
                 data-rows='{{ json_encode($rows) }}'>
            </div>
            @endforeach

            {{-- TAB 2: Generate COG / TOR --}}
            @if($activeTab === 'documents')
            <div style="padding:24px;">
                <form method="GET" action="{{ route('registrar.dashboard') }}" style="display:flex;gap:8px;margin-bottom:20px;">
                    <input type="hidden" name="tab" value="documents">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Search by student name or student number..."
                           style="flex:1;border:1px solid #d4c9b4;border-radius:8px;padding:9px 14px;font-size:0.875rem;outline:none;"
                           onfocus="this.style.borderColor='#c9a84c'" onblur="this.style.borderColor='#d4c9b4'">
                    <button type="submit"
                        style="background:#c9a84c;color:#fff;padding:9px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('registrar.dashboard', ['tab' => 'documents']) }}"
                           style="padding:9px 16px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.875rem;color:#6b5f4a;text-decoration:none;">
                            Clear
                        </a>
                    @endif
                </form>

                @if($search && $students->isEmpty())
                    <p style="color:#b8a88a;font-size:0.875rem;">No students found for "{{ $search }}".</p>
                @endif

                @if($students->isNotEmpty())
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    @foreach($students as $student)
                    <a href="{{ route('registrar.students.profile', $student) }}"
                       style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:1px solid #e2d9c8;border-radius:10px;text-decoration:none;transition:border-color 0.15s;"
                       onmouseover="this.style.borderColor='#c9a84c';this.style.background='#faf8f4'" onmouseout="this.style.borderColor='#e2d9c8';this.style.background='#fff'">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#e8c96e,#c9a84c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="color:#fff;font-size:0.85rem;font-weight:700;">{{ strtoupper(substr($student->first_name,0,1)) }}</span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;color:#1a1a2e;font-size:0.875rem;">{{ $student->getFullName() }}</div>
                            <div style="font-size:0.75rem;color:#8a7a60;">{{ $student->student_number }} &bull; {{ $student->course->name ?? 'N/A' }} &bull; Year {{ $student->year_level }}</div>
                        </div>
                        <span style="font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ $student->status === 'active' ? '#d1fae5' : '#f3f4f6' }};color:{{ $student->status === 'active' ? '#065f46' : '#6b7280' }};">
                            {{ ucfirst($student->status) }}
                        </span>
                    </a>
                    @endforeach
                </div>
                <div style="margin-top:16px;">{{ $students->links() }}</div>
                @endif

                @if(!$search)
                <div style="text-align:center;padding:32px;color:#b8a88a;font-size:0.875rem;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                    Search for a student to generate their COG or TOR.
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
<script>
function confirmFinalize(subjectId, subjectName, count) {
    Swal.fire({
        title: 'Finalize Grades?',
        html: `You are about to finalize <strong>${count} grade(s)</strong> for:<br><br><span style="font-weight:700;color:#1a1a2e;">${subjectName}</span><br><br>This action <strong>cannot be undone.</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Finalize All',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('finalizeForm-' + subjectId).submit();
        }
    });
}

function openPreview(subjectId) {
    const data = document.getElementById('subject-data-' + subjectId);
    document.getElementById('modalSubjectName').textContent = data.dataset.name;
    document.getElementById('modalSubjectCode').textContent = data.dataset.code;

    const rows = JSON.parse(data.dataset.rows);
    const tbody = document.getElementById('modalTableBody');
    tbody.innerHTML = rows.map((r, i) => `
        <tr style="border-top:1px solid #f0ebe0;">
            <td style="padding:12px 20px;color:#8a7a60;font-size:0.8rem;">${i + 1}</td>
            <td style="padding:12px 20px;font-weight:600;color:#1a1a2e;">${r.name}</td>
            <td style="padding:12px 20px;color:#6b5f4a;font-family:monospace;font-size:0.8rem;">${r.number}</td>
            <td style="padding:12px 20px;text-align:center;font-weight:700;color:${r.grade == '5.00' ? '#dc2626' : '#1a1a2e'};">${r.grade}</td>
        </tr>
    `).join('');

    const modal = document.getElementById('previewModal');
    modal.style.display = 'flex';
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});
</script>
</x-app-layout>
