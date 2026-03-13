<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Add Semester</h2>
    </x-slot>
    <div class="max-w-xl px-4 py-6 mx-auto">
        <div class="p-6 bg-white rounded-lg shadow">

            @if(session('error'))
                <div class="px-4 py-3 mb-4 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.semesters.store') }}" method="POST">
                @csrf
                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">School Year</label>
                        <select name="school_year_id" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select School Year --</option>
                            @foreach($schoolYears as $sy)
                                <option value="{{ $sy->id }}" {{ old('school_year_id') == $sy->id ? 'selected' : '' }}>
                                    {{ $sy->year_code }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_year_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="semester_order" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select Semester --</option>
                            <option value="1" {{ old('semester_order') == '1' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2" {{ old('semester_order') == '2' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="3" {{ old('semester_order') == '3' ? 'selected' : '' }}>Summer</option>
                        </select>
                        @error('semester_order')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            @error('start_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            @error('end_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
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
                    <a href="{{ route('admin.semesters.index') }}"
                        style="background:#f5f0e8;color:#4a4535;padding:9px 24px;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none;border:1px solid #e2d9c8;"
                        onmouseover="this.style.background='#e2d9c8'" onmouseout="this.style.background='#f5f0e8'">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
