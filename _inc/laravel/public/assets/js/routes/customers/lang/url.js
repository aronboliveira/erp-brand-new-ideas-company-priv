(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      url_copy_success: "تم نسخ الرابط إلى الحافظة.",
      url_copy_failed: "فشل نسخ الرابط.",
    },
    da: {
      url_copy_success: "URL kopieret til udklipsholder.",
      url_copy_failed: "Kunne ikke kopiere URL.",
    },
    de: {
      url_copy_success: "URL in die Zwischenablage kopiert.",
      url_copy_failed: "Konnte URL nicht kopieren.",
    },
    en: {
      url_copy_success: "URL copied to clipboard.",
      url_copy_failed: "Failed to copy URL.",
    },
    es: {
      url_copy_success: "URL copiada al portapapeles.",
      url_copy_failed: "Error al copiar la URL.",
    },
    fr: {
      url_copy_success: "URL copiée dans le presse-papier.",
      url_copy_failed: "Échec de la copie de l’URL.",
    },
    he: {
      url_copy_success: "הכתובת הועתקה ללוח.",
      url_copy_failed: "העתקת הכתובת נכשלה.",
    },
    it: {
      url_copy_success: "URL copiata negli appunti.",
      url_copy_failed: "Impossibile copiare l’URL.",
    },
    ja: {
      url_copy_success: "URL をクリップボードにコピーしました。",
      url_copy_failed: "URL のコピーに失敗しました。",
    },
    nl: {
      url_copy_success: "URL gekopieerd naar klembord.",
      url_copy_failed: "Kon URL niet kopiëren.",
    },
    pl: {
      url_copy_success: "URL skopiowany do schowka.",
      url_copy_failed: "Nie udało się skopiować URL.",
    },
    pt: {
      url_copy_success: "URL copiada para a área de transferência.",
      url_copy_failed: "Falha ao copiar a URL.",
    },
    "pt-br": {
      url_copy_success: "URL copiada para a área de transferência.",
      url_copy_failed: "Falha ao copiar a URL.",
    },
    ru: {
      url_copy_success: "URL скопирован в буфер обмена.",
      url_copy_failed: "Не удалось скопировать URL.",
    },
    tr: {
      url_copy_success: "URL panoya kopyalandı.",
      url_copy_failed: "URL kopyalanamadı.",
    },
    zh: {
      url_copy_success: "URL 已复制到剪贴板。",
      url_copy_failed: "无法复制 URL。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
