{{-- Encode Grades modes: one student's subjects, or one subject's class list. --}}
@php $bySubject = request()->routeIs('registrar.encode-grades.by-subject'); @endphp
<div style="display:flex;gap:4px;border-bottom:1px solid #e2d9c8;">
    <a href="{{ route('registrar.encode-grades') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:0.85rem;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $bySubject ? 'transparent' : '#c9a84c' }};color:{{ $bySubject ? '#6b5f4a' : '#c9a84c' }};margin-bottom:-1px;">
        <i class="fa-solid fa-user-graduate"></i> By Student
    </a>
    <a href="{{ route('registrar.encode-grades.by-subject') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:0.85rem;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $bySubject ? '#c9a84c' : 'transparent' }};color:{{ $bySubject ? '#c9a84c' : '#6b5f4a' }};margin-bottom:-1px;">
        <i class="fa-solid fa-book-open"></i> By Subject
    </a>
</div>
