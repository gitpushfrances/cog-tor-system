{{-- Shown when the selected semester is closed (not the active semester of the active school year). --}}
<div style="display:flex;align-items:flex-start;gap:10px;background:#f3f4f6;border:1px solid #d1d5db;color:#374151;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
    <i class="fa-solid fa-lock" style="margin-top:2px;color:#6b7280;"></i>
    <div>
        <strong>{{ $semester->semester_name }} {{ $semester->schoolYear?->year_code }} is closed.</strong>
        Grades are view only. Grades can be entered or changed only in the active semester of the active school year.
    </div>
</div>
<style>
    input[type="number"]:disabled { background: #f3f4f6 !important; color: #4b5563; cursor: not-allowed; }
    input[type="number"]:disabled::placeholder { color: transparent; }
</style>
