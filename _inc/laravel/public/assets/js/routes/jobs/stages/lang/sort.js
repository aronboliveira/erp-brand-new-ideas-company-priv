(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: { sort_unavailable: "تعذّر تحديث ترتيب العناصر." },
    da: { sort_unavailable: "Kunne ikke opdatere sorteringen." },
    de: {
      sort_unavailable: "Sortierreihenfolge konnte nicht aktualisiert werden.",
    },
    en: { sort_unavailable: "Could not update the sort order." },
    es: { sort_unavailable: "No se pudo actualizar el orden." },
    fr: { sort_unavailable: "Impossible de mettre à jour l’ordre." },
    he: { sort_unavailable: "לא ניתן לעדכן את סדר המיון." },
    it: { sort_unavailable: "Impossibile aggiornare l’ordinamento." },
    ja: { sort_unavailable: "並び順を更新できませんでした。" },
    nl: { sort_unavailable: "Kon de sorteervolgorde niet bijwerken." },
    pl: { sort_unavailable: "Nie można zaktualizować kolejności." },
    pt: { sort_unavailable: "Não foi possível atualizar a ordenação." },
    "pt-br": { sort_unavailable: "Não foi possível atualizar a ordenação." },
    ru: { sort_unavailable: "Не удалось обновить порядок сортировки." },
    tr: { sort_unavailable: "Sıralama güncellenemedi." },
    zh: { sort_unavailable: "无法更新排序。" },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
