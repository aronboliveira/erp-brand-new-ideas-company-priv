(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      date_picker_unavailable:
        "منتقي التاريخ غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال.",
    },
    da: {
      date_picker_unavailable:
        "Datovælger er ikke tilgængelig. Kontakt venligst teknisk support eller din domæneadministrator.",
    },
    de: {
      date_picker_unavailable:
        "Datumswähler ist nicht verfügbar. Bitte kontaktieren Sie den technischen Support oder Ihren Domänenadministrator.",
    },
    en: {
      date_picker_unavailable:
        "Date picker is unavailable. Please contact technical support or your domain administrator.",
    },
    es: {
      date_picker_unavailable:
        "Selector de fecha no está disponible. Por favor, póngase en contacto con el soporte técnico o con el administrador de dominio.",
    },
    fr: {
      date_picker_unavailable:
        "Le sélecteur de date n'est pas disponible. Veuillez contacter le support technique ou votre administrateur de domaine.",
    },
    he: {
      date_picker_unavailable:
        "בוחר התאריכים אינו זמין. אנא פנה לתמיכה הטכנית או למנהל הדומיין שלך.",
    },
    it: {
      date_picker_unavailable:
        "Il selettore di date non è disponibile. Si prega di contattare il supporto tecnico o l'amministratore di dominio.",
    },
    ja: {
      date_picker_unavailable:
        "日付ピッカーは利用できません。技術サポートまたはドメイン管理者にお問い合わせください。",
    },
    nl: {
      date_picker_unavailable:
        "Datumkiezer is niet beschikbaar. Neem contact op met de technische ondersteuning of uw domeinbeheerder.",
    },
    pl: {
      date_picker_unavailable:
        "Wybór daty jest niedostępny. Skontaktuj się z pomocą techniczną lub administratorem domeny.",
    },
    pt: {
      date_picker_unavailable:
        "Seletor de data não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
    },
    "pt-br": {
      date_picker_unavailable:
        "Seletor de data não está disponível. Por favor, entre em contato com o suporte técnico ou com o administrador de domínio.",
    },
    ru: {
      date_picker_unavailable:
        "Выбор даты недоступен. Пожалуйста, свяжитесь с технической поддержкой или администратором домена.",
    },
    tr: {
      date_picker_unavailable:
        "Tarih seçici kullanılamıyor. Lütfen teknik destek veya alan yöneticisi ile iletişime geçin.",
    },
    zh: {
      date_picker_unavailable:
        "日期选择器不可用。请联系技术支持或您的域管理员。",
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
