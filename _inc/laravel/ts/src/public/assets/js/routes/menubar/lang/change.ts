/**
 * @fileoverview TypeScript version of public/assets/js/routes/menubar/lang/change.js
 * @generated from original JavaScript — automated migration
 * @module change
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
      element_unavailable: "العنصر المطلوب غير موجود.",
      request_failed: "فشل الطلب.",
    },
    da: {
      element_unavailable: "Påkrævet element mangler.",
      request_failed: "Forespørgslen mislykkedes.",
    },
    de: {
      element_unavailable: "Erforderliches Element fehlt.",
      request_failed: "Anfrage fehlgeschlagen.",
    },
    en: {
      element_unavailable: "Required element is missing.",
      request_failed: "The request failed.",
    },
    es: {
      element_unavailable: "Falta el elemento requerido.",
      request_failed: "La solicitud falló.",
    },
    fr: {
      element_unavailable: "Élément requis manquant.",
      request_failed: "La requête a échoué.",
    },
    he: {
      element_unavailable: "האלמנט הנדרש חסר.",
      request_failed: "הבקשה נכשלה.",
    },
    it: {
      element_unavailable: "Elemento richiesto mancante.",
      request_failed: "Richiesta non riuscita.",
    },
    ja: {
      element_unavailable: "必要な要素が見つかりません。",
      request_failed: "リクエストに失敗しました。",
    },
    nl: {
      element_unavailable: "Vereist element ontbreekt.",
      request_failed: "Aanvraag mislukt.",
    },
    pl: {
      element_unavailable: "Brakuje wymaganego elementu.",
      request_failed: "Żądanie nie powiodło się.",
    },
    pt: {
      element_unavailable: "Elemento necessário ausente.",
      request_failed: "A solicitação falhou.",
    },
    "pt-br": {
      element_unavailable: "Elemento obrigatório ausente.",
      request_failed: "A solicitação falhou.",
    },
    ru: {
      element_unavailable: "Отсутствует необходимый элемент.",
      request_failed: "Запрос не выполнен.",
    },
    tr: {
      element_unavailable: "Gerekli öğe eksik.",
      request_failed: "İstek başarısız oldu.",
    },
    zh: { element_unavailable: "缺少所需元素。", request_failed: "请求失败。" },
  };
  Object.keys(t).forEach(k => {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
