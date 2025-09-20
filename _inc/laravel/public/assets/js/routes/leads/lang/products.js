(function () {
  var t = {
    ar: {
      action_unavailable: "الإجراء غير متاح.",
      update_unavailable: "التحديث غير متاح.",
    },
    da: {
      action_unavailable: "Handling ikke tilgængelig.",
      update_unavailable: "Opdatering ikke tilgængelig.",
    },
    de: {
      action_unavailable: "Aktion nicht verfügbar.",
      update_unavailable: "Aktualisierung nicht verfügbar.",
    },
    en: {
      action_unavailable: "Action unavailable.",
      update_unavailable: "Update is unavailable.",
    },
    es: {
      action_unavailable: "Acción no disponible.",
      update_unavailable: "Actualización no disponible.",
    },
    fr: {
      action_unavailable: "Action indisponible.",
      update_unavailable: "Mise à jour indisponible.",
    },
    he: {
      action_unavailable: "הפעולה אינה זמינה.",
      update_unavailable: "העדכון אינו זמין.",
    },
    it: {
      action_unavailable: "Azione non disponibile.",
      update_unavailable: "Aggiornamento non disponibile.",
    },
    ja: {
      action_unavailable: "操作を利用できません。",
      update_unavailable: "更新は利用できません。",
    },
    nl: {
      action_unavailable: "Actie niet beschikbaar.",
      update_unavailable: "Bijwerken niet beschikbaar.",
    },
    pl: {
      action_unavailable: "Akcja niedostępna.",
      update_unavailable: "Aktualizacja niedostępna.",
    },
    pt: {
      action_unavailable: "Ação indisponível.",
      update_unavailable: "Atualização indisponível.",
    },
    "pt-br": {
      action_unavailable: "Ação indisponível.",
      update_unavailable: "Atualização indisponível.",
    },
    ru: {
      action_unavailable: "Действие недоступно.",
      update_unavailable: "Обновление недоступно.",
    },
    tr: {
      action_unavailable: "Eylem kullanılamıyor.",
      update_unavailable: "Güncelleme kullanılamıyor.",
    },
    zh: {
      action_unavailable: "无法执行此操作。",
      update_unavailable: "无法更新。",
    },
  };
  if (!window.translations) window.translations = t;
  else
    Object.keys(t).forEach(function (k) {
      window.translations[k] = Object.assign(
        {},
        window.translations[k] || {},
        t[k]
      );
    });
})();
