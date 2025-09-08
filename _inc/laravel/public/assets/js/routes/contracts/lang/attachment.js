(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      route_unavailable: "رابط غير صالح.",
      status_update_unavailable: "تعذّر تحديث الحالة.",
      attachment_upload_unavailable: "تعذّر رفع المرفق.",
      comment_submit_unavailable: "تعذّر إرسال التعليق.",
      comment_delete_unavailable: "تعذّر حذف التعليق.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      route_unavailable: "Ugyldig URL.",
      status_update_unavailable: "Kunne ikke opdatere status.",
      attachment_upload_unavailable: "Kunne ikke uploade vedhæftning.",
      comment_submit_unavailable: "Kunne ikke sende kommentaren.",
      comment_delete_unavailable: "Kunne ikke slette kommentaren.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      route_unavailable: "Ungültige URL.",
      status_update_unavailable: "Status konnte nicht aktualisiert werden.",
      attachment_upload_unavailable: "Anlage konnte nicht hochgeladen werden.",
      comment_submit_unavailable: "Kommentar konnte nicht gesendet werden.",
      comment_delete_unavailable: "Kommentar konnte nicht gelöscht werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      route_unavailable: "Invalid URL.",
      status_update_unavailable: "Could not update the status.",
      attachment_upload_unavailable: "Could not upload the attachment.",
      comment_submit_unavailable: "Could not submit the comment.",
      comment_delete_unavailable: "Could not delete the comment.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      route_unavailable: "URL no válida.",
      status_update_unavailable: "No se pudo actualizar el estado.",
      attachment_upload_unavailable: "No se pudo subir el adjunto.",
      comment_submit_unavailable: "No se pudo enviar el comentario.",
      comment_delete_unavailable: "No se pudo eliminar el comentario.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      route_unavailable: "URL non valide.",
      status_update_unavailable: "Impossible de mettre à jour le statut.",
      attachment_upload_unavailable:
        "Impossible de téléverser la pièce jointe.",
      comment_submit_unavailable: "Impossible d’envoyer le commentaire.",
      comment_delete_unavailable: "Impossible de supprimer le commentaire.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      route_unavailable: "כתובת לא חוקית.",
      status_update_unavailable: "לא ניתן היה לעדכן את הסטטוס.",
      attachment_upload_unavailable: "לא ניתן היה להעלות קובץ מצורף.",
      comment_submit_unavailable: "לא ניתן היה לשלוח את התגובה.",
      comment_delete_unavailable: "לא ניתן היה למחוק את התגובה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      route_unavailable: "URL non valido.",
      status_update_unavailable: "Impossibile aggiornare lo stato.",
      attachment_upload_unavailable: "Impossibile caricare l'allegato.",
      comment_submit_unavailable: "Impossibile inviare il commento.",
      comment_delete_unavailable: "Impossibile eliminare il commento.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      route_unavailable: "無効なURLです。",
      status_update_unavailable: "ステータスを更新できませんでした。",
      attachment_upload_unavailable:
        "添付ファイルをアップロードできませんでした。",
      comment_submit_unavailable: "コメントを送信できませんでした。",
      comment_delete_unavailable: "コメントを削除できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      route_unavailable: "Ongeldige URL.",
      status_update_unavailable: "Status kon niet worden bijgewerkt.",
      attachment_upload_unavailable: "Bijlage kon niet worden geüpload.",
      comment_submit_unavailable: "Kon de reactie niet verzenden.",
      comment_delete_unavailable: "Kon de reactie niet verwijderen.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      route_unavailable: "Nieprawidłowy adres URL.",
      status_update_unavailable: "Nie udało się zaktualizować statusu.",
      attachment_upload_unavailable: "Nie udało się przesłać załącznika.",
      comment_submit_unavailable: "Nie udało się wysłać komentarza.",
      comment_delete_unavailable: "Nie udało się usunąć komentarza.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválido.",
      status_update_unavailable: "Não foi possível atualizar o status.",
      attachment_upload_unavailable: "Não foi possível enviar o anexo.",
      comment_submit_unavailable: "Não foi possível enviar o comentário.",
      comment_delete_unavailable: "Não foi possível excluir o comentário.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválida.",
      status_update_unavailable: "Não foi possível atualizar o status.",
      attachment_upload_unavailable: "Não foi possível enviar o anexo.",
      comment_submit_unavailable: "Não foi possível enviar o comentário.",
      comment_delete_unavailable: "Não foi possível excluir o comentário.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      route_unavailable: "Недействительный URL.",
      status_update_unavailable: "Не удалось обновить статус.",
      attachment_upload_unavailable: "Не удалось загрузить вложение.",
      comment_submit_unavailable: "Не удалось отправить комментарий.",
      comment_delete_unavailable: "Не удалось удалить комментарий.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      route_unavailable: "Geçersiz URL.",
      status_update_unavailable: "Durum güncellenemedi.",
      attachment_upload_unavailable: "Ek yüklenemedi.",
      comment_submit_unavailable: "Yorum gönderilemedi.",
      comment_delete_unavailable: "Yorum silinemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      route_unavailable: "无效的链接。",
      status_update_unavailable: "无法更新状态。",
      attachment_upload_unavailable: "无法上传附件。",
      comment_submit_unavailable: "无法提交评论。",
      comment_delete_unavailable: "无法删除评论。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
