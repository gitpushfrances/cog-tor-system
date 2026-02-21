<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Semester Management</h2>
            <a href="{{ route('admin.semesters.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Semester</a>
        </div>
    </x-slot>
    <div class="py-6 max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">School Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($semesters as $semester)
                    <tr>
                        <td class="px-6 py-4 text-sm font-semibold">{{ $semester->name }} Semester</td>
                        <td class="px-6 py-4 text-sm">{{ $semester->schoolYear->year_start ?? '-' }}-{{ $semester->schoolYear->year_end ?? '' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $semester->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($semester->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm flex gap-2">
                            @if($semester->status !== 'active')
                                <form action="{{ route('admin.semesters.set-active', $semester) }}" method="POST">
                                    @csrf
                                    <button class="text-green-600 hover:underline">Set Active</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.semesters.edit', $semester) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.semesters.destroy', $semester) }}" method="POST" onsubmit="return confirm('Delete this semester?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-400">No semesters found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $semesters->links() }}</div>
        </div>
    </div>
</x-app-layout>
