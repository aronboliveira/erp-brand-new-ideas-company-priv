/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { bugstatus_order_failed: "فشل تحديث ترتيب الحالة." },
    da: {
      bugstatus_order_failed: "Opdatering af statusrækkefølge mislykkedes.",
    },
    de: {
      bugstatus_order_failed:
        "Aktualisierung der Statusreihenfolge fehlgeschlagen.",
    },
    en: { bugstatus_order_failed: "Failed to update status order." },
    es: { bugstatus_order_failed: "Error al actualizar el orden de estado." },
    fr: {
      bugstatus_order_failed: "Échec de la mise à jour de l’ordre des statuts.",
    },
    he: { bugstatus_order_failed: "נכשל עדכון סידור הסטטוסים." },
    it: {
      bugstatus_order_failed:
        "Aggiornamento dell’ordine di stato non riuscito.",
    },
    ja: { bugstatus_order_failed: "ステータス順序の更新に失敗しました。" },
    nl: { bugstatus_order_failed: "Bijwerken van statusvolgorde mislukt." },
    pl: {
      bugstatus_order_failed:
        "Aktualizacja kolejności statusu nie powiodła się.",
    },
    pt: { bugstatus_order_failed: "Falha ao atualizar a ordem de status." },
    "pt-br": {
      bugstatus_order_failed: "Falha ao atualizar a ordem de status.",
    },
    ru: { bugstatus_order_failed: "Не удалось обновить порядок статусов." },
    tr: { bugstatus_order_failed: "Durum sırası güncellenemedi." },
    zh: { bugstatus_order_failed: "更新状态顺序失败。" },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
