/**
 * @fileoverview TypeScript version of public/assets/js/routes/leads/lang/discussions.js
 * @generated from original JavaScript — automated migration
 * @module discussions
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  const t: Record<string, Record<string, string>> = {
    ar: { leads_discussion_route_unavailable: "الإجراء غير متاح." },
    da: {
      leads_discussion_route_unavailable: "Handlingen er ikke tilgængelig.",
    },
    de: { leads_discussion_route_unavailable: "Aktion nicht verfügbar." },
    en: { leads_discussion_route_unavailable: "Action unavailable." },
    es: { leads_discussion_route_unavailable: "Acción no disponible." },
    fr: { leads_discussion_route_unavailable: "Action indisponible." },
    he: { leads_discussion_route_unavailable: "הפעולה אינה זמינה." },
    it: { leads_discussion_route_unavailable: "Azione non disponibile." },
    ja: { leads_discussion_route_unavailable: "操作を利用できません。" },
    nl: { leads_discussion_route_unavailable: "Actie niet beschikbaar." },
    pl: { leads_discussion_route_unavailable: "Akcja niedostępna." },
    pt: { leads_discussion_route_unavailable: "Ação indisponível." },
    "pt-br": { leads_discussion_route_unavailable: "Ação indisponível." },
    ru: { leads_discussion_route_unavailable: "Действие недоступно." },
    tr: { leads_discussion_route_unavailable: "İşlem kullanılamıyor." },
    zh: { leads_discussion_route_unavailable: "操作不可用。" },
  };
  if (!window.translations) {
    window.translations = t;
  } else {
    Object.keys(t).forEach(k => {
      window.translations![k] = Object.assign(
        {},
        window.translations![k] || {},
        t[k]
      );
    });
  }
})();
