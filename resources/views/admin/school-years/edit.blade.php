<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit School Year</h2>
    </x-slot>

    <div class="max-w-xl px-4 py-6 mx-auto">
        <div class="p-6 bg-white rounded-lg shadow">
            <form id="school-year-form" action="{{ route('admin.school-years.update', $schoolYear) }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year Start</label>
                        <input type="number" name="year_start"
                            value="{{ old('year_start', explode('-', $schoolYear->year_code)[0] ?? '') }}"
                            placeholder="e.g. 2026"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('year_start')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year End</label>
                        <input type="number" name="year_end"
                            value="{{ old('year_end', explode('-', $schoolYear->year_code)[1] ?? '') }}"
                            placeholder="e.g. 2027"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('year_end')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status-select" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="upcoming" {{ old('status', $schoolYear->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="active" {{ old('status', $schoolYear->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status', $schoolYear->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="handleSubmit()"
                        style="background:#c9a84c;color:#fff;padding:9px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;"
                        onmouseover="this.style.background='#a8872e'" onmouseout="this.style.background='#c9a84c'">
                        Update
                    </button>
                    <a href="{{ route('admin.school-years.index') }}"
                        style="background:#f5f0e8;color:#4a4535;padding:9px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;border:1px solid #e2d9c8;"
                        onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div id="confirm-modal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,0.4);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:28px;max-width:420px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="background:#fef3c7;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#d97706;font-size:1.1rem;"></i>
                </div>
                <div>
                    <h3 style="font-size:1rem;font-weight:700;color:#1a1a2e;margin:0;">Activate School Year?</h3>
                    <p style="font-size:0.8rem;color:#8a7a60;margin:2px 0 0;">This will deactivate all other school years.</p>
                </div>
            </div>
            <p style="font-size:0.875rem;color:#4a4535;margin-bottom:24px;">
                You are setting <strong id="modal-year-label"></strong> as the active school year.
                Any currently active school year will be automatically set to inactive.
            </p>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button onclick="closeModal()"
                    style="background:#f5f0e8;color:#4a4535;padding:8px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:1px solid #e2d9c8;cursor:pointer;"
                    onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                    Cancel
                </button>
                <button onclick="confirmSubmit()"
                    style="background:#c9a84c;color:#fff;padding:8px 20px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;"
                    onmouseover="this.style.background='#a8872e'" onmouseout="this.style.background='#c9a84c'">
                    Yes, Activate
                </button>
            </div>
        </div>
    </div>

    <script>
    const currentStatus = '{{ $schoolYear->status }}';

    function handleSubmit() {
        const selected = document.getElementById('status-select').value;
        const yearStart = document.querySelector('[name="year_start"]').value;
        const yearEnd   = document.querySelector('[name="year_end"]').value;

        if (selected === 'active' && currentStatus !== 'active') {
            document.getElementById('modal-year-label').textContent = yearStart + '–' + yearEnd;
            document.getElementById('confirm-modal').style.display = 'flex';
        } else {
            document.getElementById('school-year-form').submit();
        }
    }

    function confirmSubmit() {
        document.getElementById('confirm-modal').style.display = 'none';
        document.getElementById('school-year-form').submit();
    }

    function closeModal() {
        document.getElementById('confirm-modal').style.display = 'none';
    }

    // Close on backdrop click
    document.getElementById('confirm-modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    </script>
</x-app-layout>
