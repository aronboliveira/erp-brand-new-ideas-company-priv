<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script async src="{{ asset('assets/js/routes/bills/lang/pdf.js') }}"></script>
<script defer>
    (() => {
      const errFb = '# ERROR';
      const dataClientLocalized = 'data-client-localized';
      const dataGuardMsg = 'data-guard-msg';
      const langSessionKey = 'erp-np-lang';
    
      function getLocalizedMessage(key, el) {
        let msg = errFb;
        if (el.getAttribute('data-sv-localized') === 'true'
         || el.getAttribute(dataClientLocalized) === 'true') {
          msg = el.getAttribute(dataGuardMsg) ?? errFb;
        } else {
          let lang = (window.sessionStorage.getItem(langSessionKey)
                    ?? document.documentElement.lang
                    ?? 'en').toLowerCase().replace(/_/g, '-');
          lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
          msg = window.translations?.[lang]?.[key]
             ?? el.getAttribute(dataGuardMsg)
             ?? window.translations?.['en']?.[key]
             ?? errFb;
          if (msg !== errFb) {
            el.setAttribute(dataGuardMsg, msg);
            el.setAttribute(dataClientLocalized, 'true');
          }
        }
        return msg;
      }
    
      function showError(message) {
        try {
          let container = document.getElementById('toast-container');
          if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
          }
          const bsLink = document.querySelector('link[href*="bootstrap"]');
          if (bsLink && window.bootstrap) {
            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            const body = document.createElement('div');
            body.className = 'toast-body';
            body.textContent = message;
            toastEl.appendChild(body);
            container.appendChild(toastEl);
            bootstrap.Toast.getOrCreateInstance(toastEl).show();
          } else {
            alert(message);
          }
        } catch {
          alert(message);
        }
      }
    
      let errorMessage = '';
      const onErrorPointerUp = () => {
        if (errorMessage) {
          showError(errorMessage);
          errorMessage = '';
        }
      };
      document.addEventListener('pointerup', onErrorPointerUp);
      new MutationObserver((muts, obs) => {
        muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
          if (n === document.documentElement) {
            document.removeEventListener('pointerup', onErrorPointerUp);
            obs.disconnect();
          }
        }));
      }).observe(document.body, { childList: true, subtree: true });
    
      function closeScript() {
        setTimeout(() => {
          try {
            window.open(window.location, '_self').close();
          } catch {
            errorMessage = getLocalizedMessage('window_close_failed', document.body);
          }
        }, 1000);
      }
    
      window.addEventListener('load', () => {
        try {
          const element = document.getElementById('boxes');
          if (!element) throw 0;
          const opt = {
            filename: '{{ Utility::vendorBillNumberFormat($bill->bill_id) }}',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 4, dpi: 72, letterRendering: true },
            jsPDF: { unit: 'in', format: 'A4' }
          };
          if (typeof html2pdf !== 'function') throw 0;
          html2pdf().set(opt).from(element).save()
            .then(closeScript)
            .catch(() => {
              console.log('html2pdf error');
              errorMessage = getLocalizedMessage('pdf_generation_failed', document.body);
            });
        } catch {
          errorMessage = getLocalizedMessage('pdf_generation_failed', document.body);
        }
      });
    })();
</script>
    