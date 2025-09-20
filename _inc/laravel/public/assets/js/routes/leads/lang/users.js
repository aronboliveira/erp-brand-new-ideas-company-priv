(() => {
  const t = {
    ar: { leads_users_update_route_unavailable: "الإجراء غير متاح." },
    da: {
      leads_users_update_route_unavailable: "Handlingen er ikke tilgængelig.",
    },
    de: { leads_users_update_route_unavailable: "Aktion nicht verfügbar." },
    en: { leads_users_update_route_unavailable: "Action unavailable." },
    es: { leads_users_update_route_unavailable: "Acción no disponible." },
    fr: { leads_users_update_route_unavailable: "Action indisponible." },
    he: { leads_users_update_route_unavailable: "הפעולה אינה זמינה." },
    it: { leads_users_update_route_unavailable: "Azione non disponibile." },
    ja: { leads_users_update_route_unavailable: "操作を利用できません。" },
    nl: { leads_users_update_route_unavailable: "Actie niet beschikbaar." },
    pl: { leads_users_update_route_unavailable: "Akcja niedostępna." },
    pt: { leads_users_update_route_unavailable: "Ação indisponível." },
    "pt-br": { leads_users_update_route_unavailable: "Ação indisponível." },
    ru: { leads_users_update_route_unavailable: "Действие недоступно." },
    tr: { leads_users_update_route_unavailable: "İşlem kullanılamıyor." },
    zh: { leads_users_update_route_unavailable: "操作不可用。" },
  };
  if (!window.translations) {
    window.translations = t;
  } else {
    Object.keys(t).forEach(k => {
      window.translations[k] = Object.assign(
        {},
        window.translations[k] || {},
        t[k]
      );
    });
  }
})();
