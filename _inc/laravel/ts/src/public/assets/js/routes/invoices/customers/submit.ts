/**
 * @fileoverview TypeScript version of public/assets/js/routes/invoices/customers/submit.js
 * @generated from original JavaScript - manual review recommended
 * @module submit
 */

((): void => {
                                    const form = document.getElementById('customer_submit');
                                    if (!form || form.getAttribute('data-listener-active') === 'true') return;
                                    form.setAttribute('data-listener-active', 'true');

                                    form.addEventListener('submit', event => {
                                        try {
                                            const url = form.getAttribute('data-url') ?? '#';
                                            if (url !== '#') return;
                                            event.preventDefault();

                                            const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                            let container       = document.getElementById('toast-container');
                                            if (!container) {
                                                container       = document.createElement('div');
                                                container.id    = 'toast-container';
                                                document.body.appendChild(container);
                                            }

                                            if (bootstrapLink && window.bootstrap) {
                                                const toastEl      = document.createElement('div');
                                                toastEl.className  = 'toast';
                                                for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);

                                                const body         = document.createElement('div');
                                                body.className     = 'toast-body';
                                                body.textContent   = msg;

                                                toastEl.appendChild(body);
                                                container.appendChild(toastEl);
                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                            } else {
                                                alert(msg);
                                            }

                                            form.setAttribute('data-failed-route', 'true');
                                        } catch (e) {
    console.error(`[submit] Error:`, e);
  }
                                    });
                                })();

export {};
