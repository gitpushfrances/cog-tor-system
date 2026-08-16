<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Document Signatory Settings</h2>
        <p class="text-sm text-gray-500">Names and titles printed on generated COG and TOR documents</p>
    </x-slot>

    <div class="max-w-3xl px-4 py-6 mx-auto">

        @if(session('success'))
            <div class="px-4 py-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.document-settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="p-6 bg-white border-l-4 border-indigo-500 rounded-lg shadow">
                <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-500 uppercase">Registrar (Checked By)</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Name</label>
                        <input type="text" name="registrar_name" value="{{ old('registrar_name', $settings->registrar_name) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Credentials</label>
                        <input type="text" name="registrar_credentials" value="{{ old('registrar_credentials', $settings->registrar_credentials) }}"
                               placeholder="e.g. CPA, DBA" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Title</label>
                        <input type="text" name="registrar_title" value="{{ old('registrar_title', $settings->registrar_title) }}"
                               placeholder="e.g. Registrar III" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" required>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 border-pink-500 rounded-lg shadow">
                <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-500 uppercase">Prepared By (Records Staff)</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Name</label>
                        <input type="text" name="prepared_by_name" value="{{ old('prepared_by_name', $settings->prepared_by_name) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Title</label>
                        <input type="text" name="prepared_by_title" value="{{ old('prepared_by_title', $settings->prepared_by_title) }}"
                               placeholder="e.g. Administrative Aide VI" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border-l-4 rounded-lg shadow border-cyan-500">
                <h3 class="mb-4 text-sm font-semibold tracking-wider text-gray-500 uppercase">Campus Administrator</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Name</label>
                        <input type="text" name="campus_admin_name" value="{{ old('campus_admin_name', $settings->campus_admin_name) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">Title</label>
                        <input type="text" name="campus_admin_title" value="{{ old('campus_admin_title', $settings->campus_admin_title) }}"
                               placeholder="e.g. Campus Administrator" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
