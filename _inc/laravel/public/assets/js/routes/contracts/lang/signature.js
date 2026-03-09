/** @requires ERPUtils (translations) */
(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      route_unavailable: "رابط غير صالح.",
      signature_unavailable: "التوقيع غير متاح.",
      signature_elements_unavailable: "عناصر لوحة التوقيع غير موجودة.",
      signature_save_unavailable: "تعذّر حفظ التوقيع.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      route_unavailable: "Ugyldig URL.",
      signature_unavailable: "Signatur er ikke tilgængelig.",
      signature_elements_unavailable: "Signaturens elementer mangler.",
      signature_save_unavailable: "Kunne ikke gemme signaturen.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      route_unavailable: "Ungültige URL.",
      signature_unavailable: "Signatur nicht verfügbar.",
      signature_elements_unavailable: "Signatur-Elemente fehlen.",
      signature_save_unavailable: "Signatur konnte nicht gespeichert werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      route_unavailable: "Invalid URL.",
      signature_unavailable: "Signature unavailable.",
      signature_elements_unavailable: "Signature pad elements are missing.",
      signature_save_unavailable: "Could not save the signature.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      route_unavailable: "URL no válida.",
      signature_unavailable: "Firma no disponible.",
      signature_elements_unavailable: "Faltan elementos del panel de firma.",
      signature_save_unavailable: "No se pudo guardar la firma.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      route_unavailable: "URL non valide.",
      signature_unavailable: "Signature indisponible.",
      signature_elements_unavailable:
        "Éléments du panneau de signature manquants.",
      signature_save_unavailable: "Impossible d’enregistrer la signature.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      route_unavailable: "כתובת לא חוקית.",
      signature_unavailable: "חתימה אינה זמינה.",
      signature_elements_unavailable: "רכיבי לוח החתימה חסרים.",
      signature_save_unavailable: "לא ניתן היה לשמור את החתימה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      route_unavailable: "URL non valido.",
      signature_unavailable: "Firma non disponibile.",
      signature_elements_unavailable: "Elementi del pannello firma mancanti.",
      signature_save_unavailable: "Impossibile salvare la firma.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      route_unavailable: "無効なURLです。",
      signature_unavailable: "署名を利用できません。",
      signature_elements_unavailable: "署名パッドの要素が見つかりません。",
      signature_save_unavailable: "署名を保存できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      route_unavailable: "Ongeldige URL.",
      signature_unavailable: "Handtekening niet beschikbaar.",
      signature_elements_unavailable:
        "Elementen van het handtekeningvlak ontbreken.",
      signature_save_unavailable: "Kon de handtekening niet opslaan.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      route_unavailable: "Nieprawidłowy adres URL.",
      signature_unavailable: "Podpis niedostępny.",
      signature_elements_unavailable: "Brak elementów panelu podpisu.",
      signature_save_unavailable: "Nie udało się zapisać podpisu.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválido.",
      signature_unavailable: "Assinatura indisponível.",
      signature_elements_unavailable:
        "Elementos do painel de assinatura ausentes.",
      signature_save_unavailable: "Não foi possível salvar a assinatura.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválida.",
      signature_unavailable: "Assinatura indisponível.",
      signature_elements_unavailable:
        "Elementos do painel de assinatura ausentes.",
      signature_save_unavailable: "Não foi possível salvar a assinatura.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      route_unavailable: "Недействительный URL.",
      signature_unavailable: "Подпись недоступна.",
      signature_elements_unavailable: "Отсутствуют элементы панели подписи.",
      signature_save_unavailable: "Не удалось сохранить подпись.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      route_unavailable: "Geçersiz URL.",
      signature_unavailable: "İmza kullanılamıyor.",
      signature_elements_unavailable: "İmza paneli öğeleri eksik.",
      signature_save_unavailable: "İmza kaydedilemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      route_unavailable: "无效的链接。",
      signature_unavailable: "签名不可用。",
      signature_elements_unavailable: "缺少签名板元素。",
      signature_save_unavailable: "无法保存签名。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
