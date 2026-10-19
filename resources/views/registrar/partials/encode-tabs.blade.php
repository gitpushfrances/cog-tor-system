{{-- Encode Grades modes: one student's subjects, one subject's class list, or a whole class masterlist. --}}
@php
    $encodeTabs = [
        ['route' => 'registrar.encode-grades',            'icon' => 'fa-user-graduate', 'label' => 'By Student'],
        ['route' => 'registrar.encode-grades.by-subject', 'icon' => 'fa-book-open',     'label' => 'By Subject'],
        ['route' => 'registrar.encode-grades.masterlist', 'icon' => 'fa-table-cells',   'label' => 'Masterlist'],
    ];
@endphp
<div style="display:flex;gap:4px;border-bottom:1px solid #e2d9c8;">
    @foreach($encodeTabs as $tab)
        @php $current = request()->routeIs($tab['route']); @endphp
        <a href="{{ route($tab['route']) }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:0.85rem;font-weight:600;text-decoration:none;border-bottom:2px solid {{ $current ? '#c9a84c' : 'transparent' }};color:{{ $current ? '#c9a84c' : '#6b5f4a' }};margin-bottom:-1px;">
            <i class="fa-solid {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
        </a>
    @endforeach
</div>
