<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Backup &amp; Restore</h2>
    </x-slot>

    <div class="py6 mx-auto max-w-5xl px-4 space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Create Backup</h3>
            <p class="text-sm text-gray-500 mb-4">Creates a full backup of the database and stores it locally. This may take a few seconds.</p>
            <form method="POST" action="{{ route('admin.backup.run') }}" id="backup-form">
                @csrf
                <button type="button" id="backup-now-btn"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded">
                    Backup Now
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Backup History</h3>
            @if(count($backups) === 0)
                <p class="text-sm text-gray-400">No backups found.</p>
            @else
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">Filename</th>
                            <th class="px-4 py-2">Size</th>
                            <th class="px-4 py-2">Created</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($backups as $backup)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-xs">{{ $backup['name'] }}</td>
                            <td class="px-4 py-2">{{ $backup['size'] }}</td>
                            <td class="px-4 py-2">{{ $backup['created'] }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                <a href="{{ route('admin.backup.download', $backup['name']) }}"
                                   class="text-blue-600 hover:underline text-xs">Download</a>
                                <form method="POST" action="{{ route('admin.backup.delete', $backup['name']) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete this backup?')"
                                        class="text-red-500 hover:underline text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Restore Database</h3>
            <p class="text-sm text-red-500 mb-4"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Warning: This will overwrite the current database. Upload a valid .sql file exported from this system only.</p>
            <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="sql_file" accept=".sql,.txt"
                    class="block text-sm text-gray-600 border border-gray-300 rounded px-3 py-2 w-full max-w-md">
                @error('sql_file')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                <button type="submit"
                    onclick="return confirm('Are you sure? This will overwrite the current database.')"
                    class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2 rounded">
                    Restore Now
                </button>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        document.getElementById('backup-now-btn').addEventListener('click', function () {
            Swal.fire({
                title: 'Run backup now?',
                text: 'This will create a full backup of the database.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, backup now',
                confirmButtonColor: '#2563eb',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Backing up...',
                        text: 'Please wait, this may take a few seconds.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('backup-form').submit();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
