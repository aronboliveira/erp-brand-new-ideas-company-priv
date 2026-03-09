/**
 * @fileoverview TypeScript version of public/assets/js/routes/proposals/lang/pdf.js
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

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: { proposal_pdf_unavailable: "تعذّر إنشاء أو تنزيل ملف PDF للاقتراح" },
    da: {
      proposal_pdf_unavailable:
        "Kunne ikke oprette eller downloade forslagets PDF",
    },
    de: {
      proposal_pdf_unavailable:
        "PDF des Angebots konnte nicht erstellt oder heruntergeladen werden",
    },
    en: {
      proposal_pdf_unavailable:
        "Unable to generate or download the proposal PDF",
    },
    es: {
      proposal_pdf_unavailable:
        "No se pudo generar o descargar el PDF de la propuesta",
    },
    fr: {
      proposal_pdf_unavailable:
        "Impossible de générer ou de télécharger le PDF de la proposition",
    },
    he: {
      proposal_pdf_unavailable:
        "לא ניתן ליצור או להוריד את קובץ ה-PDF של ההצעה",
    },
    it: {
      proposal_pdf_unavailable:
        "Impossibile generare o scaricare il PDF della proposta",
    },
    ja: {
      proposal_pdf_unavailable:
        "提案書のPDFを生成またはダウンロードできませんでした",
    },
    nl: {
      proposal_pdf_unavailable:
        "Kan het PDF-bestand van het voorstel niet genereren of downloaden",
    },
    pl: {
      proposal_pdf_unavailable:
        "Nie można wygenerować ani pobrać pliku PDF oferty",
    },
    pt: {
      proposal_pdf_unavailable:
        "Não foi possível gerar ou baixar o PDF da proposta",
    },
    "pt-br": {
      proposal_pdf_unavailable:
        "Não foi possível gerar ou baixar o PDF da proposta",
    },
    ru: {
      proposal_pdf_unavailable:
        "Не удалось создать или скачать PDF предложения",
    },
    tr: {
      proposal_pdf_unavailable:
        "Teklif PDF’si oluşturulamadı veya indirilemedi",
    },
    zh: { proposal_pdf_unavailable: "无法生成或下载提案 PDF" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
