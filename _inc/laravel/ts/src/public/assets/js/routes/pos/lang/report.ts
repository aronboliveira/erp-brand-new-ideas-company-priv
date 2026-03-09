/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/lang/report.js
 * @generated from original JavaScript — automated migration
 * @module report
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
    ar: { pos_route_unavailable: "الإجراء غير متاح." },
    da: { pos_route_unavailable: "Handlingen er ikke tilgængelig." },
    de: { pos_route_unavailable: "Aktion nicht verfügbar." },
    en: { pos_route_unavailable: "Action unavailable." },
    es: { pos_route_unavailable: "Acción no disponible." },
    fr: { pos_route_unavailable: "Action indisponible." },
    he: { pos_route_unavailable: "הפעולה אינה זמינה." },
    it: { pos_route_unavailable: "Azione non disponibile." },
    ja: { pos_route_unavailable: "操作を利用できません。" },
    nl: { pos_route_unavailable: "Actie niet beschikbaar." },
    pl: { pos_route_unavailable: "Akcja niedostępna." },
    pt: { pos_route_unavailable: "Ação indisponível." },
    "pt-br": { pos_route_unavailable: "Ação indisponível." },
    ru: { pos_route_unavailable: "Действие недоступно." },
    tr: { pos_route_unavailable: "İşlem kullanılamıyor." },
    zh: { pos_route_unavailable: "操作不可用。" },
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
