(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      lead_upload_unavailable: "تعذّر رفع الملف الآن",
      lead_delete_unavailable: "تعذّر حذف الملف الآن",
      lead_notes_unavailable: "تعذّر حفظ الملاحظات الآن",
      dropzone_unavailable: "عنصر الرفع غير متاح",
    },
    da: {
      lead_upload_unavailable: "Kan ikke uploade fil lige nu",
      lead_delete_unavailable: "Kan ikke slette fil lige nu",
      lead_notes_unavailable: "Kan ikke gemme noter lige nu",
      dropzone_unavailable: "Upload-widget ikke tilgængelig",
    },
    de: {
      lead_upload_unavailable: "Datei kann derzeit nicht hochgeladen werden",
      lead_delete_unavailable: "Datei kann derzeit nicht gelöscht werden",
      lead_notes_unavailable: "Notizen können derzeit nicht gespeichert werden",
      dropzone_unavailable: "Upload-Widget nicht verfügbar",
    },
    en: {
      lead_upload_unavailable: "Cannot upload file right now",
      lead_delete_unavailable: "Cannot delete file right now",
      lead_notes_unavailable: "Cannot save notes right now",
      dropzone_unavailable: "Upload widget unavailable",
    },
    es: {
      lead_upload_unavailable: "No se puede subir el archivo ahora",
      lead_delete_unavailable: "No se puede eliminar el archivo ahora",
      lead_notes_unavailable: "No se pueden guardar las notas ahora",
      dropzone_unavailable: "Carga no disponible",
    },
    fr: {
      lead_upload_unavailable: "Impossible de téléverser le fichier maintenant",
      lead_delete_unavailable: "Impossible de supprimer le fichier maintenant",
      lead_notes_unavailable: "Impossible d’enregistrer les notes maintenant",
      dropzone_unavailable: "Widget d’envoi indisponible",
    },
    he: {
      lead_upload_unavailable: "לא ניתן להעלות קובץ כעת",
      lead_delete_unavailable: "לא ניתן למחוק קובץ כעת",
      lead_notes_unavailable: "לא ניתן לשמור הערות כעת",
      dropzone_unavailable: "רכיב העלאה לא זמין",
    },
    it: {
      lead_upload_unavailable: "Impossibile caricare il file ora",
      lead_delete_unavailable: "Impossibile eliminare il file ora",
      lead_notes_unavailable: "Impossibile salvare le note ora",
      dropzone_unavailable: "Widget di upload non disponibile",
    },
    ja: {
      lead_upload_unavailable: "現在ファイルをアップロードできません",
      lead_delete_unavailable: "現在ファイルを削除できません",
      lead_notes_unavailable: "現在メモを保存できません",
      dropzone_unavailable: "アップロードウィジェットが利用できません",
    },
    nl: {
      lead_upload_unavailable: "Bestand kan nu niet worden geüpload",
      lead_delete_unavailable: "Bestand kan nu niet worden verwijderd",
      lead_notes_unavailable: "Notities kunnen nu niet worden opgeslagen",
      dropzone_unavailable: "Uploadwidget niet beschikbaar",
    },
    pl: {
      lead_upload_unavailable: "Nie można teraz przesłać pliku",
      lead_delete_unavailable: "Nie można teraz usunąć pliku",
      lead_notes_unavailable: "Nie można teraz zapisać notatek",
      dropzone_unavailable: "Widget przesyłania niedostępny",
    },
    pt: {
      lead_upload_unavailable: "Não é possível enviar o arquivo agora",
      lead_delete_unavailable: "Não é possível excluir o arquivo agora",
      lead_notes_unavailable: "Não é possível salvar as notas agora",
      dropzone_unavailable: "Widget de upload indisponível",
    },
    "pt-br": {
      lead_upload_unavailable: "Não é possível enviar o arquivo agora",
      lead_delete_unavailable: "Não é possível excluir o arquivo agora",
      lead_notes_unavailable: "Não é possível salvar as notas agora",
      dropzone_unavailable: "Widget de upload indisponível",
    },
    ru: {
      lead_upload_unavailable: "Не удаётся загрузить файл сейчас",
      lead_delete_unavailable: "Не удаётся удалить файл сейчас",
      lead_notes_unavailable: "Не удаётся сохранить заметки сейчас",
      dropzone_unavailable: "Виджет загрузки недоступен",
    },
    tr: {
      lead_upload_unavailable: "Şu anda dosya yüklenemiyor",
      lead_delete_unavailable: "Şu anda dosya silinemiyor",
      lead_notes_unavailable: "Şu anda notlar kaydedilemiyor",
      dropzone_unavailable: "Yükleme bileşeni kullanılamıyor",
    },
    zh: {
      lead_upload_unavailable: "当前无法上传文件",
      lead_delete_unavailable: "当前无法删除文件",
      lead_notes_unavailable: "当前无法保存备注",
      dropzone_unavailable: "上传组件不可用",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
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
})();
