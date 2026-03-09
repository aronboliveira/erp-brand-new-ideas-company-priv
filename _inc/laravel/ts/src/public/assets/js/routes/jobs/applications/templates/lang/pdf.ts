/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/applications/templates/lang/pdf.js
 * @generated from original JavaScript — automated migration
 * @module pdf
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

(function (): void {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      pdf_unavailable: "تعذّر إنشاء ملف PDF.",
      element_unavailable: "العنصر المطلوب غير موجود.",
      close_unavailable: "تعذّر إغلاق النافذة.",
    },
    da: {
      pdf_unavailable: "Kunne ikke oprette PDF.",
      element_unavailable: "Påkrævet element mangler.",
      close_unavailable: "Kunne ikke lukke vinduet.",
    },
    de: {
      pdf_unavailable: "PDF konnte nicht erstellt werden.",
      element_unavailable: "Erforderliches Element fehlt.",
      close_unavailable: "Fenster konnte nicht geschlossen werden.",
    },
    en: {
      pdf_unavailable: "Could not generate the PDF.",
      element_unavailable: "Required element is missing.",
      close_unavailable: "Could not close the window.",
    },
    es: {
      pdf_unavailable: "No se pudo generar el PDF.",
      element_unavailable: "Falta el elemento requerido.",
      close_unavailable: "No se pudo cerrar la ventana.",
    },
    fr: {
      pdf_unavailable: "Impossible de générer le PDF.",
      element_unavailable: "Élément requis manquant.",
      close_unavailable: "Impossible de fermer la fenêtre.",
    },
    he: {
      pdf_unavailable: "לא ניתן ליצור PDF.",
      element_unavailable: "האלמנט הנדרש חסר.",
      close_unavailable: "לא ניתן לסגור את החלון.",
    },
    it: {
      pdf_unavailable: "Impossibile generare il PDF.",
      element_unavailable: "Elemento richiesto mancante.",
      close_unavailable: "Impossibile chiudere la finestra.",
    },
    ja: {
      pdf_unavailable: "PDF を生成できませんでした。",
      element_unavailable: "必要な要素が見つかりません。",
      close_unavailable: "ウィンドウを閉じられませんでした。",
    },
    nl: {
      pdf_unavailable: "PDF kon niet worden gegenereerd.",
      element_unavailable: "Vereist element ontbreekt.",
      close_unavailable: "Kon het venster niet sluiten.",
    },
    pl: {
      pdf_unavailable: "Nie udało się wygenerować PDF.",
      element_unavailable: "Brakuje wymaganego elementu.",
      close_unavailable: "Nie udało się zamknąć okna.",
    },
    pt: {
      pdf_unavailable: "Não foi possível gerar o PDF.",
      element_unavailable: "Elemento necessário ausente.",
      close_unavailable: "Não foi possível fechar a janela.",
    },
    "pt-br": {
      pdf_unavailable: "Não foi possível gerar o PDF.",
      element_unavailable: "Elemento obrigatório ausente.",
      close_unavailable: "Não foi possível fechar a janela.",
    },
    ru: {
      pdf_unavailable: "Не удалось создать PDF.",
      element_unavailable: "Отсутствует необходимый элемент.",
      close_unavailable: "Не удалось закрыть окно.",
    },
    tr: {
      pdf_unavailable: "PDF oluşturulamadı.",
      element_unavailable: "Gerekli öğe eksik.",
      close_unavailable: "Pencere kapatılamadı.",
    },
    zh: {
      pdf_unavailable: "无法生成 PDF。",
      element_unavailable: "缺少所需元素。",
      close_unavailable: "无法关闭窗口。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
