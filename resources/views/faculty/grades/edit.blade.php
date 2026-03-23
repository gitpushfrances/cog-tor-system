<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.subjects.grades', $subject) }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Grades</a>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Grade</h2>
    </x-slot>
    <div class="max-w-xl px-4 py-6 mx-auto">
        <div class="p-6 bg-white rounded-lg shadow">
            <div class="p-4 mb-4 rounded bg-gray-50">
                <p class="text-sm text-gray-600">Student: <span class="font-semibold">{{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</span></p>
                <p class="text-sm text-gray-600">Subject: <span class="font-semibold">{{ $subject->code }} - {{ $subject->name }}</span></p>
            </div>
            <form action="{{ route('faculty.subjects.grades.update', [$subject, $grade]) }}" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Grade</label>
                        <input type="text"
                               name="grade"
                               id="grade-input"
                               value="{{ old('grade', number_format((float)$grade->grade, 2)) }}"
                               list="grade-options"
                               placeholder="e.g. 2.00"
                               autocomplete="off"
                               class="block w-full px-3 py-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
                        <datalist id="grade-options">
                            <option value="1.00"><option value="1.25"><option value="1.50">
                            <option value="1.75"><option value="2.00"><option value="2.25">
                            <option value="2.50"><option value="2.75"><option value="3.00">
                            <option value="5.00">
                        </datalist>
                        @error('grade')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remarks <span class="text-gray-400">(optional)</span></label>
                        <textarea name="remarks" rows="2" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">{{ old('remarks', $grade->remarks) }}</textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Update Grade</button>
                    <a href="{{ route('faculty.subjects.grades', $subject) }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
