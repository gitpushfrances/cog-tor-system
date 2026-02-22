<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Generate COG — {{ $student->getFullName() }}</h2>
            <a href="{{ route('registrar.students') }}" class="text-sm text-gray-600 hover:text-gray-900">← Back to Students</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <div class="mb-6 text-sm text-gray-600">
                    <p><span class="font-medium">Student:</span> {{ $student->getFullName() }}</p>
                    <p><span class="font-medium">Student No.:</span> {{ $student->student_number }}</p>
                    <p><span class="font-medium">Course:</span> {{ $student->course->name ?? 'N/A' }}</p>
                </div>

                @if($semesters->isEmpty())
                    <p class="text-center text-gray-500">No finalized grades found for this student.</p>
                @else
                    <form method="POST" action="{{ route('registrar.students.cog.generate', $student) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block mb-1 text-sm font-medium text-gray-700">Select Semester <span class="text-red-500">*</span></label>
                            <select name="semester_id" required
                                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Select Semester --</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                            @error('semester_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 font-semibold text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                            Generate & Download COG
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
