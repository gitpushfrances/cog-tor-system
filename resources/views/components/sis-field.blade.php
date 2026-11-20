@props(['name', 'label', 'student' => null, 'options' => null])

@php
    $value  = old($name, $student?->{$name});
    $max    = \App\Models\Student::SIS_FIELDS[$name] ?? null;
    $border = $errors->has($name) ? 'border-red-400' : 'border-gray-300';
    $class  = "w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-1 focus:ring-blue-500 {$border}";
@endphp

<div {{ $attributes }}>
    <label for="{{ $name }}" class="block mb-1 text-sm font-medium text-gray-700">{{ $label }} <span class="text-red-500">*</span></label>
    @if ($options)
        <select id="{{ $name }}" name="{{ $name }}" required class="{{ $class }}">
            <option value="">Select</option>
            @foreach ($options as $option)
                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
            @endforeach
        </select>
    @else
        <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" maxlength="{{ $max }}" required class="{{ $class }}">
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
