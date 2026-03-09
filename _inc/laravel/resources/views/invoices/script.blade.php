@php
@endphp
<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script async src="{{ asset('assets/js/routes/invoices/lang/script.js') }}"></script>
<script defer>
    (() => {
        const DATA_LISTENER_ADDED = 'data-listener-added';
        const RG = window.RouteGuard || {};
        const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
        const showError = RG.showToast || (m => alert(m));
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
            const handler = () => showError(getMsg('invoice_pdf_unavailable', el));
            if (el && !el.hasAttribute(DATA_LISTENER_ADDED)) {
                el.addEventListener('click', handler);
                el.setAttribute(DATA_LISTENER_ADDED, 'true');
                const obs = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                        el.removeEventListener('click', handler);
                        o.disconnect();
                    }
                });
                obs.observe(document.body, { childList: true, subtree: true });
            } else {
                handler();
            }
        }
    })();
</script>
