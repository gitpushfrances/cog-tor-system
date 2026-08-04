<div id="confirm-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-md p-6 bg-white shadow-xl rounded-xl" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
            <div class="flex items-start gap-4">
                <div id="confirm-modal-icon" class="flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-full"></div>
                <div class="flex-1 pt-1">
                    <h3 id="confirm-modal-title" class="text-base font-semibold text-gray-900"></h3>
                    <p id="confirm-modal-message" class="mt-1 text-sm leading-relaxed text-gray-500"></p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeConfirmModal()"
                    class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" id="confirm-modal-confirm-btn"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let __confirmModalForm = null;

    const __confirmModalIcons = {
        trash: {
            wrap: 'bg-red-100 text-red-600',
            svg: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>',
        },
        warning: {
            wrap: 'bg-amber-100 text-amber-600',
            svg: '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86l-8.18 14.14A1 1 0 003 19.5h18a1 1 0 00.89-1.5L13.71 3.86a1 1 0 00-1.72 0z" /></svg>',
        },
    };

    function openConfirmModal({ title, message, form, icon = 'trash' }) {
        __confirmModalForm = form;
        document.getElementById('confirm-modal-title').textContent = title;
        document.getElementById('confirm-modal-message').textContent = message;

        const iconEl = document.getElementById('confirm-modal-icon');
        const cfg = __confirmModalIcons[icon] ?? __confirmModalIcons.trash;
        iconEl.className = 'flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-full ' + cfg.wrap;
        iconEl.innerHTML = cfg.svg;

        document.getElementById('confirm-modal-confirm-btn').onclick = function () {
            __confirmModalForm.submit();
        };

        document.getElementById('confirm-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirm-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        __confirmModalForm = null;
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeConfirmModal();
    });
</script>
