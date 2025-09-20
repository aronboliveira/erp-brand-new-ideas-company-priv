(function () {
  if (!window.translations) window.translations = {};
  const t = {
    ar: { leads_unavailable: "الإجراء غير متاح." },
    da: { leads_unavailable: "Handling ikke tilgængelig." },
    de: { leads_unavailable: "Aktion nicht verfügbar." },
    en: { leads_unavailable: "Action unavailable." },
    es: { leads_unavailable: "Acción no disponible." },
    fr: { leads_unavailable: "Action indisponible." },
    he: { leads_unavailable: "הפעולה אינה זמינה." },
    it: { leads_unavailable: "Azione non disponibile." },
    ja: { leads_unavailable: "操作を利用できません。" },
    nl: { leads_unavailable: "Actie niet beschikbaar." },
    pl: { leads_unavailable: "Działanie niedostępne." },
    pt: { leads_unavailable: "Ação indisponível." },
    "pt-br": { leads_unavailable: "Ação indisponível." },
    ru: { leads_unavailable: "Действие недоступно." },
    tr: { leads_unavailable: "İşlem kullanılamıyor." },
    zh: { leads_unavailable: "操作不可用。" },
  };
  Object.keys(t).forEach(k => {
    window.translations[k] = Object.assign(
      {},
      window.translations[k] || {},
      t[k]
    );
  });
})();
