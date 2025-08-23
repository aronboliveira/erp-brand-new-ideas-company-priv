<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
      ar: {
        pdf_generation_failed: 'فشل إنشاء ملف PDF.',
        window_close_failed: 'فشل إغلاق النافذة.'
      },
      da: {
        pdf_generation_failed: 'Oprettelse af PDF mislykkedes.',
        window_close_failed: 'Lukning af vindue mislykkedes.'
      },
      de: {
        pdf_generation_failed: 'PDF-Generierung fehlgeschlagen.',
        window_close_failed: 'Fenster konnte nicht geschlossen werden.'
      },
      en: {
        pdf_generation_failed: 'Failed to generate PDF.',
        window_close_failed: 'Failed to close window.'
      },
      es: {
        pdf_generation_failed: 'Error al generar el PDF.',
        window_close_failed: 'Error al cerrar la ventana.'
      },
      fr: {
        pdf_generation_failed: 'Échec de la génération du PDF.',
        window_close_failed: 'Échec de la fermeture de la fenêtre.'
      },
      he: {
        pdf_generation_failed: 'יצירת PDF נכשלה.',
        window_close_failed: 'סגירת החלון נכשלה.'
      },
      it: {
        pdf_generation_failed: 'Creazione del PDF non riuscita.',
        window_close_failed: 'Chiusura della finestra non riuscita.'
      },
      ja: {
        pdf_generation_failed: 'PDF の生成に失敗しました。',
        window_close_failed: 'ウィンドウを閉じることができませんでした。'
      },
      nl: {
        pdf_generation_failed: 'Genereren van PDF is mislukt.',
        window_close_failed: 'Venster sluiten is mislukt.'
      },
      pl: {
        pdf_generation_failed: 'Nie udało się wygenerować PDF.',
        window_close_failed: 'Nie udało się zamknąć okna.'
      },
      pt: {
        pdf_generation_failed: 'Falha ao gerar PDF.',
        window_close_failed: 'Falha ao fechar a janela.'
      },
      'pt-br': {
        pdf_generation_failed: 'Falha ao gerar PDF.',
        window_close_failed: 'Falha ao fechar a janela.'
      },
      ru: {
        pdf_generation_failed: 'Не удалось сформировать PDF.',
        window_close_failed: 'Не удалось закрыть окно.'
      },
      tr: {
        pdf_generation_failed: 'PDF oluşturma başarısız oldu.',
        window_close_failed: 'Pencere kapatılamadı.'
      },
      zh: {
        pdf_generation_failed: '生成 PDF 失败。',
        window_close_failed: '关闭窗口失败。'
      }
    };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
 
          })();
    </script>
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
    