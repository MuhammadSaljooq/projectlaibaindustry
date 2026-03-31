(function () {
    let pendingForm = null;

    function ensureDeleteModal() {
        let root = document.getElementById('delete-confirm-modal');
        if (root) {
            return root;
        }

        root = document.createElement('div');
        root.id = 'delete-confirm-modal';
        root.setAttribute('role', 'presentation');
        root.style.cssText =
            'display:none;position:fixed;inset:0;z-index:100000;align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,0.45);font-family:Inter,system-ui,sans-serif;';

        root.innerHTML =
            '<div role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title" tabindex="-1" ' +
            'style="max-width:26rem;width:100%;background:#fff;border:1px solid #abb3b7;box-shadow:0 12px 40px rgba(0,0,0,0.12);">' +
            '<div style="padding:1.25rem 1.25rem 0;">' +
            '<p id="delete-confirm-title" style="margin:0;font-size:10px;font-weight:800;letter-spacing:0.2em;text-transform:uppercase;color:#586064;">Confirm delete</p>' +
            '<p id="delete-confirm-message" style="margin:0.75rem 0 0;font-size:0.9375rem;line-height:1.45;color:#2b3437;"></p>' +
            '</div>' +
            '<div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:flex-end;padding:1rem 1.25rem 1.25rem;border-top:1px solid #abb3b7;background:#f8f9fa;">' +
            '<button type="button" id="delete-confirm-cancel" style="min-height:2.5rem;padding:0 1rem;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;border:1px solid #5e5e5e;background:transparent;color:#5e5e5e;cursor:pointer;">Cancel</button>' +
            '<button type="button" id="delete-confirm-ok" style="min-height:2.5rem;padding:0 1rem;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;border:0;background:#9f403d;color:#f8f8f8;cursor:pointer;">Delete</button>' +
            '</div></div>';

        document.body.appendChild(root);

        const panel = root.querySelector('[role="dialog"]');
        const msgEl = root.querySelector('#delete-confirm-message');
        const cancel = root.querySelector('#delete-confirm-cancel');
        const ok = root.querySelector('#delete-confirm-ok');

        function close() {
            root.style.display = 'none';
            pendingForm = null;
            document.body.style.overflow = '';
            cancel.removeEventListener('click', onCancel);
            ok.removeEventListener('click', onOk);
            root.removeEventListener('click', onBackdrop);
            document.removeEventListener('keydown', onKey);
        }

        function onCancel() {
            close();
        }

        function onOk() {
            const form = pendingForm;
            close();
            if (form && form instanceof HTMLFormElement) {
                form.dataset.deleteConfirmBypass = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        }

        function onBackdrop(e) {
            if (e.target === root) {
                onCancel();
            }
        }

        function onKey(e) {
            if (e.key === 'Escape') {
                onCancel();
            }
        }

        root._openDeleteModal = function (form, message) {
            pendingForm = form;
            msgEl.textContent = message || 'Delete this item?';
            root.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            cancel.addEventListener('click', onCancel);
            ok.addEventListener('click', onOk);
            root.addEventListener('click', onBackdrop);
            document.addEventListener('keydown', onKey);
            panel.focus();
        };

        return root;
    }

    document.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const message = form.getAttribute('data-confirm-delete');
            if (!message) {
                return;
            }

            if (form.dataset.deleteConfirmBypass === '1') {
                delete form.dataset.deleteConfirmBypass;
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            ensureDeleteModal()._openDeleteModal(form, message);
        },
        true
    );

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            const targetId = button.getAttribute('data-password-toggle');
            if (!targetId) {
                return;
            }

            const input = document.getElementById(targetId);
            if (!input) {
                return;
            }

            button.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                const icon = button.querySelector('.material-symbols-outlined');
                if (icon) {
                    icon.textContent = isPassword ? 'visibility_off' : 'visibility';
                }
            });
        });
    });
})();
