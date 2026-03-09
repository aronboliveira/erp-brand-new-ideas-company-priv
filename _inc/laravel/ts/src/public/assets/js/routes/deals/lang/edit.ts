/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/lang/edit.js
 * @generated from original JavaScript — automated migration
 * @module edit
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
      stages_fetch_unavailable: "فشل جلب مراحل الخط الأنابيب.",
    },
    da: {
      stages_fetch_unavailable: "Kunne ikke hente trin for pipeline.",
    },
    de: {
      stages_fetch_unavailable:
        "Abrufen der Phasen der Pipeline fehlgeschlagen.",
    },
    en: {
      stages_fetch_unavailable: "Failed to fetch pipeline stages.",
    },
    es: {
      stages_fetch_unavailable: "Error al obtener las etapas del pipeline.",
    },
    fr: {
      stages_fetch_unavailable:
        "Échec de la récupération des étapes du pipeline.",
    },
    he: {
      stages_fetch_unavailable: "הבאת שלבי הצנרת נכשלה.",
    },
    it: {
      stages_fetch_unavailable:
        "Impossibile recuperare le fasi della pipeline.",
    },
    ja: {
      stages_fetch_unavailable:
        "パイプラインのステージを取得できませんでした。",
    },
    nl: {
      stages_fetch_unavailable: "Kan pijplijnfasen niet ophalen.",
    },
    pl: {
      stages_fetch_unavailable: "Nie udało się pobrać etapów pipeline.",
    },
    pt: {
      stages_fetch_unavailable: "Falha ao obter as fases do pipeline.",
    },
    "pt-br": {
      stages_fetch_unavailable: "Falha ao obter as fases do pipeline.",
    },
    ru: {
      stages_fetch_unavailable: "Не удалось получить этапы конвейера.",
    },
    tr: {
      stages_fetch_unavailable: "Pipeline aşamaları alınamadı.",
    },
    zh: {
      stages_fetch_unavailable: "获取管道阶段失败。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
