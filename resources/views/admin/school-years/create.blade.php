<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Add School Year</h2>
    </x-slot>
    <div class="max-w-xl px-4 py-6 mx-auto">
        <div class="p-6 bg-white rounded-lg shadow">
            <form action="{{ route('admin.school-years.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year Start</label>
                        <input type="number" name="year_start" value="{{ old('year_start') }}"
                            placeholder="e.g. 2026"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('year_start')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year End</label>
                        <input type="number" name="year_end" value="{{ old('year_end') }}"
                            placeholder="e.g. 2027"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        @error('year_end')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit"
                        style="background:#c9a84c;color:#fff;padding:9px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;"
                        onmouseover="this.style.background='#a8872e'" onmouseout="this.style.background='#c9a84c'">
                        Save
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
</x-app-layout>
