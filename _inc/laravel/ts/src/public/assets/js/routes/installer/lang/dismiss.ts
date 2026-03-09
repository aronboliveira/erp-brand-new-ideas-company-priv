/**
 * @fileoverview TypeScript version of public/assets/js/routes/installer/lang/dismiss.js
 * @generated from original JavaScript — automated migration
 * @module dismiss
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

(function (): void {
  if (!window.translations) window.translations = {};
  const t: Record<string, Record<string, string>> = {
    ar: {
      dismiss_unavailable: "تعذّر إغلاق التنبيه.",
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
    },
    da: {
      dismiss_unavailable: "Kunne ikke lukke advarslen.",
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
    },
    de: {
      dismiss_unavailable: "Hinweis konnte nicht geschlossen werden.",
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
    },
    en: {
      dismiss_unavailable: "Could not dismiss the alert.",
      plugin_unavailable: "A required library failed to load.",
    },
    es: {
      dismiss_unavailable: "No se pudo cerrar la alerta.",
      plugin_unavailable: "No se cargó una biblioteca requerida.",
    },
    fr: {
      dismiss_unavailable: "Impossible de fermer l’alerte.",
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
    },
    he: {
      dismiss_unavailable: "לא ניתן היה לסגור את ההתראה.",
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
    },
    it: {
      dismiss_unavailable: "Impossibile chiudere l’avviso.",
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
    },
    ja: {
      dismiss_unavailable: "アラートを閉じられませんでした。",
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
    },
    nl: {
      dismiss_unavailable: "Kan de melding niet sluiten.",
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
    },
    pl: {
      dismiss_unavailable: "Nie można zamknąć alertu.",
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
    },
    pt: {
      dismiss_unavailable: "Não foi possível fechar o alerta.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    "pt-br": {
      dismiss_unavailable: "Não foi possível fechar o alerta.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    ru: {
      dismiss_unavailable: "Не удалось закрыть уведомление.",
      plugin_unavailable: "Не загружена необходимая библиотека.",
    },
    tr: {
      dismiss_unavailable: "Uyarı kapatılamadı.",
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
    },
    zh: {
      dismiss_unavailable: "无法关闭警报。",
      plugin_unavailable: "未能加载所需的库。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
