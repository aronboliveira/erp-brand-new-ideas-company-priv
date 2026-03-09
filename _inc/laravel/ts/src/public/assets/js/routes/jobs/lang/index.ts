/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/lang/index.js
 * @generated from original JavaScript — automated migration
 * @module index
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
      copy_success: "تم نسخ الرابط إلى الحافظة",
      copy_unavailable: "لا يمكن النسخ إلى الحافظة",
    },
    da: {
      copy_success: "Link kopieret til udklipsholder",
      copy_unavailable: "Kan ikke kopiere til udklipsholder",
    },
    de: {
      copy_success: "Link in die Zwischenablage kopiert",
      copy_unavailable: "Kann nicht in die Zwischenablage kopieren",
    },
    en: {
      copy_success: "URL copied to clipboard",
      copy_unavailable: "Cannot copy to clipboard",
    },
    es: {
      copy_success: "URL copiada al portapapeles",
      copy_unavailable: "No se puede copiar al portapapeles",
    },
    fr: {
      copy_success: "URL copiée dans le presse-papiers",
      copy_unavailable: "Impossible de copier dans le presse-papiers",
    },
    he: {
      copy_success: "הקישור הועתק ללוח",
      copy_unavailable: "לא ניתן להעתיק ללוח",
    },
    it: {
      copy_success: "URL copiata negli appunti",
      copy_unavailable: "Impossibile copiare negli appunti",
    },
    ja: {
      copy_success: "URLをクリップボードにコピーしました",
      copy_unavailable: "クリップボードにコピーできません",
    },
    nl: {
      copy_success: "URL gekopieerd naar klembord",
      copy_unavailable: "Kan niet kopiëren naar klembord",
    },
    pl: {
      copy_success: "Adres URL skopiowany do schowka",
      copy_unavailable: "Nie można skopiować do schowka",
    },
    pt: {
      copy_success: "URL copiado para a área de transferência",
      copy_unavailable: "Não é possível copiar para a área de transferência",
    },
    "pt-br": {
      copy_success: "URL copiada para a área de transferência",
      copy_unavailable: "Não é possível copiar para a área de transferência",
    },
    ru: {
      copy_success: "URL скопирован в буфер обмена",
      copy_unavailable: "Не удалось скопировать в буфер обмена",
    },
    tr: {
      copy_success: "URL panoya kopyalandı",
      copy_unavailable: "Panoya kopyalanamıyor",
    },
    zh: {
      copy_success: "URL 已复制到剪贴板",
      copy_unavailable: "无法复制到剪贴板",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
