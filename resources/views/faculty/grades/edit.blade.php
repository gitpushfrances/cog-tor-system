<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.subjects.grades', $subject) }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Grades</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Grade</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4 p-4 bg-gray-50 rounded">
                <p class="text-sm text-gray-600">Student: <span class="font-semibold">{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</span></p>
                <p class="text-sm text-gray-600">Subject: <span class="font-semibold">{{ $subject->code }} - {{ $subject->name }}</span></p>
            </div>
            <form action="{{ route('faculty.subjects.grades.update', [$subject, $grade]) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Percentage Score (%)</label>
                        <input type="number" name="percentage" value="{{ old('percentage', $grade->percentage) }}"
                               min="0" max="100" step="0.01"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('percentage')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        <p class="text-xs text-gray-400 mt-1">Grade will be auto-computed from percentage.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remarks <span class="text-gray-400">(optional)</span></label>
                        <textarea name="remarks" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('remarks', $grade->remarks) }}</textarea>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Grade</button>
                    <a href="{{ route('faculty.subjects.grades', $subject) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
