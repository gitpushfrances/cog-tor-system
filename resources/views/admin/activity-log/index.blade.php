@php
    use App\Http\Controllers\Admin\ActivityLogController as Log;
    $label = fn ($v) => ucwords(str_replace('_', ' ', (string) $v));
    $badge = [
        'created' => ['#d1fae5', '#065f46'], 'updated' => ['#dbeafe', '#1e40af'], 'deleted' => ['#fee2e2', '#991b1b'],
        'generated' => ['#ede9fe', '#5b21b6'], 'restored' => ['#fef3c7', '#92400e'], 'login' => ['#f3f4f6', '#374151'],
        'logout' => ['#f3f4f6', '#374151'], 'failed_login' => ['#fee2e2', '#991b1b'], 'password_failed' => ['#fee2e2', '#991b1b'],
    ];
    $field = 'width:100%;border:1px solid #d1d5db;border-radius:6px;padding:7px 10px;font-size:0.85rem;background:#fff;';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Activity Log</h2>
        <p class="mt-1 text-sm text-gray-500">Who changed what and when: records, documents, backups and sign-ins. Read-only.</p>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <form method="GET" action="{{ route('admin.activity-log.index') }}"
                  style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:end;margin-bottom:16px;padding:16px;background:#fff;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.06);">
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#4b5563;margin-bottom:4px;">User</label>
                    <select name="user" style="{{ $field }}">
                        <option value="">All users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['user'] ?? '') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#4b5563;margin-bottom:4px;">Record type</label>
                    <select name="type" style="{{ $field }}">
                        <option value="">All types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $label($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#4b5563;margin-bottom:4px;">Action</label>
                    <select name="event" style="{{ $field }}">
                        <option value="">All actions</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ $label($event) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#4b5563;margin-bottom:4px;">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" style="{{ $field }}">
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#4b5563;margin-bottom:4px;">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" style="{{ $field }}">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" style="padding:8px 16px;font-size:0.85rem;font-weight:600;color:#fff;background:#c9a84c;border:none;border-radius:6px;cursor:pointer;">Filter</button>
                    @if(array_filter($filters))
                        <a href="{{ route('admin.activity-log.index') }}" style="padding:8px 12px;font-size:0.85rem;color:#4b5563;text-decoration:underline;">Clear</a>
                    @endif
                </div>
            </form>

            <div style="background:#fff;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,.06);overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                    <thead>
                        <tr style="background:#f9fafb;text-align:left;font-size:0.7rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">
                            <th style="padding:10px 14px;">When</th>
                            <th style="padding:10px 14px;">Who</th>
                            <th style="padding:10px 14px;">Action</th>
                            <th style="padding:10px 14px;">Record</th>
                            <th style="padding:10px 14px;">Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            @php
                                $rows = Log::changes($activity);
                                [$bg, $fg] = $badge[$activity->event] ?? ['#f3f4f6', '#374151'];
                            @endphp
                            <tr style="border-top:1px solid #f3f4f6;vertical-align:top;">
                                <td style="padding:10px 14px;white-space:nowrap;color:#374151;">
                                    {{ $activity->created_at->format('M d, Y') }}<br>
                                    <span style="font-size:0.75rem;color:#9ca3af;">{{ $activity->created_at->format('g:i:s A') }}</span>
                                </td>
                                <td style="padding:10px 14px;color:#111827;">{{ $activity->causer?->name ?? 'System / guest' }}</td>
                                <td style="padding:10px 14px;">
                                    <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:0.7rem;font-weight:700;background:{{ $bg }};color:{{ $fg }};">{{ $label($activity->event ?? 'event') }}</span>
                                    <div style="margin-top:4px;font-size:0.75rem;color:#6b7280;">{{ $activity->description }}</div>
                                </td>
                                <td style="padding:10px 14px;">
                                    <div style="font-size:0.7rem;font-weight:600;color:#9ca3af;text-transform:uppercase;">{{ $label($activity->log_name) }}</div>
                                    <div style="color:#111827;">{{ Log::recordLabel($activity) }}</div>
                                </td>
                                <td style="padding:10px 14px;min-width:240px;">
                                    @if(empty($rows))
                                        <span style="color:#9ca3af;">—</span>
                                    @else
                                        @foreach(array_slice($rows, 0, 2) as [$name, $old, $new])
                                            <div style="font-size:0.78rem;color:#374151;">
                                                <strong>{{ $name }}:</strong>
                                                @if($old !== null && $new !== null) {{ \Illuminate\Support\Str::limit($old, 40) }} <i class="fa-solid fa-arrow-right" style="font-size:0.7em;color:#9ca3af;"></i> {{ \Illuminate\Support\Str::limit($new, 40) }}
                                                @else {{ \Illuminate\Support\Str::limit($new ?? $old, 60) }}
                                                @endif
                                            </div>
                                        @endforeach
                                        @if(count($rows) > 2)
                                            <details style="margin-top:4px;">
                                                <summary style="cursor:pointer;font-size:0.75rem;color:#4b6043;">All {{ count($rows) }} fields</summary>
                                                <table style="margin-top:6px;font-size:0.75rem;border-collapse:collapse;">
                                                    @foreach($rows as [$name, $old, $new])
                                                        <tr>
                                                            <td style="padding:2px 8px 2px 0;font-weight:600;color:#374151;white-space:nowrap;">{{ $name }}</td>
                                                            <td style="padding:2px 8px;color:#991b1b;">{{ $old ?? '' }}</td>
                                                            <td style="padding:2px 8px;color:#065f46;">{{ $new ?? '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </details>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="padding:24px;text-align:center;color:#9ca3af;">No activity found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;">{{ $activities->links() }}</div>
        </div>
    </div>
</x-app-layout>
