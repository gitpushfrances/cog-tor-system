<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('registrar.dashboard') }}" class="inline-block mb-2 text-sm text-indigo-600 hover:underline">← Back to Dashboard</a>
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
            <div>
                <form method="POST" action="{{ route('registrar.students.tor.generate', $student) }}" id="torForm">
                    @csrf
                    <button type="button" onclick="confirmTor()"
                            class="inline-flex items-center gap-2 px-5 py-2 mt-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="mr-1 fa-regular fa-file-lines"></i> Generate TOR
                    </button>
                </form>
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
                <div class="flex justify-center p-4 overflow-y-auto bg-gray-100 border border-gray-200 rounded-lg" style="max-height: 62vh;">
                    <iframe id="cogPreviewFrame"
                            class="bg-white border border-gray-200 rounded shadow-sm shrink-0"
                            style="width: 100%; max-width: 620px; aspect-ratio: 8.5 / 11;"></iframe>
                </div>
            `;
            document.getElementById('cogPreviewFrame').srcdoc = html;

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

        function confirmTor() {
            Swal.fire({
                title: 'Generate TOR?',
                text: 'Generate complete TOR for {{ $student->getFullName() }}? This includes all finalized semesters.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Generate TOR',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('torForm').submit();
                }
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
