@php
    use App\Models\Utility;
@endphp
<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script async src="{{ asset('assets/js/routes/invoices/lang/script.js') }}"></script>
<script defer>
    (() => {
        const DATA_LISTENER_ADDED   = 'data-listener-added';
        const ERR_FB                = '# ERROR';
        const DATA_CLIENT_LOCALIZED = 'data-client-localized';
        const DATA_GUARD_MSG        = 'data-guard-msg';

        const getLocalizedMessage = (el, key) => {
            let msg = ERR_FB;
            if (
                el?.getAttribute('data-sv-localized') === 'true' ||
                el?.getAttribute(DATA_CLIENT_LOCALIZED) === 'true'
            ) {
                msg = el.getAttribute(DATA_GUARD_MSG) || ERR_FB;
            } else {
                let lang = (
                    window.sessionStorage.getItem('erp-np-lang') ||
                    document.documentElement.lang ||
                    'en'
                )
                    .toLowerCase()
                    .replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                msg =
                    window.translations?.[lang]?.[key] ||
                    el.getAttribute(DATA_GUARD_MSG) ||
                    window.translations?.['en']?.[key] ||
                    ERR_FB;
                if (msg !== ERR_FB) {
                    el.setAttribute(DATA_GUARD_MSG, msg);
                    el.setAttribute(DATA_CLIENT_LOCALIZED, 'true');
                }
            }
            return msg;
        };

        const handleErrorDisplay = (el, key) => {
            const message = el
                ? getLocalizedMessage(el, key)
                : ERR_FB;
            const hasBootstrap =
                document.querySelector('link[href*="bootstrap"]') &&
                window.bootstrap?.Toast;
            if (hasBootstrap) {
                if (!document.querySelector('#error-toast')) {
                    const toast = document.createElement('div');
                    toast.id        = 'error-toast';
                    toast.className = 'toast align-items-center text-bg-danger border-0';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    toast.innerHTML = `
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button"
                                    class="btn-close btn-close-white me-2 m-auto"
                                    data-bs-dismiss="toast"
                                    aria-label="{{ __('Close') }}"></button>
                        </div>`;
                    document.body.appendChild(toast);
                }
                new bootstrap.Toast(
                    document.querySelector('#error-toast')
                ).show();
            } else {
                alert(message);
            }
        };

        const closeScript = () => {
            setTimeout(() => {
                window.open(window.location, '_self').close();
            }, 1000);
        };

        try {
            if (typeof html2pdf === 'undefined') throw new Error('html2pdf missing');
            const el = document.getElementById('boxes');
            if (!el) throw new Error('Target element not found');
            const filename = "{{ !empty($invoice) && isset($invoice->id) && is_callable([Utility::class, 'customerInvoiceNumberFormat']) ? Utility::customerInvoiceNumberFormat($invoice->invoice_id) : '' }}";
            const opt = {
                filename,
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: 'in', format: 'A4' }
            };
            html2pdf().set(opt).from(el).save().then(closeScript);
        } catch {
            const el = document.getElementById('boxes');
            if (el && !el.hasAttribute(DATA_LISTENER_ADDED)) {
                el.addEventListener('click', () =>
                    handleErrorDisplay(el, 'invoice_pdf_unavailable')
                );
                el.setAttribute(DATA_LISTENER_ADDED, 'true');
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                        el.removeEventListener('click',
                            () => handleErrorDisplay(el, 'invoice_pdf_unavailable')
                        );
                        o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
            } else {
                handleErrorDisplay(el, 'invoice_pdf_unavailable');
            }
        }
    })();
</script>
