(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { lead_order_unavailable: "فشل تحديث ترتيب المراحل" },
    da: { lead_order_unavailable: "Opdatering af rækkefølge mislykkedes" },
    de: { lead_order_unavailable: "Reihenfolgeaktualisierung fehlgeschlagen" },
    en: { lead_order_unavailable: "Failed to update lead stages order" },
    es: { lead_order_unavailable: "Error al actualizar el orden de etapas" },
    fr: {
      lead_order_unavailable: "Échec de la mise à jour de l’ordre des étapes",
    },
    he: { lead_order_unavailable: "עדכון סדר השלבים נכשל" },
    it: {
      lead_order_unavailable:
        "Aggiornamento dell’ordine delle fasi non riuscito",
    },
    ja: { lead_order_unavailable: "ステージ順序の更新に失敗しました" },
    nl: { lead_order_unavailable: "Bijwerken volgorde mislukt" },
    pl: { lead_order_unavailable: "Aktualizacja kolejności nieudana" },
    pt: { lead_order_unavailable: "Falha ao atualizar a ordem das etapas" },
    "pt-br": {
      lead_order_unavailable: "Falha ao atualizar a ordem das etapas",
    },
    ru: { lead_order_unavailable: "Не удалось обновить порядок этапов" },
    tr: { lead_order_unavailable: "Aşamalar sırası güncellenemedi" },
    zh: { lead_order_unavailable: "无法更新阶段顺序" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
