(function () {
  if (!window.translations) window.translations = {};
  const t = {
    ar: {
      action_unavailable: "الإجراء غير متاح.",
      print_unavailable: "الطباعة غير متاحة حاليًا.",
    },
    da: {
      action_unavailable: "Handling ikke tilgængelig.",
      print_unavailable: "Udskrivning er ikke tilgængelig lige nu.",
    },
    de: {
      action_unavailable: "Aktion nicht verfügbar.",
      print_unavailable: "Drucken ist derzeit nicht verfügbar.",
    },
    en: {
      action_unavailable: "Action unavailable.",
      print_unavailable: "Printing is unavailable right now.",
    },
    es: {
      action_unavailable: "Acción no disponible.",
      print_unavailable: "La impresión no está disponible en este momento.",
    },
    fr: {
      action_unavailable: "Action indisponible.",
      print_unavailable: "L'impression n'est pas disponible pour le moment.",
    },
    he: {
      action_unavailable: "הפעולה אינה זמינה.",
      print_unavailable: "הדפסה אינה זמינה כעת.",
    },
    it: {
      action_unavailable: "Azione non disponibile.",
      print_unavailable: "La stampa non è al momento disponibile.",
    },
    ja: {
      action_unavailable: "操作を利用できません。",
      print_unavailable: "現在は印刷を利用できません。",
    },
    nl: {
      action_unavailable: "Actie niet beschikbaar.",
      print_unavailable: "Afdrukken is momenteel niet beschikbaar.",
    },
    pl: {
      action_unavailable: "Działanie niedostępne.",
      print_unavailable: "Drukowanie jest obecnie niedostępne.",
    },
    pt: {
      action_unavailable: "Ação indisponível.",
      print_unavailable: "A impressão não está disponível no momento.",
    },
    "pt-br": {
      action_unavailable: "Ação indisponível.",
      print_unavailable: "A impressão não está disponível no momento.",
    },
    ru: {
      action_unavailable: "Действие недоступно.",
      print_unavailable: "Печать сейчас недоступна.",
    },
    tr: {
      action_unavailable: "İşlem kullanılamıyor.",
      print_unavailable: "Yazdırma şu anda kullanılamıyor.",
    },
    zh: {
      action_unavailable: "操作不可用。",
      print_unavailable: "当前无法打印。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = Object.assign(
      {},
      window.translations[k] || {},
      t[k]
    );
  });
})();
(function () {
  if (!window.translations) window.translations = {};
  const t = {
    ar: { pos_unavailable: "الإجراء غير متاح." },
    da: { pos_unavailable: "Handling ikke tilgængelig." },
    de: { pos_unavailable: "Aktion nicht verfügbar." },
    en: { pos_unavailable: "Action unavailable." },
    es: { pos_unavailable: "Acción no disponible." },
    fr: { pos_unavailable: "Action indisponible." },
    he: { pos_unavailable: "הפעולה אינה זמינה." },
    it: { pos_unavailable: "Azione non disponibile." },
    ja: { pos_unavailable: "操作を利用できません。" },
    nl: { pos_unavailable: "Actie niet beschikbaar." },
    pl: { pos_unavailable: "Działanie niedostępne." },
    pt: { pos_unavailable: "Ação indisponível." },
    "pt-br": { pos_unavailable: "Ação indisponível." },
    ru: { pos_unavailable: "Действие недоступно." },
    tr: { pos_unavailable: "İşlem kullanılamıyor." },
    zh: { pos_unavailable: "操作不可用。" },
  };
  Object.keys(t).forEach(k => {
    window.translations[k] = Object.assign(
      {},
      window.translations[k] || {},
      t[k]
    );
  });
  (function () {
    if (!window.translations) window.translations = {};
    const t = {
      ar: { pos_unavailable: "الإجراء غير متاح." },
      da: { pos_unavailable: "Handling ikke tilgængelig." },
      de: { pos_unavailable: "Aktion nicht verfügbar." },
      en: { pos_unavailable: "Action unavailable." },
      es: { pos_unavailable: "Acción no disponible." },
      fr: { pos_unavailable: "Action indisponible." },
      he: { pos_unavailable: "הפעולה אינה זמינה." },
      it: { pos_unavailable: "Azione non disponibile." },
      ja: { pos_unavailable: "操作を利用できません。" },
      nl: { pos_unavailable: "Actie niet beschikbaar." },
      pl: { pos_unavailable: "Działanie niedostępne." },
      pt: { pos_unavailable: "Ação indisponível." },
      "pt-br": { pos_unavailable: "Ação indisponível." },
      ru: { pos_unavailable: "Действие недоступно." },
      tr: { pos_unavailable: "İşlem kullanılamıyor." },
      zh: { pos_unavailable: "操作不可用。" },
    };
    Object.keys(t).forEach(k => {
      window.translations[k] = Object.assign(
        {},
        window.translations[k] || {},
        t[k]
      );
    });
  })();
})();
