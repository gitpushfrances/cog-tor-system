<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('registrar.dashboard', array_filter(['tab' => 'documents', 'search' => request('search'), 'page' => request('page')])) }}"
           style="display:inline-flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px 16px;font-size:0.85rem;font-weight:500;color:#374151;background:#fff;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;"
           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
            <i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Back to Generate COG / TOR
        </a>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $student->getFullName() }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $student->student_number }} &bull;
                    {{ $student->course->name ?? 'N/A' }} &bull;
                    Year {{ $student->year_level }} &bull;
                    <span class="capitalize">{{ $student->status }}</span>
                </p>
                @if($cumulativeGwa)
                    <p class="mt-1 text-sm font-semibold text-indigo-700">Cumulative GWA: {{ number_format($cumulativeGwa, 1) }}</p>
                @endif
            </div>

            {{-- TOR Button --}}
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @if($student->canHavePreviousSchoolRecords())
                    <button type="button" onclick="openPsrModal()"
                            style="display:inline-flex;align-items:center;gap:8px;margin-top:8px;padding:8px 18px;font-size:0.875rem;font-weight:600;color:#166534;background:#fff;border:1px solid #16a34a;border-radius:8px;cursor:pointer;">
                        <i class="fa-regular fa-images"></i> Previous School Records
                    </button>
                @endif
                <button type="button" onclick="openTorModal()"
                        class="inline-flex items-center gap-2 px-5 py-2 mt-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                    <i class="mr-1 fa-regular fa-file-lines"></i> Generate TOR
                </button>
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 mx-auto max-w-7xl">

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif

        @if($grouped->isEmpty())
            <div class="p-8 text-center text-gray-400 bg-white rounded-lg shadow">
                No finalized grades found for this student.
            </div>
        @else
            @foreach($grouped as $yearGroup)
                <div class="mb-8">
                    {{-- School Year Header --}}
                    <h3 class="mb-3 text-sm font-bold tracking-wider text-gray-500 uppercase">
                        School Year {{ $yearGroup['schoolYear']->year_code }}
                    </h3>

                    @foreach($yearGroup['semesters'] as $semGroup)
                        <div class="mb-4 overflow-hidden bg-white rounded-lg shadow">
                            {{-- Semester Header --}}
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                                <div>
                                    <span class="font-semibold text-gray-800">{{ $semGroup['semester']->semester_name }}</span>
                                    <span class="ml-3 text-sm text-gray-500">
                                        {{ $semGroup['enrollments']->count() }} subjects &bull;
                                        {{ $semGroup['totalUnits'] }} units &bull;
                                        GWA: <strong>{{ $semGroup['semesterGwa'] ? number_format($semGroup['semesterGwa'], 2) : 'N/A' }}</strong>
                                    </span>
                                </div>

                                {{-- COG + Undo Finalize Buttons --}}
                                <div class="flex items-center gap-2">
                                    @if($semGroup['cogRecord'] && $semGroup['cogRecord']->hasFile())
                                        <a href="{{ route('registrar.cog.download', $semGroup['cogRecord']) }}"
                                           class="px-3 py-1 text-xs font-semibold text-indigo-700 border border-indigo-300 rounded hover:bg-indigo-50">
                                            <i class="mr-1 fa-solid fa-download"></i> Download COG
                                        </a>
                                    @endif
                                    <button type="button"
                                            onclick="openCogModal({{ $semGroup['semester']->id }}, @js($semGroup['semester']->semester_name))"
                                            class="px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-700">
                                        Generate COG
                                    </button>

                                    {{-- Undo Finalize --}}
                                    <form method="POST"
                                          action="{{ route('registrar.submissions.unfinalize-subject', $semGroup['semester']->id) }}"
                                          class="unfinalizeForm"
                                          data-semester="{{ $semGroup['semester']->semester_name }}">
                                        @csrf
                                        <button type="button"
                                                onclick="confirmUnfinalize(this)"
                                                class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded hover:bg-red-600">
                                            <i class="mr-1 fa-solid fa-rotate-left"></i> Undo Finalize
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Grades Table --}}
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr class="text-xs font-medium text-left text-gray-400 uppercase">
                                        <th class="px-6 py-3">Subject Code</th>
                                        <th class="px-6 py-3">Subject Name</th>
                                        <th class="px-6 py-3">Units</th>
                                        <th class="px-6 py-3">Grade</th>
                                        <th class="px-6 py-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($semGroup['enrollments'] as $enrollment)
                                        <tr class="{{ $enrollment->grade->grade == 5.00 ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-3 font-mono text-sm">{{ $enrollment->subject->code }}</td>
                                            <td class="px-6 py-3 text-sm">{{ $enrollment->subject->name }}</td>
                                            <td class="px-6 py-3 text-sm">{{ $enrollment->subject->units }}</td>
                                            <td class="px-6 py-3 text-sm font-bold
                                                {{ $enrollment->grade->grade == 5.00 ? 'text-red-600' : 'text-gray-800' }}">
                                                {{ number_format($enrollment->grade->grade, 1) }}
                                            </td>
                                            <td class="px-6 py-3 text-sm text-gray-500">{{ $enrollment->grade->remarks ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>

    {{-- Generate COG Modal --}}
    <div id="cogModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-gray-900/60 backdrop-blur-sm">
        <div id="cogModalPanel" class="flex flex-col w-full max-w-lg max-h-[85vh] bg-white rounded-xl shadow-2xl transition-all duration-200 ease-out">
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-indigo-600 uppercase">Certificate of Grades</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $student->getFullName() }}</h3>
                    <p id="cogModalSubtitle" class="mt-0.5 text-sm text-gray-500"></p>
                </div>
                <button type="button" onclick="closeCogModal()" class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="cogModalBody" class="min-h-0 px-6 py-5 overflow-y-auto"></div>
            <div id="cogModalFooter" class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl"></div>
        </div>
    </div>

    {{-- Generate TOR Modal --}}
    <div id="torModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-gray-900/60 backdrop-blur-sm">
        <div id="torModalPanel" class="flex flex-col w-full max-w-lg max-h-[85vh] bg-white rounded-xl shadow-2xl transition-all duration-200 ease-out">
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-green-600 uppercase">Transcript of Records</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $student->getFullName() }}</h3>
                    <p class="mt-0.5 text-sm text-gray-500">Complete record — all finalized semesters</p>
                </div>
                <button type="button" onclick="closeTorModal()" class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="torModalBody" class="min-h-0 px-6 py-5 overflow-y-auto"></div>
            <div id="torModalFooter" class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl"></div>
        </div>
    </div>

    @if($student->canHavePreviousSchoolRecords())
    <div id="psrModal" class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="flex flex-col w-full max-w-3xl max-h-[92vh] bg-white rounded-xl shadow-2xl">
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-green-600 uppercase">Previous School Records</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $student->getFullName() }}</h3>
                    <p class="mt-0.5 text-sm text-gray-500">Scanned TOR/COG pages from another school, printed on the TOR in the year and semester they cover.</p>
                </div>
                <button type="button" onclick="closePsrModal()" class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="psrModalBody" class="min-h-0 px-6 py-5 overflow-y-auto"></div>
        </div>
    </div>
    @endif

    <script>
        const cogRoutes = {
            preview: '{{ route('registrar.students.cog.preview', $student) }}',
            generate: '{{ route('registrar.students.cog.generate', $student) }}',
        };
        const cogDefaults = {
            signatory_name: @js($documentSettings->registrar_name),
            signatory_credentials: @js($documentSettings->registrar_credentials),
            signatory_title: @js($documentSettings->registrar_title),
        };
        let cogState = { semesterId: null, semesterName: '', dirty: false, form: {} };

        function setCogModalSize(mode) {
            const panel = document.getElementById('cogModalPanel');
            if (mode === 'preview') {
                panel.classList.remove('max-w-lg', 'max-h-[85vh]');
                panel.classList.add('max-w-3xl', 'max-h-[92vh]');
            } else {
                panel.classList.remove('max-w-3xl', 'max-h-[92vh]');
                panel.classList.add('max-w-lg', 'max-h-[85vh]');
            }
        }

        function openCogModal(semesterId, semesterName) {
            cogState = {
                semesterId,
                semesterName,
                dirty: false,
                form: {
                    purpose: '',
                    or_number: '',
                    issued_date: new Date().toISOString().slice(0, 10),
                    signatory_name: cogDefaults.signatory_name,
                    signatory_credentials: cogDefaults.signatory_credentials,
                    signatory_title: cogDefaults.signatory_title,
                }
            };
            document.getElementById('cogModalSubtitle').textContent = semesterName;
            document.getElementById('cogModal').classList.remove('hidden');
            document.getElementById('cogModal').classList.add('flex');
            setCogModalSize('form');
            renderCogFormStep();
        }

        function closeCogModal(force = false) {
            if (!force && cogState.dirty) {
                Swal.fire({
                    title: 'Discard this COG?',
                    text: "Your entries won't be saved.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Discard',
                    cancelButtonText: 'Keep Editing',
                }).then((result) => {
                    if (result.isConfirmed) closeCogModal(true);
                });
                return;
            }
            document.getElementById('cogModal').classList.add('hidden');
            document.getElementById('cogModal').classList.remove('flex');
        }

        function markCogDirty(field, value) {
            cogState.form[field] = value;
            cogState.dirty = true;
        }

        const cogInputClass = 'w-full px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition';
        const cogLabelClass = 'block mb-1.5 text-xs font-semibold text-gray-700';

        function renderCogFormStep() {
            setCogModalSize('form');
            const f = cogState.form;
            document.getElementById('cogModalBody').innerHTML = `
                <div class="mb-6">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Request Details</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Purpose <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.purpose}" oninput="markCogDirty('purpose', this.value)"
                                   placeholder="e.g. Scholarship application" class="${cogInputClass}">
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="${cogLabelClass}">O.R. No. <span class="text-red-500">*</span></label>
                                <input type="text" value="${f.or_number}" oninput="markCogDirty('or_number', this.value)"
                                       placeholder="e.g. 8380142" class="${cogInputClass}">
                            </div>
                            <div>
                                <label class="${cogLabelClass}">Issued Date <span class="text-red-500">*</span></label>
                                <input type="date" value="${f.issued_date}" oninput="markCogDirty('issued_date', this.value)"
                                       class="${cogInputClass}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Signatory</p>
                    <div class="space-y-4">
                        <div class="space-y-4">
                            <div>
                                <label class="${cogLabelClass}">Name <span class="text-red-500">*</span></label>
                                <input type="text" value="${f.signatory_name}" oninput="markCogDirty('signatory_name', this.value)"
                                       class="${cogInputClass} bg-white">
                            </div>
                            <div>
                                <label class="${cogLabelClass}">Credentials</label>
                                <input type="text" value="${f.signatory_credentials}" oninput="markCogDirty('signatory_credentials', this.value)"
                                       placeholder="e.g. CPA, DBA" class="${cogInputClass} bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Title <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.signatory_title}" oninput="markCogDirty('signatory_title', this.value)"
                                   placeholder="e.g. Registrar III" class="${cogInputClass} bg-white">
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('cogModalFooter').innerHTML = `
                <button type="button" onclick="closeCogModal()"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 transition rounded-lg hover:bg-gray-100">Cancel</button>
                <button type="button" onclick="goToCogPreview()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Preview
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            `;
        }

        function goToCogPreview() {
            const required = ['purpose', 'or_number', 'issued_date', 'signatory_name', 'signatory_title'];
            for (const field of required) {
                if (!cogState.form[field] || !cogState.form[field].trim()) {
                    Swal.fire('Missing field', 'Please fill in all required fields before previewing.', 'warning');
                    return;
                }
            }

            document.getElementById('cogModalFooter').innerHTML = '';
            document.getElementById('cogModalBody').innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <svg class="w-6 h-6 mb-3 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm">Rendering preview…</p>
                </div>
            `;

            fetch(cogRoutes.preview, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ semester_id: cogState.semesterId, ...cogState.form }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.html) {
                    renderCogPreviewStep(data.html);
                } else {
                    Swal.fire('Error', 'Could not generate preview.', 'error');
                    renderCogFormStep();
                }
            })
            .catch(() => {
                Swal.fire('Network Error', 'Could not reach the server.', 'error');
                renderCogFormStep();
            });
        }

        function renderCogPreviewStep(html) {
            setCogModalSize('preview');
            document.getElementById('cogModalBody').innerHTML = `
                <div class="flex items-start gap-2 px-3 py-2 mb-4 text-xs border rounded-lg text-amber-800 border-amber-200 bg-amber-50">
                    <svg width="16" height="16" class="mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    Review carefully — this becomes the official record once confirmed.
                </div>
                <div id="cogPreviewScroll" class="flex justify-center p-4 overflow-y-auto bg-gray-100 border border-gray-200 rounded-lg" style="max-height: 62vh;">
                    <iframe id="cogPreviewFrame"
                            class="bg-white border border-gray-200 rounded shadow-sm shrink-0"
                            style="width: 100%; max-width: 620px; height: 400px; border: none;"
                            scrolling="no"></iframe>
                </div>
            `;

            const cogFrame = document.getElementById('cogPreviewFrame');
            const cogScrollWrap = document.getElementById('cogPreviewScroll');

            cogFrame.onload = function () {
                const resize = () => {
                    try {
                        const doc = cogFrame.contentWindow.document;
                        const height = doc.documentElement.scrollHeight || doc.body.scrollHeight;
                        cogFrame.style.height = height + 'px';
                    } catch (e) {
                        cogFrame.style.height = '803px';
                    }
                };

                resize();
                cogScrollWrap.scrollTop = 0;

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        cogScrollWrap.scrollTop = 0;
                    });
                });

                try {
                    const imgs = cogFrame.contentWindow.document.images;
                    for (const img of imgs) {
                        img.addEventListener('load', () => {
                            resize();
                            cogScrollWrap.scrollTop = 0;
                        });
                    }
                } catch (e) {}

                setTimeout(() => { cogScrollWrap.scrollTop = 0; }, 150);
            };

            cogFrame.srcdoc = html;

            document.getElementById('cogModalFooter').innerHTML = `
                <button type="button" onclick="renderCogFormStep()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Edit
                </button>
                <button type="button" onclick="submitCogGenerate()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Confirm & Generate
                </button>
            `;
        }

        function submitCogGenerate() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = cogRoutes.generate;

            const fields = { _token: '{{ csrf_token() }}', semester_id: cogState.semesterId, ...cogState.form };
            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            document.body.appendChild(form);
            cogState.dirty = false;
            form.submit();
        }

        const torRoutes = {
            preview: '{{ route('registrar.students.tor.preview', $student) }}',
            generate: '{{ route('registrar.students.tor.generate', $student) }}',
            photo: '{{ route('registrar.students.photo.update', $student) }}',
        };
        const torDefaults = {
            checked_by_name: @js($documentSettings->registrar_name),
            checked_by_credentials: @js($documentSettings->registrar_credentials),
            checked_by_title: @js($documentSettings->registrar_title),
            prepared_by_name: @js($documentSettings->prepared_by_name),
            prepared_by_title: @js($documentSettings->prepared_by_title),
            campus_admin_name: @js($documentSettings->campus_admin_name),
            campus_admin_title: @js($documentSettings->campus_admin_title),
        };
        const studentIsGraduated = @js($student->status === 'graduated');
        // Saved on the student record; editing them here updates the record when the TOR is generated.
        const studentBackground = @js($student->only(['place_of_birth', 'elementary_school', 'elementary_graduation_year', 'high_school', 'high_school_graduation_year']));
        let torPhoto = @js($student->photoDataUri());
        let torState = { dirty: false, form: {} };

        function setTorModalSize(mode) {
            const panel = document.getElementById('torModalPanel');
            if (mode === 'preview') {
                panel.classList.remove('max-w-lg', 'max-h-[85vh]');
                panel.classList.add('max-w-3xl', 'max-h-[92vh]');
            } else {
                panel.classList.remove('max-w-3xl', 'max-h-[92vh]');
                panel.classList.add('max-w-lg', 'max-h-[85vh]');
            }
        }

        function openTorModal() {
            torState = {
                dirty: false,
                form: {
                    remarks: '',
                    prepared_by_name: torDefaults.prepared_by_name,
                    prepared_by_title: torDefaults.prepared_by_title,
                    checked_by_name: torDefaults.checked_by_name,
                    checked_by_credentials: torDefaults.checked_by_credentials,
                    checked_by_title: torDefaults.checked_by_title,
                    campus_admin_name: torDefaults.campus_admin_name,
                    campus_admin_title: torDefaults.campus_admin_title,
                    place_of_birth: studentBackground.place_of_birth ?? '',
                    elementary_school: studentBackground.elementary_school ?? '',
                    elementary_graduation_year: studentBackground.elementary_graduation_year ?? '',
                    high_school: studentBackground.high_school ?? '',
                    high_school_graduation_year: studentBackground.high_school_graduation_year ?? '',
                    degree_awarded: '',
                    major_at_graduation: '',
                    graduation_date: '',
                    board_resolution_no: '',
                    board_regents_approval_date: '',
                    nstp_serial_number: '',
                }
            };
            document.getElementById('torModal').classList.remove('hidden');
            document.getElementById('torModal').classList.add('flex');
            setTorModalSize('form');
            renderTorFormStep();
        }

        function closeTorModal(force = false) {
            if (!force && torState.dirty) {
                Swal.fire({
                    title: 'Discard this TOR?',
                    text: "Your entries won't be saved.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Discard',
                    cancelButtonText: 'Keep Editing',
                }).then((result) => {
                    if (result.isConfirmed) closeTorModal(true);
                });
                return;
            }
            document.getElementById('torModal').classList.add('hidden');
            document.getElementById('torModal').classList.remove('flex');
        }

        function markTorDirty(field, value) {
            torState.form[field] = value;
            torState.dirty = true;
        }

        function renderTorFormStep() {
            setTorModalSize('form');
            const f = torState.form;
            const gradSection = studentIsGraduated ? `
                <div class="p-4 mt-4 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Graduation Details</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Degree Awarded</label>
                            <input type="text" value="${f.degree_awarded}" oninput="markTorDirty('degree_awarded', this.value)"
                                   placeholder="e.g. Bachelor of Science in Computer Science" class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Major</label>
                            <input type="text" value="${f.major_at_graduation}" oninput="markTorDirty('major_at_graduation', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Graduation Date</label>
                            <input type="date" value="${f.graduation_date}" oninput="markTorDirty('graduation_date', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Board Resolution No.</label>
                            <input type="text" value="${f.board_resolution_no}" oninput="markTorDirty('board_resolution_no', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Board of Regents Approval Date</label>
                            <input type="date" value="${f.board_regents_approval_date}" oninput="markTorDirty('board_regents_approval_date', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">NSTP Serial Number</label>
                            <input type="text" value="${f.nstp_serial_number}" oninput="markTorDirty('nstp_serial_number', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                    </div>
                </div>
            ` : '';

            document.getElementById('torModalBody').innerHTML = `
                ${torPhotoSection()}
                <div class="mb-6">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Student Record Details</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Place of Birth</label>
                            <input type="text" value="${f.place_of_birth}" oninput="markTorDirty('place_of_birth', this.value)"
                                   class="${cogInputClass}">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Elementary School</label>
                            <input type="text" value="${f.elementary_school}" oninput="markTorDirty('elementary_school', this.value)"
                                   class="${cogInputClass}">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Elementary Year of Graduation</label>
                            <input type="text" value="${f.elementary_graduation_year}" oninput="markTorDirty('elementary_graduation_year', this.value)"
                                   placeholder="e.g. 2015" class="${cogInputClass}">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">High School</label>
                            <input type="text" value="${f.high_school}" oninput="markTorDirty('high_school', this.value)"
                                   class="${cogInputClass}">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">High School Year of Graduation</label>
                            <input type="text" value="${f.high_school_graduation_year}" oninput="markTorDirty('high_school_graduation_year', this.value)"
                                   placeholder="e.g. 2021" class="${cogInputClass}">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Remarks</label>
                            <input type="text" value="${f.remarks}" oninput="markTorDirty('remarks', this.value)"
                                   placeholder="optional" class="${cogInputClass}">
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Prepared By</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Name <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.prepared_by_name}" oninput="markTorDirty('prepared_by_name', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Title <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.prepared_by_title}" oninput="markTorDirty('prepared_by_title', this.value)"
                                   placeholder="e.g. Administrative Aide VI" class="${cogInputClass} bg-white">
                        </div>
                    </div>
                </div>

                <div class="p-4 mt-4 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Checked By</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Name <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.checked_by_name}" oninput="markTorDirty('checked_by_name', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Credentials</label>
                            <input type="text" value="${f.checked_by_credentials}" oninput="markTorDirty('checked_by_credentials', this.value)"
                                   placeholder="e.g. CPA, DBA" class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Title <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.checked_by_title}" oninput="markTorDirty('checked_by_title', this.value)"
                                   placeholder="e.g. Registrar III" class="${cogInputClass} bg-white">
                        </div>
                    </div>
                </div>

                <div class="p-4 mt-4 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Campus Administrator</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Name <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.campus_admin_name}" oninput="markTorDirty('campus_admin_name', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Title <span class="text-red-500">*</span></label>
                            <input type="text" value="${f.campus_admin_title}" oninput="markTorDirty('campus_admin_title', this.value)"
                                   class="${cogInputClass} bg-white">
                        </div>
                    </div>
                </div>

                ${gradSection}
            `;

            document.getElementById('torModalFooter').innerHTML = `
                <button type="button" onclick="closeTorModal()"
                        class="px-4 py-2 text-sm font-semibold text-gray-600 transition rounded-lg hover:bg-gray-100">Cancel</button>
                <button type="button" onclick="goToTorPreview()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Preview
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            `;
        }

        function torPhotoThumb() {
            return torPhoto
                ? `<img src="${torPhoto}" alt="ID picture" style="width:100%;height:100%;object-fit:cover;">`
                : `<span style="font-size:11px;color:#9ca3af;">No picture</span>`;
        }

        function torPhotoSection() {
            return `
                <div class="p-4 mb-6 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">2x2 ID Picture</p>
                    <div style="display:flex;align-items:center;gap:16px;">
                        <div id="torPhotoThumb" style="width:96px;height:96px;flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fff;border:1px solid #d1d5db;border-radius:6px;">
                            ${torPhotoThumb()}
                        </div>
                        <div style="min-width:0;">
                            <label style="display:inline-flex;align-items:center;padding:7px 14px;font-size:13px;font-weight:600;color:#374151;background:#fff;border:1px solid #d1d5db;border-radius:8px;cursor:pointer;">
                                <span id="torPhotoBtnText">${torPhoto ? 'Replace picture' : 'Upload picture'}</span>
                                <input type="file" accept="image/jpeg,image/png" onchange="uploadTorPhoto(this)" style="display:none;">
                            </label>
                            <p style="margin-top:6px;font-size:12px;color:#6b7280;">JPG or PNG, up to 5 MB. Cropped to a square and saved to the student's record.</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function uploadTorPhoto(input) {
            const file = input.files[0];
            if (!file) return;

            const btnText = document.getElementById('torPhotoBtnText');
            const data = new FormData();
            data.append('photo', file);
            btnText.textContent = 'Uploading…';

            fetch(torRoutes.photo, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: data,
            })
            .then(async (res) => {
                const body = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(body.errors?.photo?.[0] || body.message || 'Upload failed.');
                }
                return body;
            })
            .then((body) => {
                torPhoto = body.photo;
                document.getElementById('torPhotoThumb').innerHTML = torPhotoThumb();
            })
            .catch((err) => {
                Swal.fire({ icon: 'error', title: 'Picture not saved', text: err.message });
            })
            .finally(() => {
                btnText.textContent = torPhoto ? 'Replace picture' : 'Upload picture';
                input.value = '';
            });
        }

        function goToTorPreview() {
            const required = ['prepared_by_name', 'prepared_by_title', 'checked_by_name', 'checked_by_title', 'campus_admin_name', 'campus_admin_title'];
            for (const field of required) {
                if (!torState.form[field] || !torState.form[field].trim()) {
                    Swal.fire('Missing field', 'Please fill in all required fields before previewing.', 'warning');
                    return;
                }
            }

            document.getElementById('torModalFooter').innerHTML = '';
            document.getElementById('torModalBody').innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <svg class="w-6 h-6 mb-3 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm">Rendering preview…</p>
                </div>
            `;

            fetch(torRoutes.preview, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(torState.form),
            })
            .then(res => res.json())
            .then(data => {
                if (data.html) {
                    renderTorPreviewStep(data.html);
                } else {
                    Swal.fire('Error', data.errors ? Object.values(data.errors)[0][0] : 'Could not generate preview.', 'error');
                    renderTorFormStep();
                }
            })
            .catch(() => {
                Swal.fire('Network Error', 'Could not reach the server.', 'error');
                renderTorFormStep();
            });
        }

        function renderTorPreviewStep(html) {
            setTorModalSize('preview');
            document.getElementById('torModalBody').innerHTML = `
                <div class="flex items-start gap-2 px-3 py-2 mb-4 text-xs border rounded-lg text-amber-800 border-amber-200 bg-amber-50">
                    <svg width="16" height="16" class="mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    Review carefully — this becomes the official record once confirmed.
                </div>
                <div id="torPreviewScroll" class="flex justify-center p-4 overflow-y-auto bg-gray-100 border border-gray-200 rounded-lg" style="max-height: 62vh;">
                    <iframe id="torPreviewFrame"
                            class="bg-white border border-gray-200 rounded shadow-sm shrink-0"
                            style="width: 100%; max-width: 620px; height: 400px; border: none;"
                            scrolling="no"></iframe>
                </div>
            `;

            const torFrame = document.getElementById('torPreviewFrame');
            const torScrollWrap = document.getElementById('torPreviewScroll');

            torFrame.onload = function () {
                const resize = () => {
                    try {
                        const doc = torFrame.contentWindow.document;
                        const height = doc.documentElement.scrollHeight || doc.body.scrollHeight;
                        torFrame.style.height = height + 'px';
                    } catch (e) {
                        torFrame.style.height = '803px';
                    }
                };

                resize();
                torScrollWrap.scrollTop = 0;

                // Browsers can auto-scroll the ancestor container right after
                // an iframe finishes navigating — force it back to top once
                // that settles.
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        torScrollWrap.scrollTop = 0;
                    });
                });

                // Logo image inside the iframe loads after the doc itself,
                // which can reflow height and re-trigger the scroll jump.
                try {
                    const imgs = torFrame.contentWindow.document.images;
                    for (const img of imgs) {
                        img.addEventListener('load', () => {
                            resize();
                            torScrollWrap.scrollTop = 0;
                        });
                    }
                } catch (e) {}

                setTimeout(() => { torScrollWrap.scrollTop = 0; }, 150);
            };

            torFrame.srcdoc = html;

            document.getElementById('torModalFooter').innerHTML = `
                <button type="button" onclick="renderTorFormStep()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Edit
                </button>
                <button type="button" onclick="submitTorGenerate()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Confirm & Generate
                </button>
            `;
        }

        function submitTorGenerate() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = torRoutes.generate;

            const fields = { _token: '{{ csrf_token() }}', ...torState.form };
            for (const [key, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            document.body.appendChild(form);
            torState.dirty = false;
            form.submit();
        }

        // ---- Previous School Records (Transferee / Irregular) ----
        const psrRoutes = {
            index: '{{ route('registrar.students.previous-school-records.index', $student) }}',
            store: '{{ route('registrar.students.previous-school-records.store', $student) }}',
            addPages: (id) => `{{ url('registrar/previous-school-records') }}/${id}/pages`,
            destroy: (id) => `{{ url('registrar/previous-school-records') }}/${id}`,
            destroyPage: (id) => `{{ url('registrar/previous-school-pages') }}/${id}`,
        };
        const psrSemesters = { 1: '1st Semester', 2: '2nd Semester', 3: 'Summer' };
        let psrRecords = [];

        function psrEscape(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function psrRequest(url, method, body = null) {
            return fetch(url, {
                method,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body,
            }).then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const err = new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Request failed.'));
                    err.status = res.status;
                    err.data = data;
                    throw err;
                }
                return data;
            });
        }

        function psrError(err) {
            Swal.fire({ icon: 'error', title: 'Not saved', text: err.message });
        }

        function openPsrModal() {
            document.getElementById('psrModal').classList.remove('hidden');
            document.getElementById('psrModal').classList.add('flex');
            document.getElementById('psrModalBody').innerHTML = '<p class="text-sm text-gray-500">Loading…</p>';
            psrRequest(psrRoutes.index, 'GET').then((data) => {
                psrRecords = data.records;
                renderPsr();
            }).catch(psrError);
        }

        function closePsrModal() {
            document.getElementById('psrModal').classList.add('hidden');
            document.getElementById('psrModal').classList.remove('flex');
        }

        function psrOptions(values, selected) {
            return Object.entries(values).map(([v, label]) =>
                `<option value="${v}" ${String(v) === String(selected) ? 'selected' : ''}>${label}</option>`).join('');
        }

        function renderPsr() {
            const years = { 1: '1st Year', 2: '2nd Year', 3: '3rd Year', 4: '4th Year', 5: '5th Year' };

            const list = psrRecords.length ? psrRecords.map((r) => `
                <div class="p-4 mb-3 border border-gray-200 rounded-lg">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${psrEscape(r.school_name)}</p>
                            <p class="text-xs text-gray-500">${psrEscape(r.coverage)} &middot; ${r.pages.length} image(s)</p>
                        </div>
                        <button type="button" onclick="psrDeleteRecord(${r.id})"
                                style="font-size:12px;font-weight:600;color:#dc2626;background:none;border:none;cursor:pointer;">Delete record</button>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                        ${r.pages.map((p, i) => `
                            <div style="position:relative;">
                                <a href="${p.url}" target="_blank" rel="noopener">
                                    <img src="${p.url}" alt="Page ${i + 1}" style="width:70px;height:90px;object-fit:cover;border:1px solid #d1d5db;border-radius:4px;">
                                </a>
                                <button type="button" onclick="psrDeletePage(${p.id})" title="Remove image"
                                        style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;line-height:18px;font-size:12px;color:#fff;background:#dc2626;border:none;border-radius:50%;cursor:pointer;">&times;</button>
                            </div>`).join('')}
                        <label title="Add images" style="width:70px;height:90px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#6b7280;border:1px dashed #9ca3af;border-radius:4px;cursor:pointer;">
                            +
                            <input type="file" accept="image/jpeg,image/png" multiple style="display:none;" onchange="psrAddPages(${r.id}, this)">
                        </label>
                    </div>
                </div>`).join('') : '<p class="mb-4 text-sm text-gray-500">No previous school records yet.</p>';

            document.getElementById('psrModalBody').innerHTML = `
                ${list}
                <div class="p-4 mt-2 border border-gray-200 rounded-lg bg-gray-50/60">
                    <p class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Add Record</p>
                    <div class="space-y-4">
                        <div>
                            <label class="${cogLabelClass}">Previous School <span class="text-red-500">*</span></label>
                            <input id="psrSchool" type="text" maxlength="255" placeholder="e.g. Eastern Visayas State University" class="${cogInputClass} bg-white">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label class="${cogLabelClass}">From <span class="text-red-500">*</span></label>
                                <div style="display:flex;gap:6px;">
                                    <select id="psrFromYear" class="${cogInputClass} bg-white">${psrOptions(years, 1)}</select>
                                    <select id="psrFromSem" class="${cogInputClass} bg-white">${psrOptions(psrSemesters, 1)}</select>
                                </div>
                            </div>
                            <div>
                                <label class="${cogLabelClass}">To <span class="text-red-500">*</span></label>
                                <div style="display:flex;gap:6px;">
                                    <select id="psrToYear" class="${cogInputClass} bg-white">${psrOptions(years, 1)}</select>
                                    <select id="psrToSem" class="${cogInputClass} bg-white">${psrOptions(psrSemesters, 2)}</select>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="${cogLabelClass}">Scanned Images <span class="text-red-500">*</span></label>
                            <input id="psrImages" type="file" accept="image/jpeg,image/png" multiple class="text-sm">
                            <p style="margin-top:6px;font-size:12px;color:#6b7280;">JPG or PNG, up to 10 MB each, up to 15 images. Select them in page order.</p>
                        </div>
                        <div style="text-align:right;">
                            <button id="psrSaveBtn" type="button" onclick="psrSave(false)"
                                    class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Save Record</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function psrSave(confirmOverlap) {
            const files = document.getElementById('psrImages').files;
            const data = new FormData();
            data.append('school_name', document.getElementById('psrSchool').value.trim());
            data.append('from_year_level', document.getElementById('psrFromYear').value);
            data.append('from_semester', document.getElementById('psrFromSem').value);
            data.append('to_year_level', document.getElementById('psrToYear').value);
            data.append('to_semester', document.getElementById('psrToSem').value);
            Array.from(files).forEach((f) => data.append('images[]', f));
            if (confirmOverlap) data.append('confirm_overlap', '1');

            const btn = document.getElementById('psrSaveBtn');
            btn.disabled = true;
            btn.textContent = 'Saving…';

            psrRequest(psrRoutes.store, 'POST', data).then((res) => {
                psrRecords.push(res.record);
                psrRecords.sort((a, b) => (a.from_year_level * 10 + a.from_semester) - (b.from_year_level * 10 + b.from_semester));
                renderPsr();
            }).catch((err) => {
                btn.disabled = false;
                btn.textContent = 'Save Record';
                if (err.status === 409 && err.data.overlap) {
                    Swal.fire({
                        title: 'Overlaps ESSU grades',
                        text: err.data.message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Save anyway',
                        cancelButtonText: 'Go back',
                    }).then((r) => { if (r.isConfirmed) psrSave(true); });
                    return;
                }
                psrError(err);
            });
        }

        function psrReplace(record) {
            psrRecords = psrRecords.map((r) => r.id === record.id ? record : r);
            renderPsr();
        }

        function psrAddPages(recordId, input) {
            const data = new FormData();
            Array.from(input.files).forEach((f) => data.append('images[]', f));
            psrRequest(psrRoutes.addPages(recordId), 'POST', data)
                .then((res) => psrReplace(res.record))
                .catch(psrError)
                .finally(() => { input.value = ''; });
        }

        function psrDeletePage(pageId) {
            Swal.fire({ title: 'Remove this image?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Remove' })
                .then((r) => {
                    if (!r.isConfirmed) return;
                    const data = new FormData();
                    data.append('_method', 'DELETE');
                    psrRequest(psrRoutes.destroyPage(pageId), 'POST', data).then((res) => psrReplace(res.record)).catch(psrError);
                });
        }

        function psrDeleteRecord(recordId) {
            Swal.fire({ title: 'Delete this record and all its images?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete' })
                .then((r) => {
                    if (!r.isConfirmed) return;
                    const data = new FormData();
                    data.append('_method', 'DELETE');
                    psrRequest(psrRoutes.destroy(recordId), 'POST', data).then(() => {
                        psrRecords = psrRecords.filter((x) => x.id !== recordId);
                        renderPsr();
                    }).catch(psrError);
                });
        }

        function confirmUnfinalize(btn) {
            const form = btn.closest('form');
            const semName = form.dataset.semester;
            Swal.fire({
                title: 'Undo Finalization?',
                html: `This will revert all finalized grades for <strong>${semName}</strong> back to approved status.<br><br>Faculty will <strong>not</strong> be able to edit them — only the registrar can re-finalize.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Undo Finalize',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
