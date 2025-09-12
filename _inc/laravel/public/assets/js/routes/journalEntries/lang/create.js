(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      repeater_show_unavailable: "فشل عرض المكرر",
      repeater_hide_unavailable: "فشل إخفاء المكرر",
      calc_unavailable: "فشل الحساب",
    },
    da: {
      repeater_show_unavailable: "Visning af gentager mislykkedes",
      repeater_hide_unavailable: "Skjul af gentager mislykkedes",
      calc_unavailable: "Beregning mislykkedes",
    },
    de: {
      repeater_show_unavailable: "Wiederholer-Anzeige fehlgeschlagen",
      repeater_hide_unavailable: "Wiederholer-Ausblenden fehlgeschlagen",
      calc_unavailable: "Berechnung fehlgeschlagen",
    },
    en: {
      repeater_show_unavailable: "Cannot show repeater",
      repeater_hide_unavailable: "Cannot hide repeater",
      calc_unavailable: "Calculation failed",
    },
    es: {
      repeater_show_unavailable: "Error al mostrar repetidor",
      repeater_hide_unavailable: "Error al ocultar repetidor",
      calc_unavailable: "Error en el cálculo",
    },
    fr: {
      repeater_show_unavailable: "Échec de l'affichage du répéteur",
      repeater_hide_unavailable: "Échec de la suppression du répéteur",
      calc_unavailable: "Échec du calcul",
    },
    he: {
      repeater_show_unavailable: "הצגת החוזר נכשלה",
      repeater_hide_unavailable: "הסתרת החוזר נכשלה",
      calc_unavailable: "החישוב נכשל",
    },
    it: {
      repeater_show_unavailable: "Impossibile mostrare il ripetitore",
      repeater_hide_unavailable: "Impossibile nascondere il ripetitore",
      calc_unavailable: "Errore nel calcolo",
    },
    ja: {
      repeater_show_unavailable: "リピーターの表示に失敗しました",
      repeater_hide_unavailable: "リピーターの非表示に失敗しました",
      calc_unavailable: "計算に失敗しました",
    },
    nl: {
      repeater_show_unavailable: "Herhaler weergeven mislukt",
      repeater_hide_unavailable: "Herhaler verbergen mislukt",
      calc_unavailable: "Berekening mislukt",
    },
    pl: {
      repeater_show_unavailable: "Nie można wyświetlić powtarzacza",
      repeater_hide_unavailable: "Nie można ukryć powtarzacza",
      calc_unavailable: "Błąd obliczeń",
    },
    pt: {
      repeater_show_unavailable: "Não foi possível exibir o repetidor",
      repeater_hide_unavailable: "Não foi possível ocultar o repetidor",
      calc_unavailable: "Falha no cálculo",
    },
    "pt-br": {
      repeater_show_unavailable: "Não foi possível exibir o repetidor",
      repeater_hide_unavailable: "Não foi possível ocultar o repetidor",
      calc_unavailable: "Falha no cálculo",
    },
    ru: {
      repeater_show_unavailable: "Не удалось показать повторитель",
      repeater_hide_unavailable: "Не удалось скрыть повторитель",
      calc_unavailable: "Ошибка вычисления",
    },
    tr: {
      repeater_show_unavailable: "Tekrar gösterilemedi",
      repeater_hide_unavailable: "Tekrar gizlenemedi",
      calc_unavailable: "Hesaplama başarısız",
    },
    zh: {
      repeater_show_unavailable: "无法显示重复项",
      repeater_hide_unavailable: "无法隐藏重复项",
      calc_unavailable: "计算失败",
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
