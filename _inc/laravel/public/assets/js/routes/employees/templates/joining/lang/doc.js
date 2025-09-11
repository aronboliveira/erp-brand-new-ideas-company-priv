(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      export_unavailable: "تعذّر إنشاء الملف.",
      element_unavailable: "العنصر المطلوب غير موجود.",
      close_unavailable: "تعذّر إغلاق النافذة.",
    },
    da: {
      export_unavailable: "Kunne ikke oprette fil.",
      element_unavailable: "Påkrævet element mangler.",
      close_unavailable: "Kunne ikke lukke vinduet.",
    },
    de: {
      export_unavailable: "Datei konnte nicht erstellt werden.",
      element_unavailable: "Erforderliches Element fehlt.",
      close_unavailable: "Fenster konnte nicht geschlossen werden.",
    },
    en: {
      export_unavailable: "Could not create the file.",
      element_unavailable: "Required element is missing.",
      close_unavailable: "Could not close the window.",
    },
    es: {
      export_unavailable: "No se pudo crear el archivo.",
      element_unavailable: "Falta el elemento requerido.",
      close_unavailable: "No se pudo cerrar la ventana.",
    },
    fr: {
      export_unavailable: "Impossible de créer le fichier.",
      element_unavailable: "Élément requis manquant.",
      close_unavailable: "Impossible de fermer la fenêtre.",
    },
    he: {
      export_unavailable: "לא ניתן ליצור את הקובץ.",
      element_unavailable: "האלמנט הנדרש חסר.",
      close_unavailable: "לא ניתן לסגור את החלון.",
    },
    it: {
      export_unavailable: "Impossibile creare il file.",
      element_unavailable: "Elemento richiesto mancante.",
      close_unavailable: "Impossibile chiudere la finestra.",
    },
    ja: {
      export_unavailable: "ファイルを作成できませんでした。",
      element_unavailable: "必要な要素が見つかりません。",
      close_unavailable: "ウィンドウを閉じられませんでした。",
    },
    nl: {
      export_unavailable: "Kan het bestand niet maken.",
      element_unavailable: "Vereist element ontbreekt.",
      close_unavailable: "Kon het venster niet sluiten.",
    },
    pl: {
      export_unavailable: "Nie można utworzyć pliku.",
      element_unavailable: "Brakuje wymaganego elementu.",
      close_unavailable: "Nie można zamknąć okna.",
    },
    pt: {
      export_unavailable: "Não foi possível criar o arquivo.",
      element_unavailable: "Elemento necessário ausente.",
      close_unavailable: "Não foi possível fechar a janela.",
    },
    "pt-br": {
      export_unavailable: "Não foi possível criar o arquivo.",
      element_unavailable: "Elemento obrigatório ausente.",
      close_unavailable: "Não foi possível fechar a janela.",
    },
    ru: {
      export_unavailable: "Не удалось создать файл.",
      element_unavailable: "Отсутствует необходимый элемент.",
      close_unavailable: "Не удалось закрыть окно.",
    },
    tr: {
      export_unavailable: "Dosya oluşturulamadı.",
      element_unavailable: "Gerekli öğe eksik.",
      close_unavailable: "Pencere kapatılamadı.",
    },
    zh: {
      export_unavailable: "无法创建文件。",
      element_unavailable: "缺少所需元素。",
      close_unavailable: "无法关闭窗口。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
