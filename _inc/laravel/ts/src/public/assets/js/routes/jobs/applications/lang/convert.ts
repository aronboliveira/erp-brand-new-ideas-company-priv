/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/lang/convert.js
 * @generated from original JavaScript — automated migration
 * @module convert
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
    ar: { designation_fetch_unavailable: "لا يمكن جلب المسميات الوظيفية" },
    da: { designation_fetch_unavailable: "Kan ikke hente titler" },
    de: { designation_fetch_unavailable: "Kann Bezeichnungen nicht abrufen" },
    en: { designation_fetch_unavailable: "Cannot fetch designations" },
    es: {
      designation_fetch_unavailable: "No se pueden obtener las designaciones",
    },
    fr: {
      designation_fetch_unavailable: "Impossible de récupérer les désignations",
    },
    he: { designation_fetch_unavailable: "לא ניתן להביא את התפקידים" },
    it: {
      designation_fetch_unavailable: "Impossibile recuperare le designazioni",
    },
    ja: { designation_fetch_unavailable: "役職を取得できません" },
    nl: { designation_fetch_unavailable: "Kan functietitels niet ophalen" },
    pl: { designation_fetch_unavailable: "Nie można pobrać stanowisk" },
    pt: {
      designation_fetch_unavailable: "Não foi possível obter as designações",
    },
    "pt-br": {
      designation_fetch_unavailable: "Não foi possível obter as designações",
    },
    ru: { designation_fetch_unavailable: "Не удалось получить должности" },
    tr: { designation_fetch_unavailable: "Unvanlar alınamadı" },
    zh: { designation_fetch_unavailable: "无法获取职务" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
