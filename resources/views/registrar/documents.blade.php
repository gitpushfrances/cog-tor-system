<x-app-layout>
    <x-slot name="header">
        <p style="font-size:0.75rem;font-weight:600;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Eastern Samar State University - Guiuan Campus</p>
        <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Documents</h2>
        <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">All generated COG and TOR records</p>
    </x-slot>

    <div class="mx-auto space-y-5 max-w-7xl">

        {{-- Filters --}}
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <form method="GET" action="{{ route('registrar.documents.index') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end;">

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">Search Student</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or student number..."
                           style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;min-width:200px;">
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">Type</label>
                    <select name="type" style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                        <option value="">All</option>
                        <option value="cog" {{ request('type') == 'cog' ? 'selected' : '' }}>COG</option>
                        <option value="tor" {{ request('type') == 'tor' ? 'selected' : '' }}>TOR</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">Status</label>
                    <select name="status" style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                        <option value="">All</option>
                        <option value="current" {{ request('status') == 'current' ? 'selected' : '' }}>Current</option>
                        <option value="superseded" {{ request('status') == 'superseded' ? 'selected' : '' }}>Superseded</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">Department</label>
                    <select name="department_id" style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">School Year</label>
                    <select name="school_year_id" style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                        <option value="">All</option>
                        @foreach($schoolYears as $sy)
                            <option value="{{ $sy->id }}" {{ request('school_year_id') == $sy->id ? 'selected' : '' }}>{{ $sy->year_code }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">Semester <span style="font-weight:400;">(COG only)</span></label>
                    <select name="semester_id" style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                        <option value="">All</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                </div>

                <div>
                    <label style="display:block;font-size:0.7rem;font-weight:600;color:#8a7a60;margin-bottom:4px;">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           style="border:1px solid #d4c9b4;border-radius:8px;padding:8px 12px;font-size:0.85rem;">
                </div>

                <button type="submit"
                    style="background:#c9a84c;color:#fff;padding:9px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;">
                    Filter
                </button>

                @if(request()->hasAny(['search','type','status','department_id','school_year_id','semester_id','date_from','date_to']))
                    <a href="{{ route('registrar.documents.index') }}"
                       style="padding:9px 16px;border:1px solid #e2d9c8;border-radius:8px;font-size:0.875rem;color:#6b5f4a;text-decoration:none;">
                        Clear
                    </a>
                @endif
            </form>

            <div style="margin-top:14px;display:flex;gap:8px;">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                   style="padding:6px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;text-decoration:none;background:{{ request('view', 'list') === 'list' ? '#c9a84c' : '#f5f0e8' }};color:{{ request('view', 'list') === 'list' ? '#fff' : '#6b5f4a' }};">
                    <i class="fa-solid fa-list"></i> List View
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'grouped']) }}"
                   style="padding:6px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;text-decoration:none;background:{{ request('view') === 'grouped' ? '#c9a84c' : '#f5f0e8' }};color:{{ request('view') === 'grouped' ? '#fff' : '#6b5f4a' }};">
                    <i class="fa-solid fa-users"></i> Group by Student
                </a>
            </div>
        </div>

        {{-- Legend --}}
        <div style="background:#fefce8;border:1px solid #fef08a;border-radius:10px;padding:12px 16px;font-size:0.8rem;color:#713f12;display:flex;gap:20px;flex-wrap:wrap;">
            <span><span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:20px;font-size:0.7rem;font-weight:700;">Current</span> = the latest, official version of this document.</span>
            <span><span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:20px;font-size:0.7rem;font-weight:700;">Superseded</span> = an older version, replaced by a newer regeneration. Still downloadable for record-keeping.</span>
            <span><i class="fa-solid fa-clock"></i> The date/time shown is when that specific version was generated — not when the student's grades were finalized.</span>
        </div>

        {{-- Results --}}
        <div style="background:#fff;border:1px solid #e2d9c8;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.06);">

            @if($documents->isEmpty())
                <div style="text-align:center;padding:48px;color:#b8a88a;font-size:0.875rem;">
                    <i class="fa-solid fa-folder-open" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                    No documents found for the selected filters.
                </div>
            @else

                @if(request('view') === 'grouped')
                    {{-- Grouped by student --}}
                    @foreach($groupedByStudent as $studentId => $docs)
                    @php $studentModel = $docs->first()->student; @endphp
                    <div style="border-bottom:1px solid #f0ebe0;">
                        <div style="padding:14px 20px;background:#faf8f4;font-weight:700;color:#1a1a2e;font-size:0.9rem;">
                            {{ $studentModel->getFullName() }}
                            <span style="font-weight:400;color:#8a7a60;font-size:0.8rem;">— {{ $studentModel->student_number }}</span>
                        </div>
                        <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                            <thead>
                                <tr style="background:#f9f6f0;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.06em;color:#a89878;">
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">ID</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">Type</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">Document #</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">Semester</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">School Year</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">GWA</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">Generated</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">Status</th>
                                    <th style="padding:8px 20px;text-align:left;font-weight:700;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($docs as $doc)
                                <tr style="border-top:1px solid #f0ebe0;">
                                    <td style="padding:10px 20px;color:#a89878;font-family:monospace;font-size:0.75rem;">#{{ $doc->id }}</td>
                                    <td style="padding:10px 20px;">
                                        <span style="font-weight:700;font-size:0.75rem;color:{{ $doc->doc_type === 'COG' ? '#2563eb' : '#7c3aed' }};">{{ $doc->doc_type }}</span>
                                    </td>
                                    <td style="padding:10px 20px;font-family:monospace;font-size:0.8rem;color:#6b5f4a;">{{ $doc->document_number }}</td>
                                    <td style="padding:10px 20px;color:#4a4535;">
                                        {{ $doc->doc_type === 'COG' ? ($doc->semester->semester_name ?? 'N/A') : 'Complete Record' }}
                                    </td>
                                    <td style="padding:10px 20px;color:#4a4535;">
                                        {{ $doc->doc_type === 'COG' ? ($doc->semester->schoolYear->year_code ?? 'N/A') : '—' }}
                                    </td>
                                    <td style="padding:10px 20px;color:#4a4535;font-weight:600;">
                                        {{ number_format($doc->doc_type === 'COG' ? $doc->semester_gwa : $doc->cumulative_gwa, 2) }}
                                    </td>
                                    <td style="padding:10px 20px;color:#8a7a60;font-size:0.8rem;">{{ $doc->generated_at->format('M d, Y g:i A') }}</td>
                                    <td style="padding:10px 20px;">
                                        @if($doc->is_current)
                                            <span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">Current</span>
                                        @else
                                            <span style="background:#f3f4f6;color:#6b7280;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">Superseded</span>
                                        @endif
                                    </td>
                                    <td style="padding:10px 20px;">
                                        @if($doc->hasFile())
                                            <div style="display:flex;gap:6px;">
                                                <button type="button"
                                                    onclick="openDocPreview('{{ route($doc->doc_type === 'COG' ? 'registrar.cog.preview' : 'registrar.tor.preview', $doc) }}', '{{ $doc->document_number }}')"
                                                    style="background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;padding:6px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                                                    <i class="fa-solid fa-eye"></i> Preview
                                                </button>
                                                <a href="{{ route($doc->doc_type === 'COG' ? 'registrar.cog.download' : 'registrar.tor.download', $doc) }}"
                                                   style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:6px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;text-decoration:none;display:inline-block;">
                                                    <i class="fa-solid fa-download"></i> Download
                                                </a>
                                            </div>
                                        @else
                                            <span style="color:#b8a88a;font-size:0.8rem;">File missing</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                @else
                    {{-- Flat list --}}
                    <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                        <thead>
                            <tr style="background:#f5f0e8;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.08em;color:#8a7a60;">
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">ID</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Type</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Document #</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Student</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Semester</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">School Year</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">GWA</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Generated</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">By</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Status</th>
                                <th style="padding:10px 16px;text-align:left;font-weight:700;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr style="border-top:1px solid #f0ebe0;">
                                <td style="padding:12px 16px;color:#a89878;font-family:monospace;font-size:0.75rem;">#{{ $doc->id }}</td>
                                <td style="padding:12px 16px;">
                                    <span style="font-weight:700;font-size:0.75rem;color:{{ $doc->doc_type === 'COG' ? '#2563eb' : '#7c3aed' }};">{{ $doc->doc_type }}</span>
                                </td>
                                <td style="padding:12px 16px;font-family:monospace;font-size:0.8rem;color:#6b5f4a;">{{ $doc->document_number }}</td>
                                <td style="padding:12px 16px;">
                                    <div style="font-weight:600;color:#1a1a2e;">{{ $doc->student->getFullName() }}</div>
                                    <div style="font-size:0.75rem;color:#8a7a60;">{{ $doc->student->student_number }}</div>
                                </td>
                                <td style="padding:12px 16px;color:#4a4535;">
                                    {{ $doc->doc_type === 'COG' ? ($doc->semester->semester_name ?? 'N/A') : 'Complete Record' }}
                                </td>
                                <td style="padding:12px 16px;color:#4a4535;">
                                    {{ $doc->doc_type === 'COG' ? ($doc->semester->schoolYear->year_code ?? 'N/A') : '—' }}
                                </td>
                                <td style="padding:12px 16px;color:#4a4535;font-weight:600;">
                                    {{ number_format($doc->doc_type === 'COG' ? $doc->semester_gwa : $doc->cumulative_gwa, 2) }}
                                </td>
                                <td style="padding:12px 16px;color:#8a7a60;font-size:0.8rem;">{{ $doc->generated_at->format('M d, Y g:i A') }}</td>
                                <td style="padding:12px 16px;color:#8a7a60;font-size:0.8rem;">{{ $doc->generatedBy->name ?? '—' }}</td>
                                <td style="padding:12px 16px;">
                                    @if($doc->is_current)
                                        <span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">Current</span>
                                    @else
                                        <span style="background:#f3f4f6;color:#6b7280;padding:2px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;">Superseded</span>
                                    @endif
                                </td>
                                <td style="padding:12px 16px;">
                                    @if($doc->hasFile())
                                        <div style="display:flex;gap:6px;">
                                            <button type="button"
                                                onclick="openDocPreview('{{ route($doc->doc_type === 'COG' ? 'registrar.cog.preview' : 'registrar.tor.preview', $doc) }}', '{{ $doc->document_number }}')"
                                                style="background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;padding:6px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                                                <i class="fa-solid fa-eye"></i> Preview
                                            </button>
                                            <a href="{{ route($doc->doc_type === 'COG' ? 'registrar.cog.download' : 'registrar.tor.download', $doc) }}"
                                               style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:6px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;text-decoration:none;display:inline-block;">
                                                <i class="fa-solid fa-download"></i> Download
                                            </a>
                                        </div>
                                    @else
                                        <span style="color:#b8a88a;font-size:0.8rem;">File missing</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            @endif
        </div>

    </div>

    {{-- Document Preview Modal --}}
    <div id="docPreviewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:850px;max-width:95vw;height:85vh;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #e2d9c8;background:#faf8f4;">
                <div id="docPreviewTitle" style="font-weight:700;color:#1a1a2e;font-size:0.9rem;font-family:monospace;"></div>
                <button onclick="closeDocPreview()" style="background:none;border:none;font-size:1.2rem;color:#8a7a60;cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <iframe id="docPreviewFrame" src="" style="flex:1;border:none;width:100%;"></iframe>
        </div>
    </div>

    <script>
        function openDocPreview(url, docNumber) {
            document.getElementById('docPreviewTitle').textContent = docNumber;
            document.getElementById('docPreviewFrame').src = url;
            document.getElementById('docPreviewModal').style.display = 'flex';
        }

        function closeDocPreview() {
            document.getElementById('docPreviewModal').style.display = 'none';
            document.getElementById('docPreviewFrame').src = '';
        }

        document.getElementById('docPreviewModal').addEventListener('click', function(e) {
            if (e.target === this) closeDocPreview();
        });
    </script>
</x-app-layout>
