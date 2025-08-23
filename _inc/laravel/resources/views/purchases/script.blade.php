<script src="{{ asset('js/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script async>
  (function () {
    if (!window.translations) { window.translations = {}; }
    const t = {
      "ar": { "pdf_unavailable": "تعذّر إنشاء ملف PDF الآن.", "plugin_unavailable": "فشل تحميل مكتبة مطلوبة." },
      "da": { "pdf_unavailable": "Kunne ikke generere PDF lige nu.", "plugin_unavailable": "Et påkrævet bibliotek blev ikke indlæst." },
      "de": { "pdf_unavailable": "PDF konnte derzeit nicht erstellt werden.", "plugin_unavailable": "Erforderliche Bibliothek wurde nicht geladen." },
      "en": { "pdf_unavailable": "PDF export failed.", "plugin_unavailable": "A required library failed to load." },
      "es": { "pdf_unavailable": "La exportación a PDF falló.", "plugin_unavailable": "No se cargó una biblioteca requerida." },
      "fr": { "pdf_unavailable": "L’export PDF a échoué.", "plugin_unavailable": "Une bibliothèque requise n’a pas été chargée." },
      "he": { "pdf_unavailable": "ייצוא ה-PDF נכשל.", "plugin_unavailable": "ספרייה נדרשת לא נטענה." },
      "it": { "pdf_unavailable": "Esportazione PDF non riuscita.", "plugin_unavailable": "Una libreria richiesta non è stata caricata." },
      "ja": { "pdf_unavailable": "PDF の書き出しに失敗しました。", "plugin_unavailable": "必要なライブラリが読み込まれていません。" },
      "nl": { "pdf_unavailable": "PDF-export is mislukt.", "plugin_unavailable": "Een vereiste bibliotheek is niet geladen." },
      "pl": { "pdf_unavailable": "Eksport do PDF nie powiódł się.", "plugin_unavailable": "Wymagana biblioteka nie została wczytana." },
      "pt": { "pdf_unavailable": "Falha na exportação para PDF.", "plugin_unavailable": "Uma biblioteca necessária não foi carregada." },
      "pt-br": { "pdf_unavailable": "Falha ao exportar o PDF.", "plugin_unavailable": "Uma biblioteca necessária não foi carregada." },
      "ru": { "pdf_unavailable": "Не удалось выполнить экспорт PDF.", "plugin_unavailable": "Не загружена необходимая библиотека." },
      "tr": { "pdf_unavailable": "PDF dışa aktarma başarısız oldu.", "plugin_unavailable": "Gerekli bir kitaplık yüklenmedi." },
      "zh": { "pdf_unavailable": "PDF 导出失败。", "plugin_unavailable": "未能加载所需的库。" }
    };
    Object.keys(t).forEach(function (k) { window.translations[k] = { ...(window.translations[k] || {}), ...t[k] }; });
  })();
</script>
<script defer>
  (function () {
    const $ = window.jQuery;
    if (!$) { try { console.error("jQuery unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(document.body, "plugin_unavailable")); return; }
    const qs = (s, r = document) => r.querySelector(s);
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    const dataSvLocalized = "data-sv-localized";
    const dataErrGuard = "data-error-guard";
    const dataLoadGuard = "data-pdf-load-bound";
    const ensureToastContainer = () => {
      const id = "np-toast-container";
      let c = qs("#" + id);
      if (c) { return c; }
      c = document.createElement("div");
      c.id = id;
      c.setAttribute("aria-live", "polite");
      c.setAttribute("aria-atomic", "true");
      c.style.position = "fixed";
      c.style.top = "1rem";
      c.style.right = "1rem";
      document.body.appendChild(c);
      return c;
    };
    const showErrorNow = (message) => {
      const hasBootstrap = qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]');
      if (hasBootstrap && window.bootstrap && window.bootstrap.Toast) {
        const container = ensureToastContainer();
        const toastId = "np-toast";
        let t = qs("#" + toastId, container);
        if (!t) {
          t = document.createElement("div");
          t.id = toastId;
          t.className = "toast";
          t.setAttribute("role", "alert");
          t.setAttribute("aria-live", "assertive");
          t.setAttribute("aria-atomic", "true");
          t.innerHTML = '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
          container.appendChild(t);
        }
        const body = qs(".toast-body", t);
        if (body) { body.textContent = message ?? errFb; }
        try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); }
      } else { alert(message ?? errFb); }
    };
    function scheduleInteractiveError(message) {
      const host = document.body;
      if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
      host.setAttribute(dataErrGuard, "true");
      const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
      document.addEventListener("click", once, { once: true });
      const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener("click", once); o.disconnect(); } });
      mo.observe(document.documentElement, { childList: true, subtree: true });
    }
    const getMsg = (el, key) => {
      const errFbL = errFb;
      const dataClientLocalizedL = dataClientLocalized;
      const dataGuardMsgL = dataGuardMsg;
      let msg = errFbL;
      if (el && (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalizedL) === "true")) { msg = el.getAttribute(dataGuardMsgL) || errFbL; }
      else {
        let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        const msgKey = key;
        msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsgL) || window.translations?.["en"]?.[msgKey] || errFbL;
        if (el && msg !== errFbL) { el.setAttribute(dataGuardMsgL, msg); el.setAttribute(dataClientLocalizedL, "true"); }
      }
      return msg;
    };
    const closeScript = () => {};
    const bindExport = () => {
      const root = document.documentElement;
      if (root.getAttribute(dataLoadGuard) === "true") { return; }
      root.setAttribute(dataLoadGuard, "true");
      $(window).on("load", function () {
        const element = document.getElementById("boxes");
        if (!element) { scheduleInteractiveError(getMsg(document.body, "pdf_unavailable")); return; }
        const opt = {
          filename: "{{Utility::purchaseNumberFormat($purchase->purchase_id)}}",
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A4" }
        };
        try {
          if (typeof window.html2pdf !== "function") { try { console.error("html2pdf unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(element, "plugin_unavailable")); return; }
          window.html2pdf().set(opt).from(element).save().then(closeScript);
        } catch (_) {
          scheduleInteractiveError(getMsg(element, "pdf_unavailable"));
        }
      });
      const mo = new MutationObserver((m, o) => { if (!document.documentElement.isConnected) { $(window).off("load"); o.disconnect(); } });
      mo.observe(document.documentElement, { childList: true, subtree: true });
    };
    if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", bindExport, { once: true }); }
    else { bindExport(); }
  })();
</script>
