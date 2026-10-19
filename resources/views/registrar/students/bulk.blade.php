@php
    $card = 'background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);';
    $btn  = 'display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;text-decoration:none;';
    $th   = 'padding:9px 12px;text-align:left;font-weight:700;';
    $td   = 'padding:9px 12px;vertical-align:top;';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Bulk Add Students</h2>
                <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Fill the template, upload it, check the preview, then add.</p>
            </div>
            <a href="{{ route('registrar.students.index') }}" style="{{ $btn }}border:1px solid #e2d9c8;color:#6b5f4a;background:#fff;">
                <i class="fa-solid fa-arrow-left"></i> Back to Students
            </a>
        </div>
    </x-slot>

    <div style="max-width:80rem;margin:0 auto;display:flex;flex-direction:column;gap:20px;">

        @if(session('error'))
            <div style="padding:12px 16px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:0.875rem;">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div style="padding:12px 16px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:0.875rem;">{{ $errors->first() }}</div>
        @endif

        {{-- Step 1 + 2: template and upload --}}
        <div style="{{ $card }}display:flex;flex-wrap:wrap;gap:24px;align-items:flex-end;justify-content:space-between;">
            <div>
                <div style="font-size:0.75rem;font-weight:700;color:#c9a84c;text-transform:uppercase;letter-spacing:0.08em;">Step 1</div>
                <div style="font-weight:600;color:#1a1a2e;margin:4px 0 8px;">Download the template</div>
                <a href="{{ route('registrar.students.bulk.template') }}" style="{{ $btn }}border:1px solid #c9a84c;color:#92610c;background:#fff;">
                    <i class="fa-solid fa-download"></i> Template (.xlsx)
                </a>
            </div>
            <form method="POST" action="{{ route('registrar.students.bulk.preview') }}" enctype="multipart/form-data" style="flex:1;min-width:280px;">
                @csrf
                <div style="font-size:0.75rem;font-weight:700;color:#c9a84c;text-transform:uppercase;letter-spacing:0.08em;">Step 2</div>
                <div style="font-weight:600;color:#1a1a2e;margin:4px 0 8px;">Upload the filled template <span style="font-weight:400;color:#8a7a60;font-size:0.8rem;">(.xlsx or .csv, up to {{ \App\Support\StudentBulkImport::MAX_ROWS }} students — nothing is saved yet)</span></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           style="flex:1;min-width:220px;border:1px solid #d4c9b4;border-radius:8px;padding:6px 10px;font-size:0.85rem;background:#fff;">
                    <button type="submit" style="{{ $btn }}border:none;background:#c9a84c;color:#fff;">
                        <i class="fa-solid fa-magnifying-glass"></i> Preview
                    </button>
                </div>
            </form>
        </div>

        {{-- Step 3: preview --}}
        @if($preview)
            @php
                $rows       = collect($preview['rows']);
                $errorRows  = $rows->filter(fn ($r) => $r['errors'])->count();
                $warnRows   = $rows->filter(fn ($r) => ! $r['errors'] && $r['warnings'])->count();
                $readyRows  = $rows->count() - $errorRows - $warnRows;
            @endphp
            <form method="POST" action="{{ route('registrar.students.bulk.store') }}" id="bulkAddForm" style="{{ $card }}padding:0;overflow:hidden;">
                @csrf
                <input type="hidden" name="token" value="{{ $preview['token'] }}">

                <div style="padding:16px 24px;border-bottom:1px solid #f0ebe0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-size:0.75rem;font-weight:700;color:#c9a84c;text-transform:uppercase;letter-spacing:0.08em;">Step 3 — Preview of {{ $preview['file'] }}</div>
                        <div style="font-size:0.85rem;color:#4a4535;margin-top:4px;">
                            <strong>{{ $rows->count() }}</strong> row(s):
                            <span style="color:#065f46;"><strong>{{ $readyRows }}</strong> ready</span> ·
                            <span style="color:#92400e;"><strong>{{ $warnRows }}</strong> possible duplicate(s), unticked</span> ·
                            <span style="color:#991b1b;"><strong>{{ $errorRows }}</strong> with errors, cannot be added</span>
                        </div>
                    </div>
                    <button type="button" onclick="confirmBulkAdd()" style="{{ $btn }}border:none;background:#c9a84c;color:#fff;">
                        <i class="fa-solid fa-user-plus"></i> Add <span id="bulkSelectedCount">0</span> student(s)
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                        <thead>
                            <tr style="background:#f5f0e8;font-size:0.68rem;text-transform:uppercase;letter-spacing:0.06em;color:#8a7a60;">
                                <th style="{{ $th }}width:36px;"><input type="checkbox" id="bulkToggleAll" title="Tick / untick all ready rows"></th>
                                <th style="{{ $th }}">Row</th>
                                <th style="{{ $th }}">Student No.</th>
                                <th style="{{ $th }}">Name</th>
                                <th style="{{ $th }}">Course / Year</th>
                                <th style="{{ $th }}">Type</th>
                                <th style="{{ $th }}">Birth Date</th>
                                <th style="{{ $th }}">Gender</th>
                                <th style="{{ $th }}">Email</th>
                                <th style="{{ $th }}min-width:260px;">Check</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $d = $row['data'];
                                    $bg = $row['errors'] ? '#fef2f2' : ($row['warnings'] ? '#fffbeb' : '#fff');
                                @endphp
                                <tr style="border-top:1px solid #f0ebe0;background:{{ $bg }};">
                                    <td style="{{ $td }}">
                                        @unless($row['errors'])
                                            <input type="checkbox" name="rows[]" value="{{ $row['line'] }}" class="bulk-row" {{ $row['warnings'] ? '' : 'checked' }}>
                                        @endunless
                                    </td>
                                    <td style="{{ $td }}color:#8a7a60;">{{ $row['line'] }}</td>
                                    <td style="{{ $td }}font-family:monospace;white-space:nowrap;">{{ $d['student_number'] }}</td>
                                    <td style="{{ $td }}font-weight:600;color:#1a1a2e;">{{ $d['last_name'] }}, {{ $d['first_name'] }} {{ $d['middle_name'] }} {{ $d['suffix'] }}</td>
                                    <td style="{{ $td }}white-space:nowrap;">{{ $d['course_code'] }} · {{ $d['year_level'] }}</td>
                                    <td style="{{ $td }}">{{ $d['student_type'] }}</td>
                                    <td style="{{ $td }}white-space:nowrap;">{{ $d['birth_date'] }}</td>
                                    <td style="{{ $td }}">{{ $d['gender'] }}</td>
                                    <td style="{{ $td }}">{{ $d['email'] }}</td>
                                    <td style="{{ $td }}">
                                        @foreach($row['errors'] as $message)
                                            <div style="color:#991b1b;"><i class="fa-solid fa-circle-xmark"></i> {{ $message }}</div>
                                        @endforeach
                                        @foreach($row['warnings'] as $message)
                                            <div style="color:#92400e;"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</div>
                                        @endforeach
                                        @if($row['changes'])
                                            <div style="color:#6b7280;font-size:0.75rem;"><i class="fa-solid fa-wand-magic-sparkles"></i> Fixed: {{ implode(', ', $row['changes']) }}</div>
                                        @endif
                                        @if(! $row['errors'] && ! $row['warnings'] && ! $row['changes'])
                                            <span style="color:#065f46;"><i class="fa-solid fa-circle-check"></i> Ready</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 24px;border-top:1px solid #f0ebe0;font-size:0.75rem;color:#8a7a60;">
                    Rows with errors are not added: fix them in your file and upload it again (rows already added will then show as duplicates).
                    Possible duplicates are unticked; tick them only if they are different people.
                </div>
            </form>
        @endif
    </div>

    <script>
        (function () {
            const boxes = () => Array.from(document.querySelectorAll('.bulk-row'));
            const counter = document.getElementById('bulkSelectedCount');
            const update = () => { if (counter) counter.textContent = boxes().filter((b) => b.checked).length; };
            document.addEventListener('change', (e) => {
                if (e.target.id === 'bulkToggleAll') boxes().forEach((b) => { b.checked = e.target.checked; });
                if (e.target.id === 'bulkToggleAll' || e.target.classList.contains('bulk-row')) update();
            });
            update();

            window.confirmBulkAdd = function () {
                const count = boxes().filter((b) => b.checked).length;
                if (count === 0) {
                    Swal.fire({ icon: 'warning', title: 'No rows ticked', text: 'Tick at least one row to add.', confirmButtonColor: '#c9a84c' });
                    return;
                }
                Swal.fire({
                    title: `Add ${count} student(s)?`,
                    text: 'They are saved now and listed under Students.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#c9a84c',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, add them',
                    reverseButtons: true,
                }).then((result) => { if (result.isConfirmed) document.getElementById('bulkAddForm').submit(); });
            };
        })();
    </script>
</x-app-layout>
