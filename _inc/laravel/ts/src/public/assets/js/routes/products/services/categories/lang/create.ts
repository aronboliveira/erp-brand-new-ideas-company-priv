/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/categories/lang/create.js
 * @generated from original JavaScript — automated migration
 * @module create
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
      account_toggle_unavailable: "تعذّر تبديل عرض الحساب",
      get_account_unavailable: "تعذّر جلب قائمة الحسابات",
    },
    da: {
      account_toggle_unavailable: "Kan ikke skifte konto-visning",
      get_account_unavailable: "Kan ikke hente kontoliste",
    },
    de: {
      account_toggle_unavailable:
        "Kontosichtbarkeit konnte nicht umgeschaltet werden",
      get_account_unavailable: "Kontoliste konnte nicht geladen werden",
    },
    en: {
      account_toggle_unavailable: "Cannot toggle account visibility",
      get_account_unavailable: "Cannot fetch accounts list",
    },
    es: {
      account_toggle_unavailable:
        "No se puede alternar la visibilidad de cuenta",
      get_account_unavailable: "No se pudo obtener la lista de cuentas",
    },
    fr: {
      account_toggle_unavailable:
        "Impossible d’afficher/masquer la section compte",
      get_account_unavailable: "Impossible de récupérer la liste des comptes",
    },
    he: {
      account_toggle_unavailable: "לא ניתן להחליף תצוגת חשבון",
      get_account_unavailable: "לא ניתן להביא את רשימת החשבונות",
    },
    it: {
      account_toggle_unavailable: "Impossibile alternare visibilità conto",
      get_account_unavailable: "Impossibile recuperare elenco conti",
    },
    ja: {
      account_toggle_unavailable: "アカウント表示を切り替えできません",
      get_account_unavailable: "口座リストを取得できません",
    },
    nl: {
      account_toggle_unavailable: "Kan accountweergave niet wisselen",
      get_account_unavailable: "Kan accountlijst niet ophalen",
    },
    pl: {
      account_toggle_unavailable: "Nie można przełączyć widoczności konta",
      get_account_unavailable: "Nie można pobrać listy kont",
    },
    pt: {
      account_toggle_unavailable:
        "Não foi possível alternar a visibilidade da conta",
      get_account_unavailable: "Não foi possível obter a lista de contas",
    },
    "pt-br": {
      account_toggle_unavailable:
        "Não foi possível alternar a visibilidade da conta",
      get_account_unavailable: "Não foi possível obter a lista de contas",
    },
    ru: {
      account_toggle_unavailable: "Не удалось переключить видимость счёта",
      get_account_unavailable: "Не удалось получить список счетов",
    },
    tr: {
      account_toggle_unavailable: "Hesap görünürlüğü değiştirilemiyor",
      get_account_unavailable: "Hesap listesi alınamıyor",
    },
    zh: {
      account_toggle_unavailable: "无法切换账户可见性",
      get_account_unavailable: "无法获取账户列表",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
