(function () {
  if (!window.translations) window.translations = {};
  const t = {
    ar: {
      element_unavailable: "العنصر المطلوب غير موجود.",
      request_failed: "فشل الطلب.",
      jquery_unavailable: "مكتبة jQuery غير متوفرة.",
      init_failed: "فشل بدء تشغيل الواجهة.",
      bootstrap_toast_unavailable: "إشعار التوست غير متاح.",
    },
    da: {
      element_unavailable: "Påkrævet element mangler.",
      request_failed: "Forespørgslen mislykkedes.",
      jquery_unavailable: "jQuery er ikke tilgængelig.",
      init_failed: "Initialisering mislykkedes.",
      bootstrap_toast_unavailable: "Toast-notifikation er ikke tilgængelig.",
    },
    de: {
      element_unavailable: "Erforderliches Element fehlt.",
      request_failed: "Anfrage fehlgeschlagen.",
      jquery_unavailable: "jQuery ist nicht verfügbar.",
      init_failed: "Initialisierung fehlgeschlagen.",
      bootstrap_toast_unavailable:
        "Toast-Benachrichtigung ist nicht verfügbar.",
    },
    en: {
      element_unavailable: "Required element is missing.",
      request_failed: "The request failed.",
      jquery_unavailable: "jQuery is not available.",
      init_failed: "UI initialization failed.",
      bootstrap_toast_unavailable: "Toast notification is unavailable.",
    },
    es: {
      element_unavailable: "Falta el elemento requerido.",
      request_failed: "La solicitud falló.",
      jquery_unavailable: "jQuery no está disponible.",
      init_failed: "La inicialización falló.",
      bootstrap_toast_unavailable: "La notificación toast no está disponible.",
    },
    fr: {
      element_unavailable: "Élément requis manquant.",
      request_failed: "La requête a échoué.",
      jquery_unavailable: "jQuery n'est pas disponible.",
      init_failed: "Échec de l'initialisation.",
      bootstrap_toast_unavailable: "Notification toast indisponible.",
    },
    he: {
      element_unavailable: "האלמנט הנדרש חסר.",
      request_failed: "הבקשה נכשלה.",
      jquery_unavailable: "jQuery אינו זמין.",
      init_failed: "אתחול הממשק נכשל.",
      bootstrap_toast_unavailable: "התראת Toast אינה זמינה.",
    },
    it: {
      element_unavailable: "Elemento richiesto mancante.",
      request_failed: "Richiesta non riuscita.",
      jquery_unavailable: "jQuery non è disponibile.",
      init_failed: "Inizializzazione non riuscita.",
      bootstrap_toast_unavailable: "Notifica toast non disponibile.",
    },
    ja: {
      element_unavailable: "必要な要素が見つかりません。",
      request_failed: "リクエストに失敗しました。",
      jquery_unavailable: "jQuery が利用できません。",
      init_failed: "初期化に失敗しました。",
      bootstrap_toast_unavailable: "トースト通知は利用できません。",
    },
    nl: {
      element_unavailable: "Vereist element ontbreekt.",
      request_failed: "Aanvraag mislukt.",
      jquery_unavailable: "jQuery is niet beschikbaar.",
      init_failed: "Initialisatie mislukt.",
      bootstrap_toast_unavailable: "Toastmelding is niet beschikbaar.",
    },
    pl: {
      element_unavailable: "Brakuje wymaganego elementu.",
      request_failed: "Żądanie nie powiodło się.",
      jquery_unavailable: "Brak dostępnej biblioteki jQuery.",
      init_failed: "Inicjalizacja nie powiodła się.",
      bootstrap_toast_unavailable: "Powiadomienie toast jest niedostępne.",
    },
    pt: {
      element_unavailable: "Elemento necessário ausente.",
      request_failed: "A solicitação falhou.",
      jquery_unavailable: "jQuery não está disponível.",
      init_failed: "Falha na inicialização.",
      bootstrap_toast_unavailable: "Notificação toast indisponível.",
    },
    "pt-br": {
      element_unavailable: "Elemento obrigatório ausente.",
      request_failed: "A solicitação falhou.",
      jquery_unavailable: "jQuery não está disponível.",
      init_failed: "Falha na inicialização.",
      bootstrap_toast_unavailable: "Notificação toast indisponível.",
    },
    ru: {
      element_unavailable: "Отсутствует необходимый элемент.",
      request_failed: "Запрос не выполнен.",
      jquery_unavailable: "jQuery недоступен.",
      init_failed: "Сбой инициализации.",
      bootstrap_toast_unavailable: "Уведомление toast недоступно.",
    },
    tr: {
      element_unavailable: "Gerekli öğe eksik.",
      request_failed: "İstek başarısız oldu.",
      jquery_unavailable: "jQuery kullanılamıyor.",
      init_failed: "Başlatma başarısız.",
      bootstrap_toast_unavailable: "Toast bildirimi kullanılamıyor.",
    },
    zh: {
      element_unavailable: "缺少所需元素。",
      request_failed: "请求失败。",
      jquery_unavailable: "jQuery 不可用。",
      init_failed: "初始化失败。",
      bootstrap_toast_unavailable: "Toast 通知不可用。",
    },
  };
  Object.keys(t).forEach(k => {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
  (() => {
    const lang = (
      document.documentElement.getAttribute("lang") || "en"
    ).toLowerCase();
    const dict =
      (window.translations &&
        (window.translations[lang] ||
          window.translations[lang.split("-")[0]])) ||
      window.translations?.en ||
      {};
    const tr = k => dict[k] || k;

    const showToastOrAlert = msg => {
      try {
        const hasBootstrapToast = !!(
          window.bootstrap && window.bootstrap.Toast
        );
        if (hasBootstrapToast) {
          let container = document.getElementById("toast-container");
          if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.style.position = "fixed";
            container.style.top = "1rem";
            container.style.right = "1rem";
            container.style.zIndex = "1080";
            document.body.appendChild(container);
          }
          const toastEl = document.createElement("div");
          toastEl.className =
            "toast align-items-center text-bg-danger border-0";
          toastEl.setAttribute("role", "alert");
          toastEl.setAttribute("aria-live", "assertive");
          toastEl.setAttribute("aria-atomic", "true");
          toastEl.innerHTML = `
            <div class="d-flex">
              <div class="toast-body">${msg}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
          container.appendChild(toastEl);
          const toast = new window.bootstrap.Toast(toastEl, { delay: 3500 });
          toast.show();
          setTimeout(() => toastEl.remove(), 4000);
        } else {
          alert(msg);
        }
      } catch {
        alert(msg);
      }
    };

    const ensure = selector => {
      const el = document.querySelector(selector);
      if (!el) throw new Error(`${tr("element_unavailable")} (${selector})`);
      return el;
    };

    const init = () => {
      if (!window.jQuery) throw new Error(tr("jquery_unavailable"));
      const $ = window.jQuery;
      const $radios = $("input[name='client_check']");
      const $existWrap = $(".exist_client");
      const $newWrap = $(".new_client");
      const $name = $("#client_name");
      const $email = $("#client_email");
      const $password = $("#client_password");

      const safeToggle = mode => {
        const exist = mode === "exist";
        if ($existWrap.length) $existWrap.toggleClass("d-none", !exist);
        if ($newWrap.length) $newWrap.toggleClass("d-none", exist);
        if ($name.length)
          exist
            ? $name.removeAttr("required")
            : $name.attr("required", "required");
        if ($email.length)
          exist
            ? $email.removeAttr("required")
            : $email.attr("required", "required");
        if ($password.length)
          exist
            ? $password.removeAttr("required")
            : $password.attr("required", "required");
      };

      const current = ($radios.filter(":checked").val() || "new").toLowerCase();
      safeToggle(current);

      $(document).on("click", "input[name='client_check']", function () {
        try {
          const mode = String($(this).val() || "new").toLowerCase();
          safeToggle(mode);
        } catch (e) {
          showToastOrAlert(e.message || tr("request_failed"));
        }
      });
    };

    const start = () => {
      try {
        ensure("body");
        init();
      } catch (e) {
        showToastOrAlert(e.message || tr("init_failed"));
      }
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", start, { once: true });
    } else {
      start();
    }
  })();
})();
