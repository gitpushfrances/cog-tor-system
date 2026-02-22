<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Generate TOR — {{ $student->getFullName() }}</h2>
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

                @if($hasFinalized)
                    <form method="POST" action="{{ route('registrar.students.tor.generate', $student) }}">
                        @csrf
                        <p class="mb-4 text-sm text-gray-600">This will generate a complete TOR covering all finalized semesters on record.</p>
                        <button type="submit"
                                onclick="return confirm('Generate TOR for {{ $student->getFullName() }}?')"
                                class="w-full px-4 py-2 font-semibold text-white transition bg-purple-600 rounded-lg hover:bg-purple-700">
                            Generate & Download TOR
                        </button>
                    </form>
                @else
                    <p class="text-center text-gray-500">No finalized grades found for this student.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
