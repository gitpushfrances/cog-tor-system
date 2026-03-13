<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">School Year Management</h2>
            <a href="{{ route('admin.school-years.create') }}" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">+ Add School Year</a>
        </div>
    </x-slot>
    <div class="px-4 py-6 mx-auto max-w-7xl">
        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 mb-4 text-red-800 bg-red-100 rounded">{{ session('error') }}</div>
        @endif
        <div class="overflow-hidden bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">School Year</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Semesters</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($schoolYears as $sy)
                    <tr>
                        <td class="px-6 py-4 text-sm font-semibold">{{ $sy->year_code }}</td>
                        <td class="px-6 py-4 text-sm">{{ $sy->semesters_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sy->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($sy->status) }}
                            </span>
                        </td>
                        <td class="flex gap-2 px-6 py-4 text-sm">
                            @if($sy->status !== 'active')
                                <form action="{{ route('admin.school-years.set-active', $sy) }}" method="POST">
                                    @csrf
                                    <button class="text-green-600 hover:underline">Set Active</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.school-years.edit', $sy) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.school-years.destroy', $sy) }}" method="POST" onsubmit="return confirm('Delete this school year?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-400">No school years found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $schoolYears->links() }}</div>
        </div>
    </div>
</x-app-layout>
