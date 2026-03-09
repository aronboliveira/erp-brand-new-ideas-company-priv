/**
 * @fileoverview TypeScript version of public/assets/js/routes/journalEntries/lang/edit.js
 * @generated from original JavaScript — automated migration
 * @module edit
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      repeater_show_unavailable: "فشل عرض المكرر",
      repeater_hide_unavailable: "فشل إخفاء المكرر",
      calc_unavailable: "فشل الحساب",
      destroy_unavailable: "فشل حذف الحساب",
    },
    da: {
      repeater_show_unavailable: "Visning af gentager mislykkedes",
      repeater_hide_unavailable: "Skjul af gentager mislykkedes",
      calc_unavailable: "Beregning mislykkedes",
      destroy_unavailable: "Sletning af post mislykkedes",
    },
    de: {
      repeater_show_unavailable: "Wiederholer-Anzeige fehlgeschlagen",
      repeater_hide_unavailable: "Wiederholer-Ausblenden fehlgeschlagen",
      calc_unavailable: "Berechnung fehlgeschlagen",
      destroy_unavailable: "Löschen des Kontos fehlgeschlagen",
    },
    en: {
      repeater_show_unavailable: "Cannot show repeater",
      repeater_hide_unavailable: "Cannot hide repeater",
      calc_unavailable: "Calculation failed",
      destroy_unavailable: "Failed to delete account",
    },
    es: {
      repeater_show_unavailable: "Error al mostrar repetidor",
      repeater_hide_unavailable: "Error al ocultar repetidor",
      calc_unavailable: "Error en el cálculo",
      destroy_unavailable: "Error al eliminar la cuenta",
    },
    fr: {
      repeater_show_unavailable: "Échec de l'affichage du répéteur",
      repeater_hide_unavailable: "Échec de la suppression du répéteur",
      calc_unavailable: "Échec du calcul",
      destroy_unavailable: "Échec de la suppression du compte",
    },
    he: {
      repeater_show_unavailable: "הצגת החוזר נכשלה",
      repeater_hide_unavailable: "הסתרת החוזר נכשלה",
      calc_unavailable: "החישוב נכשל",
      destroy_unavailable: "המחיקה נכשלה",
    },
    it: {
      repeater_show_unavailable: "Impossibile mostrare il ripetitore",
      repeater_hide_unavailable: "Impossibile nascondere il ripetitore",
      calc_unavailable: "Errore nel calcolo",
      destroy_unavailable: "Impossibile eliminare il conto",
    },
    ja: {
      repeater_show_unavailable: "リピーターの表示に失敗しました",
      repeater_hide_unavailable: "リピーターの非表示に失敗しました",
      calc_unavailable: "計算に失敗しました",
      destroy_unavailable: "アカウントの削除に失敗しました",
    },
    nl: {
      repeater_show_unavailable: "Herhaler weergeven mislukt",
      repeater_hide_unavailable: "Herhaler verbergen mislukt",
      calc_unavailable: "Berekening mislukt",
      destroy_unavailable: "Verwijderen van account mislukt",
    },
    pl: {
      repeater_show_unavailable: "Nie można wyświetlić powtarzacza",
      repeater_hide_unavailable: "Nie można ukryć powtarzacza",
      calc_unavailable: "Błąd obliczeń",
      destroy_unavailable: "Nie można usunąć konta",
    },
    pt: {
      repeater_show_unavailable: "Não foi possível exibir o repetidor",
      repeater_hide_unavailable: "Não foi possível ocultar o repetidor",
      calc_unavailable: "Falha no cálculo",
      destroy_unavailable: "Falha ao excluir conta",
    },
    "pt-br": {
      repeater_show_unavailable: "Não foi possível exibir o repetidor",
      repeater_hide_unavailable: "Não foi possível ocultar o repetidor",
      calc_unavailable: "Falha no cálculo",
      destroy_unavailable: "Falha ao excluir conta",
    },
    ru: {
      repeater_show_unavailable: "Не удалось показать повторитель",
      repeater_hide_unavailable: "Не удалось скрыть повторитель",
      calc_unavailable: "Ошибка вычисления",
      destroy_unavailable: "Не удалось удалить запись",
    },
    tr: {
      repeater_show_unavailable: "Tekrar gösterilemedi",
      repeater_hide_unavailable: "Tekrar gizlenemedi",
      calc_unavailable: "Hesaplama başarısız",
      destroy_unavailable: "Hesap silme başarısız",
    },
    zh: {
      repeater_show_unavailable: "无法显示重复项",
      repeater_hide_unavailable: "无法隐藏重复项",
      calc_unavailable: "计算失败",
      destroy_unavailable: "删除账户失败",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
