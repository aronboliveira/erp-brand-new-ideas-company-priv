/**
 * @fileoverview TypeScript version of public/assets/js/routes/formBuilders/lang/formField.js
 * @generated from original JavaScript — automated migration
 * @module formField
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      lead_toggle_failed: "فشل تبديل حالة العميل المحتمل.",
    },
    da: {
      lead_toggle_failed: "Kunne ikke ændre lead-tilstand.",
    },
    de: {
      lead_toggle_failed: "Lead-Zustand konnte nicht geändert werden.",
    },
    en: {
      lead_toggle_failed: "Failed to toggle lead status.",
    },
    es: {
      lead_toggle_failed: "Error al cambiar el estado del lead.",
    },
    fr: {
      lead_toggle_failed: "Échec du basculement de l’état du lead.",
    },
    he: {
      lead_toggle_failed: "נכשל בהחלפת מצב הליד.",
    },
    it: {
      lead_toggle_failed: "Impossibile cambiare lo stato del lead.",
    },
    ja: {
      lead_toggle_failed: "リードステータスの切り替えに失敗しました。",
    },
    nl: {
      lead_toggle_failed: "Kan de status van de lead niet wijzigen.",
    },
    pl: {
      lead_toggle_failed: "Nie udało się przełączyć statusu leada.",
    },
    pt: {
      lead_toggle_failed: "Falha ao alternar o status do lead.",
    },
    "pt-br": {
      lead_toggle_failed: "Falha ao alternar o status do lead.",
    },
    ru: {
      lead_toggle_failed: "Не удалось изменить статус лида.",
    },
    tr: {
      lead_toggle_failed: "Lead durumu değiştirilemedi.",
    },
    zh: {
      lead_toggle_failed: "无法切换潜在客户状态。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
