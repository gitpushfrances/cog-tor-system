{{-- Student Information Sheet (ESSU-ACAD-203.b). Shared by the Add and Edit forms; $student is null when adding. --}}
@php
    $student ??= null;

    $sections = [
        'Personal Details' => ['civil_status' => 'Civil Status', 'home_phone' => 'Home Phone'],
        'Permanent Address' => [
            'address_street' => 'Street', 'address_barangay' => 'Barangay',
            'address_municipality' => 'City / Municipality', 'address_province' => 'Province', 'address_zip' => 'Zip Code',
        ],
        'Parents' => [
            'father_name' => 'Name of Father', 'father_contact' => 'Father\'s Contact No.',
            'mother_name' => 'Name of Mother', 'mother_contact' => 'Mother\'s Contact No.',
        ],
        'Guardian' => ['guardian_name' => 'Name of Guardian', 'guardian_contact' => 'Guardian\'s Contact No.', 'guardian_address' => 'Guardian\'s Address'],
        'Past Schools Attended' => null,
        'Person to Notify in Case of Emergency' => [
            'emergency_name' => 'Name', 'emergency_relation' => 'Relation',
            'emergency_address' => 'Address', 'emergency_contact' => 'Contact No.',
        ],
    ];

    $levels = [
        'Elementary' => 'elementary', 'Junior High School' => 'junior_high', 'Senior High School' => 'high_school',
        'College' => 'college', 'Graduate' => 'graduate', 'Post-Graduate' => 'post_graduate',
    ];
    $labels = ['school' => 'Name of School', 'address' => 'School Address', 'period' => 'Period of Attendance', 'honors' => 'Honors Received'];
    $choices = ['civil_status' => \App\Models\Student::CIVIL_STATUSES];
@endphp

<div class="pt-5 space-y-6 border-t border-gray-100">
    <div>
        <h4 class="text-sm font-semibold text-gray-800">Student Information Sheet</h4>
        <p class="mt-1 text-xs text-gray-500">All fields are required. Write N/A or None if not applicable.</p>
    </div>

    @foreach ($sections as $title => $fields)
        <fieldset class="space-y-4">
            <legend class="text-sm font-medium text-gray-700">{{ $title }}</legend>

            @if ($title === 'Permanent Address' && $student?->address && ! $student->address_street)
                <p class="px-3 py-2 text-xs text-amber-800 rounded bg-amber-50">Address on file: {{ $student->address }}. Enter it in the boxes below; this line is replaced when you save.</p>
            @endif

            @if ($fields === null)
                <p class="text-xs text-gray-500">Elementary and Senior High school names and years are entered above.</p>
                @foreach ($levels as $levelLabel => $prefix)
                    <div class="space-y-2">
                        <p class="text-xs font-semibold tracking-wide text-gray-500 uppercase">{{ $levelLabel }}</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ($labels as $part => $label)
                                @continue($part === 'school' && ! array_key_exists("{$prefix}_school", \App\Models\Student::SIS_FIELDS))
                                <x-sis-field :name="$prefix . '_' . $part" :label="$label" :student="$student" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($fields as $name => $label)
                        <x-sis-field :name="$name" :label="$label" :student="$student" :options="$choices[$name] ?? null" />
                    @endforeach
                </div>
            @endif
        </fieldset>
    @endforeach
</div>
