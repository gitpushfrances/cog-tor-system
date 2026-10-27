{{-- Account name (Last / First / Middle) and sign-in username. Used by Add User and Edit User. --}}
@php
    $u      = $user ?? null;
    $field  = 'width:100%;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.875rem;outline:none;box-sizing:border-box;';
    $label  = 'display:block;font-size:0.8rem;font-weight:600;color:#4a4535;margin-bottom:6px;';
    $error  = 'color:#dc2626;font-size:0.75rem;margin-top:4px;';
@endphp
<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px;">
    @foreach(['last_name' => 'Last Name', 'first_name' => 'First Name', 'middle_name' => 'Middle Name'] as $name => $text)
        <div>
            <label for="{{ $name }}" style="{{ $label }}">{{ $text }} @if($name !== 'middle_name')<span style="color:#dc2626;">*</span>@endif</label>
            <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $u?->{$name}) }}" maxlength="100"
                   @if($name !== 'middle_name') required @endif style="{{ $field }}">
            @error($name)<p style="{{ $error }}">{{ $message }}</p>@enderror
        </div>
    @endforeach
</div>
<div style="margin-bottom:20px;">
    <label for="username" style="{{ $label }}">Username <span style="color:#dc2626;">*</span>
        <span style="color:#8a7a60;font-weight:400;">(used to sign in; letters, numbers, dot, dash or underscore)</span></label>
    <input type="text" name="username" id="username" value="{{ old('username', $u?->username) }}" required minlength="3" maxlength="50"
           pattern="[A-Za-z0-9._\-]+" autocomplete="off" autocapitalize="off" spellcheck="false" style="{{ $field }}text-transform:lowercase;">
    @error('username')<p style="{{ $error }}">{{ $message }}</p>@enderror
</div>
