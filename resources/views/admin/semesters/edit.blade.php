<x-app-layout>
    <x-slot name="header">
        <a href="{{ url()->previous() }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Semester</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('admin.semesters.update', $semester) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">School Year</label>
                        <select name="school_year_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Select School Year --</option>
                            @foreach($schoolYears as $sy)
                                <option value="{{ $sy->id }}" {{ old('school_year_id', $semester->school_year_id) == $sy->id ? 'selected' : '' }}>
                                    {{ $sy->year_start }}-{{ $sy->year_end }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_year_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1st" {{ old('name', $semester->name) == '1st' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd" {{ old('name', $semester->name) == '2nd' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ old('name', $semester->name) == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="inactive" {{ old('status', $semester->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="active" {{ old('status', $semester->status) == 'active' ? 'selected' : '' }}>Active</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
                    <a href="{{ route('admin.semesters.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
