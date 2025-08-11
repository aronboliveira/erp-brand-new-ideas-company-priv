<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="{{ asset('assets/js/html2pdf.bundle.min.js') }}"></script>
<script>
    window.translations = {
      ar:    { estimate_pdf_unavailable: 'فشل إنشاء ملف PDF للتقدير.' },
      da:    { estimate_pdf_unavailable: 'Kunne ikke generere PDF for estimat.' },
      de:    { estimate_pdf_unavailable: 'PDF-Erstellung für Kostenvoranschlag fehlgeschlagen.' },
      en:    { estimate_pdf_unavailable: 'Failed to generate estimate PDF.' },
      es:    { estimate_pdf_unavailable: 'Error al generar el PDF del presupuesto.' },
      fr:    { estimate_pdf_unavailable: 'Échec de la génération du PDF du devis.' },
      it:    { estimate_pdf_unavailable: 'Impossibile generare il PDF del preventivo.' },
      ja:    { estimate_pdf_unavailable: '見積書のPDFを生成できませんでした。' },
      nl:    { estimate_pdf_unavailable: 'Kon PDF voor schatting niet genereren.' },
      pl:    { estimate_pdf_unavailable: 'Nie udało się wygenerować pliku PDF wyceny.' },
      pt:    { estimate_pdf_unavailable: 'Falha ao gerar PDF da estimativa.' },
      'pt-br':{ estimate_pdf_unavailable: 'Falha ao gerar PDF do orçamento.' },
      ru:    { estimate_pdf_unavailable: 'Не удалось создать PDF сметы.' },
      tr:    { estimate_pdf_unavailable: 'Keşif PDF\'i oluşturulamadı.' },
      zh:    { estimate_pdf_unavailable: '生成估算 PDF 失败。' }
    };
</script>
<script defer>
    (() => {
      const errFb       = '# ERROR';
      const toastBoxId  = 'toast-box';
      const langKey     = 'erp-np-lang';
      const msgKey      = 'estimate_pdf_unavailable';
    
      const getMsg = () => {
        let lang = (sessionStorage.getItem(langKey) || document.documentElement.lang || 'en')
          .toLowerCase()
          .replace(/_/g, '-');
        lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
        return window.translations?.[lang]?.[msgKey]
          || window.translations.en[msgKey]
          || errFb;
      };
    
      const showToast = message => {
        const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
          .some(l => /bootstrap/i.test(l.href))
          && window.bootstrap?.Toast;
        if (hasBs) {
          let box = document.getElementById(toastBoxId);
          if (!box) {
            box = document.createElement('div');
            box.id = toastBoxId;
            box.setAttribute('aria-live', 'polite');
            box.setAttribute('aria-atomic', 'true');
            document.body.appendChild(box);
          }
          const t = document.createElement('div');
          t.className = 'toast';
          t.innerHTML = `<div class="toast-body">${message}</div>`;
          box.appendChild(t);
          bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(message);
        }
      };
    
      const closeScript = () => {
        setTimeout(() => {
          try {
            window.open(window.location, '_self').close();
          } catch {
            // ignore
          }
        }, 1000);
      };
    
      window.addEventListener('load', () => {
        try {
          const element = document.getElementById('boxes');
          if (!element) throw new Error();
          const opt = {
            filename: '{{ \Auth::user()->estimateNumberFormat($estimation->estimation_id) }}',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 4, dpi: 72, letterRendering: true },
            jsPDF: { unit: 'in', format: 'A4' }
          };
          html2pdf()
            .set(opt)
            .from(element)
            .save()
            .then(closeScript)
            .catch(() => { throw new Error(); });
        } catch {
          showToast(getMsg());
        }
      });
    })();
</script>
    