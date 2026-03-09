/**
 * @fileoverview TypeScript version of public/assets/js/routes/generics/lang/utility.js
 * @generated from original JavaScript — automated migration
 * @module utility
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  if (!window.translations) window.translations = {};
  const t: Record<string, Record<string, string>> = {
    ar: {
      route_unavailable:
        "هذا المسار غير متاح في الوقت الحالي. يرجى المحاولة مرة أخرى لاحقًا.",
    },
    da: {
      route_unavailable: "Denne rute er ikke tilgængelig i øjeblikket.",
    },
    de: {
      route_unavailable: "Diese Route ist im Moment nicht verfügbar.",
    },
    en: {
      route_unavailable:
        "This route is unavailable at the moment. Please try again later.",
    },
    es: {
      route_unavailable: "Esta ruta no está disponible en este momento.",
    },
    fr: {
      route_unavailable: "Cette route n'est pas disponible pour le moment.",
    },
    he: {
      route_unavailable: "נתיב זה אינו זמין כרגע.",
    },
    it: {
      route_unavailable: "Questa rotta non è disponibile al momento.",
    },
    ja: {
      route_unavailable: "このルートは現在利用できません。",
    },
    nl: {
      route_unavailable: "Deze route is momenteel niet beschikbaar.",
    },
    pt: {
      route_unavailable: "Esta rota não está disponível no momento.",
    },
    "pt-br": {
      route_unavailable: "Esta rota não está disponível no momento.",
    },
    pl: {
      route_unavailable: "Ta trasa jest obecnie niedostępna.",
    },
    ru: {
      route_unavailable: "Этот маршрут в данный момент недоступен.",
    },
    tr: {
      route_unavailable: "Bu rota şu anda kullanılamıyor.",
    },
    zh: {
      route_unavailable: "此路由目前不可用。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
