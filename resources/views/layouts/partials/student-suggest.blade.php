{{--
    Type-ahead student search (client C3). Any input with data-student-suggest="<suggest URL>"
    shows matching students while typing (2+ characters).
      data-suggest-mode="submit"  (default) picking a student puts the student number in the box and submits the form
      data-suggest-mode="profile" picking a student opens the student's profile
    Arrow keys + Enter pick a suggestion; Enter with nothing highlighted submits the form as typed; Esc closes.
--}}
<style>
    .ss-box { position:absolute; z-index:60; left:0; right:0; top:calc(100% + 4px); background:#fff; border:1px solid #e2d9c8;
              border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); overflow:hidden; max-height:340px; overflow-y:auto; }
    .ss-item { display:flex; justify-content:space-between; gap:12px; padding:8px 12px; cursor:pointer; font-size:0.85rem; color:#1a1a2e; text-align:left; }
    .ss-item + .ss-item { border-top:1px solid #f5f0e8; }
    .ss-item.ss-active, .ss-item:hover { background:#fef3e2; }
    .ss-item small { color:#8a7a60; font-size:0.75rem; white-space:nowrap; }
    .ss-empty { padding:10px 12px; font-size:0.8rem; color:#9ca3af; }
</style>
<script>
(function () {
    function attach(input) {
        if (input.dataset.ssReady) return;
        input.dataset.ssReady = '1';
        input.setAttribute('autocomplete', 'off');

        const holder = document.createElement('div');
        holder.style.position = 'relative';
        holder.style.flex = getComputedStyle(input).flex;
        holder.style.minWidth = getComputedStyle(input).minWidth;
        input.parentNode.insertBefore(holder, input);
        holder.appendChild(input);
        input.style.width = '100%';
        input.style.boxSizing = 'border-box';

        const box = document.createElement('div');
        box.className = 'ss-box';
        box.style.display = 'none';
        holder.appendChild(box);

        let items = [], active = -1, timer = null, controller = null;

        const close = () => { box.style.display = 'none'; active = -1; };
        const highlight = (i) => {
            active = i;
            box.querySelectorAll('.ss-item').forEach((el, n) => el.classList.toggle('ss-active', n === i));
        };
        const pick = (s) => {
            close();
            if (input.dataset.suggestMode === 'profile') {
                window.location.href = s.profile_url;
                return;
            }
            input.value = s.number;
            if (input.form) input.form.submit();
        };
        const render = () => {
            box.innerHTML = '';
            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'ss-empty';
                empty.textContent = 'No matching students.';
                box.appendChild(empty);
            }
            items.forEach((s, i) => {
                const row = document.createElement('div');
                row.className = 'ss-item';
                const left = document.createElement('span');
                left.textContent = s.name;
                const right = document.createElement('small');
                right.textContent = s.number + ' · ' + s.course + (s.status !== 'active' ? ' · ' + s.status : '');
                row.append(left, right);
                row.addEventListener('mousedown', (e) => { e.preventDefault(); pick(s); });
                row.addEventListener('mousemove', () => highlight(i));
                box.appendChild(row);
            });
            box.style.display = 'block';
            active = -1;
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { close(); return; }
            timer = setTimeout(() => {
                if (controller) controller.abort();
                controller = new AbortController();
                fetch(input.dataset.studentSuggest + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json' }, signal: controller.signal,
                })
                    .then((r) => r.ok ? r.json() : [])
                    .then((data) => { if (input.value.trim() === q) { items = data; render(); } })
                    .catch(() => {});
            }, 200);
        });
        input.addEventListener('keydown', (e) => {
            if (box.style.display === 'none') return;
            if (e.key === 'ArrowDown') { e.preventDefault(); highlight(Math.min(active + 1, items.length - 1)); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(Math.max(active - 1, 0)); }
            else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); pick(items[active]); }
            else if (e.key === 'Escape') { close(); }
        });
        input.addEventListener('blur', () => setTimeout(close, 150));
    }

    document.querySelectorAll('input[data-student-suggest]').forEach(attach);
})();
</script>
