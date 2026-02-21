<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('faculty.dashboard') }}" class="inline-block mb-2 text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Assigned Subjects</h2>
    </x-slot>
    <div class="py-6 max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($subjects as $subject)
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">{{ $subject->code }}</h3>
                        <p class="text-sm text-gray-600">{{ $subject->name }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        {{ $subject->units }} units
                    </span>
                </div>
                <div class="text-sm text-gray-500 mb-4">
                    <p>{{ $subject->course->code ?? '-' }} &bull; Year {{ $subject->year_level }} &bull; {{ $subject->semester }} Sem</p>
                    <p class="mt-1">{{ $subject->enrollments->count() }} enrolled students</p>
                    <p class="mt-1">{{ $subject->enrollments->filter(fn($e) => $e->grade)->count() }} grades encoded</p>
                </div>
                <a href="{{ route('faculty.subjects.grades', $subject) }}"
                   class="block text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                    Manage Grades
                </a>
            </div>
            @empty
            <div class="col-span-3 bg-white shadow rounded-lg p-12 text-center text-gray-400">
                No subjects assigned to you yet. Contact your administrator.
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
