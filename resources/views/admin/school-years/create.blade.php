<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add School Year</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('admin.school-years.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year Start</label>
                        <input type="number" name="year_start" value="{{ old('year_start') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('year_start')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year End</label>
                        <input type="number" name="year_end" value="{{ old('year_end') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('year_end')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="inactive">Inactive</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                    <a href="{{ route('admin.school-years.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
