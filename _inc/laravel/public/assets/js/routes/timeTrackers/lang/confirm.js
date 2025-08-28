(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      confirm_unavailable: "تعذّر فتح مربع تأكيد الحذف.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      confirm_unavailable: "Bekræftelsesdialogen kunne ikke åbnes.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      confirm_unavailable: "Bestätigungsdialog konnte nicht geöffnet werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      confirm_unavailable: "Could not open delete confirmation dialog.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      confirm_unavailable:
        "No se pudo abrir el cuadro de confirmación de eliminación.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      confirm_unavailable:
        "Impossible d’ouvrir la boîte de confirmation de suppression.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      confirm_unavailable: "לא ניתן היה לפתוח את דו־שיח האישור למחיקה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      confirm_unavailable:
        "Impossibile aprire la finestra di conferma eliminazione.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      confirm_unavailable: "削除の確認ダイアログを開けませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      confirm_unavailable: "Kan het verwijderbevestigingsvenster niet openen.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      confirm_unavailable: "Nie można otworzyć okna potwierdzenia usunięcia.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      confirm_unavailable: "Não foi possível abrir a confirmação de exclusão.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      confirm_unavailable: "Não foi possível abrir a confirmação de exclusão.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      confirm_unavailable: "Не удалось открыть окно подтверждения удаления.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      confirm_unavailable: "Silme onayı penceresi açılamadı.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      confirm_unavailable: "无法打开删除确认对话框。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
