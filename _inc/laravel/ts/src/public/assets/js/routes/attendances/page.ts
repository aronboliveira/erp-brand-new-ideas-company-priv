/**
 * @fileoverview TypeScript version of public/assets/js/routes/attendances/page.js
 * @generated from original JavaScript - manual review recommended
 * @module page
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars

((): void => {
  const BS_LINK = 'link[href*="bootstrap"]';
  const TYPE_RADIO = 'input[name="type"][type="radio"]';
  const MONTH_CLASS = "month";
  const DATE_CLASS = "date";
  const TOGGLER_ATTR = "data-toggler-initialized";
  const _translations = {
    ar: {
      toggler_unavailable: "فشل في تهيئة المبدل",
      toggle_failed: "فشل في تبديل العرض",
    },
    da: {
      toggler_unavailable: "Kunne ikke initialisere skifter",
      toggle_failed: "Kunne ikke skifte visning",
    },
    de: {
      toggler_unavailable: "Umschalter konnte nicht initialisiert werden",
      toggle_failed: "Anzeige konnte nicht umgeschaltet werden",
    },
    en: {
      toggler_unavailable: "Failed to initialize toggler",
      toggle_failed: "Failed to toggle display",
    },
    es: {
      toggler_unavailable: "Error al inicializar el conmutador",
      toggle_failed: "Error al cambiar la visualización",
    },
    fr: {
      toggler_unavailable: "Échec de l'initialisation du commutateur",
      toggle_failed: "Échec de la commutation de l'affichage",
    },
    he: {
      toggler_unavailable: "נכשל באתחול המתג",
      toggle_failed: "נכשל בשינוי התצוגה",
    },
    it: {
      toggler_unavailable: "Impossibile inizializzare l'interruttore",
      toggle_failed: "Impossibile cambiare la visualizzazione",
    },
    ja: {
      toggler_unavailable: "トグラーの初期化に失敗しました",
      toggle_failed: "表示の切り替えに失敗しました",
    },
    nl: {
      toggler_unavailable: "Initialiseren van schakelaar mislukt",
      toggle_failed: "Omschakelen van weergave mislukt",
    },
    pl: {
      toggler_unavailable: "Nie udało się zainicjować przełącznika",
      toggle_failed: "Nie udało się przełączyć wyświetlania",
    },
    pt: {
      toggler_unavailable: "Falha ao inicializar o comutador",
      toggle_failed: "Falha ao alternar a exibição",
    },
    "pt-br": {
      toggler_unavailable: "Falha ao inicializar o comutador",
      toggle_failed: "Falha ao alternar a exibição",
    },
    ru: {
      toggler_unavailable: "Не удалось инициализировать переключатель",
      toggle_failed: "Не удалось переключить отображение",
    },
    tr: {
      toggler_unavailable: "Değiştirici başlatılamadı",
      toggle_failed: "Görüntü değiştirilemedi",
    },
    zh: {
      toggler_unavailable: "无法初始化切换器",
      toggle_failed: "无法切换显示",
    },
  };

  const toastContainer = ((): HTMLElement => {
    let container = document.querySelector<HTMLElement>(".toast-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "toast-container position-fixed bottom-0 end-0 p-3";
      document.body.append(container);
    }
    return container;
  })();

  const showError = (key: string, el: HTMLElement | null = null): void=> {
    const errFb = "# ERROR";
    const dataClientLocalized = "data-client-localized";
    const dataGuardMsg = "data-guard-msg";
    let msg = errFb;
    if (
      el?.getAttribute("data-sv-localized") === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    const bs = document.querySelector(BS_LINK);
    if (bs && window.bootstrap.Toast) {
      const existingToast = toastContainer.querySelector(
        '.toast[data-error-key="' + key + '"]',
      );
      if (existingToast) return;

      const toast = document.createElement("div");
      toast.className = "toast align-items-center text-bg-danger border-0";
      toast.dataset.errorKey = key;
      for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
      {
        toast.replaceChildren();
        const _d = document.createElement("div");
        _d.className = "d-flex";
        const _b = document.createElement("div");
        _b.className = "toast-body";
        _b.textContent = msg;
        const _c = document.createElement("button");
        _c.type = "button";
        _c.className = "btn-close btn-close-white me-2 m-auto";
        _c.dataset.bsDismiss = "toast";
        _c.setAttribute("aria-label", "Close");
        _d.append(_b, _c);
        toast.append(_d);
      }
      toastContainer.append(toast);
      new window.bootstrap.Toast(toast).show();
    } else {
      alert(msg);
    }
  };

  const handleToggle = (e: Event): void=> {
    try {
      if (typeof $ !== "function") throw new Error("jQuery not loaded");
      const target = e.target as HTMLInputElement | null;
      if (!target) return;
      const type = target.value ?? "";
      const showMonth = type === "monthly";
      document
        .querySelectorAll(`.${MONTH_CLASS}`)
        .forEach((el: Element): void => {
          el.classList.toggle("d-block", showMonth);
          el.classList.toggle("d-none", !showMonth);
        });
      document
        .querySelectorAll(`.${DATE_CLASS}`)
        .forEach((el: Element): void => {
          el.classList.toggle("d-block", !showMonth);
          el.classList.toggle("d-none", showMonth);
        });
    } catch (err) {
      showError("toggle_failed");
    }
  };

  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      mutation.removedNodes.forEach(node => {
        if (node.nodeType === 1 && (node as HTMLElement).matches(TYPE_RADIO)) {
          (node as HTMLElement).removeEventListener("change", handleToggle);
        }
      });
    });
  });

  try {
    const radios = document.querySelectorAll(TYPE_RADIO);
    if (radios.length === 0) return;

    observer.observe(document.body, { childList: true, subtree: true });

    radios.forEach(radio => {
      if (radio.getAttribute(TOGGLER_ATTR)) return;
      radio.setAttribute(TOGGLER_ATTR, "true");
      radio.addEventListener("change", handleToggle);
    });

    const checked = document.querySelector(`${TYPE_RADIO}:checked`);
    if (checked) checked.dispatchEvent(new Event("change"));
  } catch (err) {
    showError("toggler_unavailable");
  }
})();

export {};
