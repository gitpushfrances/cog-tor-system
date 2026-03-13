<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Semester Management</h2>
            <a href="{{ route('admin.semesters.create') }}"
                style="background:#c9a84c;color:#fff;padding:8px 18px;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;"
                onmouseover="this.style.background='#a8872e'" onmouseout="this.style.background='#c9a84c'">
                + Add Semester
            </a>
        </div>
    </x-slot>

    <div class="px-4 py-6 mx-auto max-w-7xl">

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-hidden bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Semester</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">School Year</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Period</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($semesters as $semester)
                    <tr>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">
                            {{ $semester->semester_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $semester->schoolYear->year_code ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($semester->start_date && $semester->end_date)
                                {{ $semester->start_date->format('M d, Y') }} — {{ $semester->end_date->format('M d, Y') }}
                            @else
                                <span class="italic text-gray-300">No dates set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $colors = [
                                    'active'    => 'background:#d1fae5;color:#065f46;',
                                    'completed' => 'background:#e0e7ff;color:#3730a3;',
                                    'upcoming'  => 'background:#fef3c7;color:#92400e;',
                                ];
                                $style = $colors[$semester->status] ?? 'background:#f3f4f6;color:#374151;';
                            @endphp
                            <span style="{{ $style }}padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;">
                                {{ ucfirst($semester->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div style="display:flex;align-items:center;gap:12px;">
                                @if($semester->status !== 'active')
                                    <form action="{{ route('admin.semesters.set-active', $semester) }}" method="POST"
                                        onsubmit="return confirm('Set {{ $semester->semester_name }} ({{ $semester->schoolYear->year_code ?? '' }}) as active? The current active semester will be marked completed.')">
                                        @csrf
                                        <button type="submit"
                                            style="font-size:0.78rem;color:#059669;font-weight:600;background:none;border:none;cursor:pointer;padding:0;"
                                            onmouseover="this.style.color='#047857'" onmouseout="this.style.color='#059669'">
                                            Set Active
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.semesters.edit', $semester) }}"
                                    style="font-size:0.78rem;color:#c9a84c;font-weight:600;text-decoration:none;"
                                    onmouseover="this.style.color='#a8872e'" onmouseout="this.style.color='#c9a84c'">
                                    Edit
                                </a>
                                <form action="{{ route('admin.semesters.destroy', $semester) }}" method="POST"
                                    onsubmit="return confirm('Delete this semester?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        style="font-size:0.78rem;color:#dc2626;font-weight:600;background:none;border:none;cursor:pointer;padding:0;"
                                        onmouseover="this.style.color='#b91c1c'" onmouseout="this.style.color='#dc2626'">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-sm text-center text-gray-400">
                            <i class="block mb-2 text-2xl fa-solid fa-calendar-xmark"></i>
                            No semesters found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $semesters->links() }}</div>
        </div>

    </div>
</x-app-layout>
