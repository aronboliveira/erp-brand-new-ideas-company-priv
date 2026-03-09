/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/lang/actions.js
 * @generated from original JavaScript — automated migration
 * @module actions
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

(function (): void {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      confirm_unavailable: "تعذّر فتح مربع التأكيد.",
      delete_unavailable: "فشل حذف الاجتماع.",
      route_unavailable: "الرابط غير صالح للحذف.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      confirm_unavailable: "Kunne ikke åbne bekræftelsesdialog.",
      delete_unavailable: "Sletning af mødet mislykkedes.",
      route_unavailable: "Ugyldig slette-URL.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      confirm_unavailable: "Bestätigungsdialog konnte nicht geöffnet werden.",
      delete_unavailable: "Löschen des Meetings fehlgeschlagen.",
      route_unavailable: "Ungültige Lösch-URL.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      confirm_unavailable: "Could not open confirmation dialog.",
      delete_unavailable: "Failed to delete meeting.",
      route_unavailable: "Invalid delete URL.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      confirm_unavailable: "No se pudo abrir el diálogo de confirmación.",
      delete_unavailable: "Error al eliminar la reunión.",
      route_unavailable: "URL de eliminación no válida.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      confirm_unavailable: "Impossible d’ouvrir la boîte de confirmation.",
      delete_unavailable: "Échec de la suppression de la réunion.",
      route_unavailable: "URL de suppression invalide.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      confirm_unavailable: "לא ניתן לפתוח חלון אישור.",
      delete_unavailable: "מחיקת הפגישה נכשלה.",
      route_unavailable: "כתובת מחיקה לא תקפה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      confirm_unavailable: "Impossibile aprire la finestra di conferma.",
      delete_unavailable: "Eliminazione riunione non riuscita.",
      route_unavailable: "URL di eliminazione non valido.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      confirm_unavailable: "確認ダイアログを開けませんでした。",
      delete_unavailable: "会議の削除に失敗しました。",
      route_unavailable: "無効な削除URLです。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      confirm_unavailable: "Kon bevestigingsvenster niet openen.",
      delete_unavailable: "Vergadering verwijderen is mislukt.",
      route_unavailable: "Ongeldige verwijder-URL.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      confirm_unavailable: "Nie można otworzyć okna potwierdzenia.",
      delete_unavailable: "Nie udało się usunąć spotkania.",
      route_unavailable: "Nieprawidłowy adres URL usuwania.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      confirm_unavailable: "Não foi possível abrir a confirmação.",
      delete_unavailable: "Falha ao excluir reunião.",
      route_unavailable: "URL de exclusão inválida.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      confirm_unavailable: "Não foi possível abrir a confirmação.",
      delete_unavailable: "Falha ao excluir a reunião.",
      route_unavailable: "URL de exclusão inválida.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      confirm_unavailable: "Не удалось открыть окно подтверждения.",
      delete_unavailable: "Не удалось удалить встречу.",
      route_unavailable: "Недействительный URL удаления.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      confirm_unavailable: "Onay penceresi açılamadı.",
      delete_unavailable: "Toplantı silinemedi.",
      route_unavailable: "Geçersiz silme URL’si.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      confirm_unavailable: "无法打开确认对话框。",
      delete_unavailable: "删除会议失败。",
      route_unavailable: "无效的删除链接。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
