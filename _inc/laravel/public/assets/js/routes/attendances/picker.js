(() => {
  const BS_LINK = 'link[href*="bootstrap"]';
  const DATE_PICKER_CLASS = ".daterangepicker";
  const DATE_PICKER_ATTR = "data-datepicker";
  const translations = {
    ar: { datepicker_unavailable: "فشل في تهيئة منتقي التاريخ" },
    da: { datepicker_unavailable: "Kunne ikke initialisere datovælger" },
    de: {
      datepicker_unavailable: "Datumauswahl konnte nicht initialisiert werden",
    },
    en: { datepicker_unavailable: "Failed to initialize date picker" },
    es: { datepicker_unavailable: "Error al inicializar el selector de fecha" },
    fr: {
      datepicker_unavailable: "Échec de l'initialisation du sélecteur de date",
    },
    he: { datepicker_unavailable: "נכשל באתחול בורר התאריכים" },
    it: {
      datepicker_unavailable: "Impossibile inizializzare il selettore di data",
    },
    ja: { datepicker_unavailable: "日付ピッカーの初期化に失敗しました" },
    nl: { datepicker_unavailable: "Initialiseren van datumkiezer mislukt" },
    pl: { datepicker_unavailable: "Nie udało się zainicjować selektora daty" },
    pt: { datepicker_unavailable: "Falha ao inicializar o seletor de data" },
    "pt-br": {
      datepicker_unavailable: "Falha ao inicializar o seletor de data",
    },
    ru: { datepicker_unavailable: "Не удалось инициализировать выбор даты" },
    tr: { datepicker_unavailable: "Tarih seçici başlatılamadı" },
    zh: { datepicker_unavailable: "无法初始化日期选择器" },
  };

  const toastContainer = (() => {
    const existing = document.querySelector(".toast-container");
    if (existing) return existing;
    const container = document.createElement("div");
    container.className = "toast-container position-fixed bottom-0 end-0 p-3";
    document.body.append(container);
    return container;
  })();

  const showError = (key) => {
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    let msg = errFb;
    if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true")
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.["en"]?.[msgKey] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    const bs = document.querySelector(BS_LINK);
    if (bs && window.bootstrap?.Toast) {
      const toast = document.createElement("div");
      toast.className = "toast align-items-center text-bg-danger border-0";
      toast.setAttribute("role", "alert");
      toast.setAttribute("aria-live", "assertive");
      toast.setAttribute("aria-atomic", "true");
      toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
      toastContainer.append(toast);
      new window.bootstrap.Toast(toast).show();
    } else {
      alert(msg);
    }
  };

  const handleDatePickerClick = (el) => {
    try {
      if (typeof $ !== "function") throw new Error("jQuery not loaded");
      if (typeof $(el).daterangepicker !== "function") throw new Error("daterangepicker plugin not available");
      $(el).daterangepicker({
        format: "yyyy-mm-dd",
        locale: { format: "YYYY-MM-DD" },
      });
    } catch (err) {
      showError("datepicker_unavailable");
    }
  };

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.removedNodes.forEach((node) => {
        if (node.nodeType === 1 && node.matches(DATE_PICKER_CLASS)) {
          node.removeEventListener("click", handleDatePickerClick);
        }
      });
    });
  });

  try {
    const pickers = document.querySelectorAll(DATE_PICKER_CLASS);
    if (!pickers.length) return;

    observer.observe(document.body, { childList: true, subtree: true });

    pickers.forEach((el) => {
      if (el.getAttribute(DATE_PICKER_ATTR) === "true") return;
      el.setAttribute(DATE_PICKER_ATTR, "true");
      el.addEventListener("click", () => handleDatePickerClick(el));
    });
  } catch (err) {
    showError("datepicker_unavailable");
  }
})();
