<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Department Management') }}
            </h2>
            <a href="{{ route('admin.departments.create') }}" class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                Add New Department
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Departments Table -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Courses</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($departments as $department)
                                <tr>
                                    <td class="px-6 py-4 font-semibold">{{ $department->code }}</td>
                                    <td class="px-6 py-4">{{ $department->name }}</td>
                                    <td class="px-6 py-4">{{ $department->courses_count }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $department->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($department->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.departments.edit', $department) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            @if($department->status === 'active')
                                            <form action="{{ route('admin.departments.deactivate', $department) }}" method="POST" class="inline" id="deactivate-{{ $department->id }}">
                                                @csrf
                                                <button type="button"
                                                    onclick="Swal.fire({title:'Deactivate Department?',text:'This will deactivate this department.',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'Yes, Deactivate'}).then(r=>{if(r.isConfirmed)document.getElementById('deactivate-{{ $department->id }}').submit()})"
                                                    class="text-red-600 hover:text-red-900">Deactivate</button>
                                            </form>
                                            @else
                                            <span class="text-xs text-gray-400">Inactive</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No departments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $departments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
