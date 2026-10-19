@php
    $card  = 'background:#fff;border:1px solid #e2d9c8;border-radius:12px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);';
    $input = 'width:100%;box-sizing:border-box;border:1px solid #d4c9b4;border-radius:8px;padding:9px 12px;font-size:0.9rem;outline:none;';
    $states = [
        'current'    => ['#065f46', '#ecfdf5', '#a7f3d0', 'fa-circle-check', 'VALID — CURRENT', 'This is the current Transcript of Records for this student. The code matches.'],
        'superseded' => ['#92400e', '#fffbeb', '#fcd34d', 'fa-clock-rotate-left', 'VALID — REPLACED', 'This TOR was really issued by the registrar, but a newer TOR has replaced it. Ask for the current one.'],
        'mismatch'   => ['#991b1b', '#fef2f2', '#fecaca', 'fa-circle-xmark', 'DOES NOT MATCH', 'The verify code does not match this document number. The document may be altered or not issued by this office.'],
        'not_found'  => ['#991b1b', '#fef2f2', '#fecaca', 'fa-circle-question', 'NOT FOUND', 'No TOR with this document number was issued by this system.'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:1.25rem;font-weight:700;color:#1a1a2e;">Verify Document</h2>
        <p style="font-size:0.85rem;color:#8a7a60;margin-top:2px;">Check a Transcript of Records using the QR text or the document number and verify code printed at the bottom of each page.</p>
    </x-slot>

    <div style="max-width:52rem;margin:0 auto;display:flex;flex-direction:column;gap:18px;">
        <form method="GET" action="{{ route('registrar.verify-document') }}" style="{{ $card }}display:flex;flex-direction:column;gap:12px;">
            <label style="font-size:0.75rem;font-weight:600;color:#4a4535;">QR text or document number
                <textarea name="q" rows="4" required placeholder="Paste the text read from the QR, or type the document number, e.g. TOR-6AD56F7E274FF"
                          style="{{ $input }}margin-top:4px;font-family:monospace;resize:vertical;">{{ request('q') }}</textarea>
            </label>
            <label style="font-size:0.75rem;font-weight:600;color:#4a4535;">Verify code <span style="font-weight:400;color:#8a7a60;">(not needed if the pasted QR text has it)</span>
                <input type="text" name="code" value="{{ request('code') }}" placeholder="e.g. B4ED2-5B9CB" maxlength="20" autocomplete="off"
                       style="{{ $input }}margin-top:4px;font-family:monospace;text-transform:uppercase;max-width:220px;">
            </label>
            <div>
                <button type="submit" style="background:#c9a84c;color:#fff;padding:9px 22px;border-radius:8px;font-size:0.875rem;font-weight:600;border:none;cursor:pointer;">
                    <i class="fa-solid fa-shield-halved"></i> Verify
                </button>
            </div>
        </form>

        @if($result)
            @php [$fg, $bg, $border, $icon, $title, $text] = $states[$result['status']]; $tor = $result['tor']; @endphp
            <div style="{{ $card }}background:{{ $bg }};border-color:{{ $border }};">
                <div style="display:flex;gap:14px;align-items:flex-start;">
                    <i class="fa-solid {{ $icon }}" style="font-size:2rem;color:{{ $fg }};"></i>
                    <div style="flex:1;">
                        <div style="font-size:1.1rem;font-weight:800;color:{{ $fg }};letter-spacing:0.03em;">{{ $title }}</div>
                        <div style="font-size:0.875rem;color:#374151;margin-top:2px;">{{ $text }}</div>

                        <table style="margin-top:14px;font-size:0.875rem;border-collapse:collapse;">
                            <tr><td style="padding:3px 16px 3px 0;color:#6b7280;">Document No.</td><td style="font-family:monospace;font-weight:700;">{{ $result['number'] ?: '—' }}</td></tr>
                            @if($tor && in_array($result['status'], ['current', 'superseded'], true))
                                <tr><td style="padding:3px 16px 3px 0;color:#6b7280;">Student</td><td>{{ $tor->student?->getFullName() }} ({{ $tor->student?->student_number }})</td></tr>
                                <tr><td style="padding:3px 16px 3px 0;color:#6b7280;">Issued</td><td>{{ $tor->generated_at?->format('F d, Y g:i A') }}</td></tr>
                                @if($result['status'] === 'superseded' && $result['newer'])
                                    <tr><td style="padding:3px 16px 3px 0;color:#6b7280;">Current TOR</td><td style="font-family:monospace;">{{ $result['newer']->document_number }} · {{ $result['newer']->generated_at?->format('F d, Y') }}</td></tr>
                                @endif
                            @endif
                        </table>

                        @if($tor && in_array($result['status'], ['current', 'superseded'], true))
                            <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                                <a href="{{ route('registrar.tor.preview', $tor) }}" target="_blank" style="font-size:0.8rem;font-weight:600;color:#4b6043;"><i class="fa-solid fa-file-pdf"></i> Open the issued PDF</a>
                                <a href="{{ route('registrar.students.profile', $tor->student_id) }}" style="font-size:0.8rem;font-weight:600;color:#4b6043;"><i class="fa-solid fa-user"></i> Student profile</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
