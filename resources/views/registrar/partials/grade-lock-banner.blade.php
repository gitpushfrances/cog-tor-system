{{-- Shown when the selected semester belongs to a closed (not active) school year. --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#fef3e2;border:1px solid #f5d9a8;color:#92610c;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
    <div>
        <i class="fa-solid fa-lock" style="margin-right:6px;"></i>
        <strong>SY {{ $schoolYear }} is closed.</strong> Existing grades are locked. Missing grades can still be encoded.
    </div>
    <button type="button"
            onclick="document.querySelectorAll('input[data-locked]').forEach((input) => { input.readOnly = false; input.style.background = '#fffbeb'; }); this.remove();"
            style="background:#fff;border:1px solid #c9a84c;color:#92610c;padding:6px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;cursor:pointer;white-space:nowrap;">
        <i class="fa-solid fa-pen" style="margin-right:4px;"></i> Correct past grades
    </button>
</div>
